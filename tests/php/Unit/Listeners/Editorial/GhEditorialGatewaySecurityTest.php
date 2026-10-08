<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\GhEditorialGateway;
use App\Listeners\Editorial\ProcessResult;
use PHPUnit\Framework\TestCase;

final class GhEditorialGatewaySecurityTest extends TestCase
{
    public function testStatusBodyIsPassedAsSingleArgumentWithoutShellInterpolation(): void
    {
        $runner = new RecordingProcessRunner();
        $runner->queue(
            new ProcessResult(0, "[]", ''),
            new ProcessResult(0, '{}', ''),
        );

        $gateway = new GhEditorialGateway(
            'e-cidade/site',
            '/repo',
            $runner,
            '/tmp/workspaces',
        );

        $body = "texto; touch /tmp/pwned\n\$(uname) && echo unsafe";
        $gateway->upsertStatus(321, '<!-- marker -->', $body);

        self::assertSame(
            [
                'gh', 'api', '--method', 'POST',
                'repos/e-cidade/site/issues/321/comments',
                '-f', 'body=' . $body,
            ],
            $runner->calls[1]['command'],
        );
    }

    public function testUsesConfiguredAutomationIdentityForEditorialCommit(): void
    {
        $runner = new RecordingProcessRunner();
        $runner->queue(
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
            new ProcessResult(0, '', ''),
        );

        $gateway = new GhEditorialGateway(
            'e-cidade/site',
            '/repo',
            $runner,
            '/tmp/workspaces',
            'e-cidade-editorial[bot]',
            '123+e-cidade-editorial[bot]@users.noreply.github.com',
        );

        $workspace = new \App\Listeners\Editorial\EditorialWorkspace(
            'content/news-321',
            '/tmp/workspaces/news-321',
            null,
            false,
        );

        $gateway->commitAndPushEditorialChanges($workspace, 321);

        self::assertSame(
            ['git', '-C', '/tmp/workspaces/news-321', 'config', 'user.name', 'e-cidade-editorial[bot]'],
            $runner->calls[0]['command'],
        );
        self::assertSame(
            [
                'git', '-C', '/tmp/workspaces/news-321', 'config', 'user.email',
                '123+e-cidade-editorial[bot]@users.noreply.github.com',
            ],
            $runner->calls[1]['command'],
        );
    }

    public function testEditorialBranchNameIsDerivedFromIssueNumberOutsideGateway(): void
    {
        $runner = new RecordingProcessRunner();
        $runner->queue(new ProcessResult(0, '', ''));

        $gateway = new GhEditorialGateway(
            'e-cidade/site',
            '/repo',
            $runner,
            '/tmp/workspaces',
        );

        self::assertFalse($gateway->branchExists('content/news-321'));
        self::assertSame(
            ['git', 'ls-remote', '--heads', 'origin', 'content/news-321'],
            $runner->calls[0]['command'],
        );
    }
}
