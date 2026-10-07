<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\NewsPublicationResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class NewsPublicationResolverTest extends TestCase
{
    private string $postsDirectory;

    protected function setUp(): void
    {
        $this->postsDirectory = sys_get_temp_dir() . '/ecidade-news-publication-' . bin2hex(random_bytes(6));
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

    public function testResolvesPublishedUrlFromIssueIdentity(): void
    {
        file_put_contents(
            $this->postsDirectory . '/prefeitura-exemplo.md',
            "---\ngithub_issue: 321\n---\nConteúdo\n",
        );

        $url = (new NewsPublicationResolver($this->postsDirectory))->resolveUrl(
            321,
            'https://site-ecidade.librecode.coop/',
        );

        self::assertSame('https://site-ecidade.librecode.coop/prefeitura-exemplo', $url);
    }

    public function testFailsWhenIssueHasNoPublishedPost(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Published news post not found for issue #999');

        (new NewsPublicationResolver($this->postsDirectory))->resolveUrl(
            999,
            'https://site-ecidade.librecode.coop',
        );
    }
}
