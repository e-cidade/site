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
    public static function excludedPaths(): iterable
    {
        yield 'assets' => ['/assets/build/app.css', true];
        yield 'favicon' => ['/favicon.ico', true];
        yield '404 page' => ['/404/index.html', true];
        yield 'regular page' => ['/sobre/index.html', false];
    }

    #[DataProvider('excludedPaths')]
    public function testExcludedPaths(string $path, bool $expected): void
    {
        self::assertSame($expected, (new GenerateSitemap())->isExcluded($path));
    }
}
