<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\NewsEntry;
use App\Listeners\Editorial\NewsMediaFetcher;
use App\Listeners\Editorial\NewsMediaLocalizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NewsMediaLocalizerTest extends TestCase
{
    private string $mediaDirectory;

    protected function setUp(): void
    {
        $this->mediaDirectory = sys_get_temp_dir() . '/ecidade-news-media-' . bin2hex(random_bytes(6));
        mkdir($this->mediaDirectory, 0775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->mediaDirectory);
    }

    public function testLocalizesCoverAndInlineImagesIntoIssueDirectory(): void
    {
        $fetcher = new FakeNewsMediaFetcher();
        $localizer = new NewsMediaLocalizer($this->mediaDirectory, '/assets/images/news', $fetcher);

        $localized = $localizer->localize($this->entry(
            cover: '![capa](https://github.com/user-attachments/assets/cover-image)',
            body: "Texto.\n\n![Mapa](https://github.com/user-attachments/assets/body-image)\n",
        ));

        self::assertSame('/assets/images/news/321/cover.png', $localized->cover);
        self::assertStringContainsString('/assets/images/news/321/image-', $localized->body);
        self::assertStringNotContainsString('github.com/user-attachments', $localized->body);
        self::assertFileExists($this->mediaDirectory . '/321/cover.png');
        self::assertCount(2, glob($this->mediaDirectory . '/321/*.png') ?: []);
    }

    public function testPreservesAlreadyVersionedHistoricalCover(): void
    {
        $fetcher = new FakeNewsMediaFetcher();
        $localizer = new NewsMediaLocalizer($this->mediaDirectory, '/assets/images/news', $fetcher);

        $localized = $localizer->localize($this->entry(
            cover: '/assets/images/migrated/historical.png',
            body: 'Texto.',
        ));

        self::assertSame('/assets/images/migrated/historical.png', $localized->cover);
        self::assertSame([], $fetcher->calls);
    }

    public function testAllowsLegacyWordPressMediaForMigration(): void
    {
        $fetcher = new FakeNewsMediaFetcher();
        $localizer = new NewsMediaLocalizer($this->mediaDirectory, '/assets/images/news', $fetcher);

        $localized = $localizer->localize($this->entry(
            cover: 'https://ecidade.softwarepublico.org/wp-content/uploads/capa.png',
            body: '<img src="https://ecidade.softwarepublico.org/wp-content/uploads/corpo.png" alt="Exemplo">',
        ));

        self::assertSame('/assets/images/news/321/cover.png', $localized->cover);
        self::assertStringContainsString('/assets/images/news/321/image-', $localized->body);
        self::assertCount(2, $fetcher->calls);
    }

    public function testSameRemoteImageIsDownloadedOnlyOncePerRun(): void
    {
        $fetcher = new FakeNewsMediaFetcher();
        $url = 'https://github.com/user-attachments/assets/shared-image';
        $localizer = new NewsMediaLocalizer($this->mediaDirectory, '/assets/images/news', $fetcher);

        $localized = $localizer->localize($this->entry(
            cover: '![capa](' . $url . ')',
            body: '![Mesma imagem](' . $url . ')',
        ));

        self::assertSame(1, $fetcher->calls[$url] ?? 0);
        self::assertStringContainsString((string) $localized->cover, $localized->body);
        self::assertCount(1, glob($this->mediaDirectory . '/321/*.png') ?: []);
    }

    public function testRejectsMediaFromUntrustedHostBeforeDownloading(): void
    {
        $fetcher = new FakeNewsMediaFetcher();
        $localizer = new NewsMediaLocalizer($this->mediaDirectory, '/assets/images/news', $fetcher);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('News media host is not allowed');

        $localizer->localize($this->entry(
            cover: 'https://example.com/image.png',
            body: 'Texto',
        ));
    }

    public function testReplacementOverwritesDeterministicCoverPath(): void
    {
        $fetcher = new FakeNewsMediaFetcher([
            'https://github.com/user-attachments/assets/first' => FakeNewsMediaFetcher::png('first'),
            'https://github.com/user-attachments/assets/second' => FakeNewsMediaFetcher::png('second'),
        ]);
        $localizer = new NewsMediaLocalizer($this->mediaDirectory, '/assets/images/news', $fetcher);

        $localizer->localize($this->entry('https://github.com/user-attachments/assets/first', 'Texto'));
        $first = (string) file_get_contents($this->mediaDirectory . '/321/cover.png');

        $localizer = new NewsMediaLocalizer($this->mediaDirectory, '/assets/images/news', $fetcher);
        $localizer->localize($this->entry('https://github.com/user-attachments/assets/second', 'Texto'));
        $second = (string) file_get_contents($this->mediaDirectory . '/321/cover.png');

        self::assertNotSame($first, $second);
    }

    private function entry(?string $cover, string $body): NewsEntry
    {
        return new NewsEntry(
            source: 'github',
            externalId: 'github-issue-321',
            issueNumber: 321,
            title: 'Notícia',
            slug: 'noticia',
            description: 'Resumo',
            publishedAt: '2026-10-07',
            updatedAt: null,
            author: 'Comunidade e-Cidade',
            category: 'Notícias',
            body: $body,
            cover: $cover,
            coverAlt: $cover !== null ? 'Descrição da imagem' : null,
            editorUrl: 'https://github.com/e-cidade/site/issues/321',
        );
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = $directory . '/' . $entry;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}

final class FakeNewsMediaFetcher implements NewsMediaFetcher
{
    /** @var array<string, int> */
    public array $calls = [];

    /** @param array<string, string> $responses */
    public function __construct(private readonly array $responses = []) {}

    public function fetch(string $url): string
    {
        $this->calls[$url] = ($this->calls[$url] ?? 0) + 1;

        return $this->responses[$url] ?? self::png($url);
    }

    public static function png(string $seed): string
    {
        $base = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZbK0AAAAASUVORK5CYII=',
            true,
        );

        if (! is_string($base)) {
            throw new \RuntimeException('Unable to build PNG fixture.');
        }

        return $base . $seed;
    }
}
