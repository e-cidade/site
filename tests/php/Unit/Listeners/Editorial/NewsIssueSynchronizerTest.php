<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\NewsIssueSynchronizer;
use PHPUnit\Framework\TestCase;

final class NewsIssueSynchronizerTest extends TestCase
{
    private string $postsDirectory;

    protected function setUp(): void
    {
        $this->postsDirectory = sys_get_temp_dir() . '/ecidade-news-' . bin2hex(random_bytes(6));
        mkdir($this->postsDirectory, 0775, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->postsDirectory . '/*') ?: [] as $path) {
            unlink($path);
        }

        if (is_dir($this->postsDirectory)) {
            rmdir($this->postsDirectory);
        }
    }

    public function testCreatesPostFromIssue(): void
    {
        $result = $this->synchronize('Prefeitura de Exemplo adota o e-Cidade');

        self::assertTrue($result['changed']);
        self::assertTrue($result['created']);
        self::assertSame('prefeitura-de-exemplo-adota-o-e-cidade', $result['slug']);
        self::assertFileExists($result['path']);
        self::assertStringContainsString('github_issue: 321', (string) file_get_contents($result['path']));
    }

    public function testIsIdempotentForSameIssueContent(): void
    {
        $first = $this->synchronize('Prefeitura de Exemplo adota o e-Cidade');
        $second = $this->synchronize('Prefeitura de Exemplo adota o e-Cidade');

        self::assertTrue($first['changed']);
        self::assertFalse($second['changed']);
        self::assertFalse($second['created']);
        self::assertSame($first['path'], $second['path']);
    }

    public function testUpdatesExistingPostWithoutChangingPublishedSlug(): void
    {
        $first = $this->synchronize('Prefeitura de Exemplo adota o e-Cidade');
        $second = $this->synchronize('Título alterado após revisão');

        self::assertTrue($second['changed']);
        self::assertFalse($second['created']);
        self::assertSame($first['slug'], $second['slug']);
        self::assertSame($first['path'], $second['path']);

        $content = (string) file_get_contents($second['path']);
        self::assertStringContainsString('title: "Título alterado após revisão"', $content);
    }

    public function testEditingAnUnpublishedDraftDoesNotInventPublicationUpdateDate(): void
    {
        $body = (string) file_get_contents(__DIR__ . '/../../../Fixtures/Editorial/valid-news-issue.md');
        $synchronizer = new NewsIssueSynchronizer($this->postsDirectory);

        $synchronizer->synchronize(
            321,
            'Título inicial',
            $body,
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-07T12:00:00Z',
        );

        $second = $synchronizer->synchronize(
            321,
            'Título corrigido antes da publicação',
            $body,
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-08T15:30:00Z',
        );

        $content = (string) file_get_contents($second['path']);
        self::assertStringNotContainsString('updated_at:', $content);
        self::assertStringContainsString('date: 2026-10-07', $content);
    }

    public function testRevisionPreservesSlugAndOriginalDateWhileRecordingUpdatedAt(): void
    {
        $body = (string) file_get_contents(__DIR__ . '/../../../Fixtures/Editorial/valid-news-issue.md');
        $synchronizer = new NewsIssueSynchronizer($this->postsDirectory);

        $first = $synchronizer->synchronize(
            321,
            'Título original',
            $body,
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-07T12:00:00Z',
        );
        $second = $synchronizer->synchronize(
            321,
            'Título revisado',
            $body,
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-08T15:30:00Z',
            true,
        );

        self::assertSame($first['slug'], $second['slug']);
        self::assertSame($first['path'], $second['path']);

        $content = (string) file_get_contents($second['path']);
        self::assertStringContainsString('date: 2026-10-07', $content);
        self::assertStringContainsString(
            'updated_at: "2026-10-08T15:30:00Z"',
            $content,
        );
        self::assertStringContainsString('github_issue: 321', $content);
    }

    /** @return array{changed:bool,created:bool,path:string,slug:string} */
    private function synchronize(string $title): array
    {
        $body = (string) file_get_contents(__DIR__ . '/../../../Fixtures/Editorial/valid-news-issue.md');

        return (new NewsIssueSynchronizer($this->postsDirectory))->synchronize(
            321,
            $title,
            $body,
            'https://github.com/e-cidade/site/issues/321',
        );
    }
}
