<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\GhEditorialGateway;
use App\Listeners\Editorial\ProcessResult;
use PHPUnit\Framework\TestCase;

final class GhEditorialGatewayWorktreeTest extends TestCase
{
    public function testNewEditorialBranchUsesSeparateWorktreeBasedOnMain(): void
    {
        $runner = new RecordingProcessRunner();
        $runner->queue(
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, "mainsha\n", ''),
            new ProcessResult(1, '', ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
        );

        $gateway = new GhEditorialGateway(
            'e-cidade/site',
            '/repo',
            $runner,
            '/tmp/workspaces',
        );

        $workspace = $gateway->prepareEditorialBranch('content/news-321');

        self::assertSame('content/news-321', $workspace->branch);
        self::assertNull($workspace->remoteSha);
        self::assertFalse($workspace->refreshRequired);
        self::assertStringStartsWith('/tmp/workspaces/e-cidade-editorial-', $workspace->path);
        self::assertContains(
            ['git', 'worktree', 'add', '--force', '-B', 'content/news-321', $workspace->path, 'origin/main'],
            array_column($runner->calls, 'command'),
        );
        self::assertNotContains(
            ['git', 'checkout', '-B', 'content/news-321', 'origin/main'],
            array_column($runner->calls, 'command'),
        );
    }

    public function testStaleEditorialBranchIsRebuiltOnMainAndOnlyEditorialDiffIsRestored(): void
    {
        $runner = new RecordingProcessRunner();
        $runner->queue(
            new ProcessResult(0, "oldsha\trefs/heads/content/news-321\n", ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, "mainsha\n", ''),
            new ProcessResult(1, '', ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, "basesha\n", ''),
            new ProcessResult(0, "M\tsource/_posts/noticia.md\nA\tsource/assets/images/news/321/cover.png\n", ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
        );

        $gateway = new GhEditorialGateway(
            'e-cidade/site',
            '/repo',
            $runner,
            '/tmp/workspaces',
        );

        $workspace = $gateway->prepareEditorialBranch('content/news-321');

        self::assertSame('oldsha', $workspace->remoteSha);
        self::assertTrue($workspace->refreshRequired);
        self::assertContains(
            ['git', 'worktree', 'add', '--force', '-B', 'content/news-321', $workspace->path, 'origin/main'],
            array_column($runner->calls, 'command'),
        );
        self::assertContains(
            ['git', '-C', $workspace->path, 'restore', '--source=origin/content/news-321', '--', 'source/_posts/noticia.md'],
            array_column($runner->calls, 'command'),
        );
        self::assertContains(
            ['git', '-C', $workspace->path, 'restore', '--source=origin/content/news-321', '--', 'source/assets/images/news/321/cover.png'],
            array_column($runner->calls, 'command'),
        );
    }
}
