<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners;

use App\Listeners\GenerateSitemap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GenerateSitemapTest extends TestCase
{
    #[DataProvider('indexablePathProvider')]
    public function testDetectsIndexablePaths(string $path, bool $expected): void
    {
        self::assertSame($expected, (new GenerateSitemap())->isIndexableHtmlPath($path));
    }

    public static function indexablePathProvider(): iterable
    {
        yield 'home' => ['/', true];
        yield 'regular page' => ['/sobre', true];
        yield 'html page' => ['/noticias/index.html', true];
        yield 'asset' => ['/assets/build/app.css', false];
        yield 'robots' => ['/robots.txt', false];
        yield 'sitemap' => ['/sitemap.xml', false];
        yield '404' => ['/404', false];
    }

    public function testResolvesCoverImageForSitemap(): void
    {
        $page = (object) ['cover_image' => '/assets/images/news.png'];

        self::assertSame(
            ['https://example.com/assets/images/news.png'],
            (new GenerateSitemap())->resolveImages('https://example.com', $page),
        );
    }

    public function testAbsoluteImageRemainsUnchanged(): void
    {
        $page = (object) ['socialImage' => 'https://cdn.example.com/social.jpg'];

        self::assertSame(
            ['https://cdn.example.com/social.jpg'],
            (new GenerateSitemap())->resolveImages('https://example.com', $page),
        );
    }
}
