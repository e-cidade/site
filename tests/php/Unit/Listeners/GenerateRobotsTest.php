<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners;

use App\Listeners\GenerateRobots;
use PHPUnit\Framework\TestCase;

final class GenerateRobotsTest extends TestCase
{
    public function testProductionRobotsPublishesSitemap(): void
    {
        self::assertSame(
            "User-agent: *\nDisallow:\n\nSitemap: https://example.com/sitemap.xml\n",
            (new GenerateRobots())->content(true, 'https://example.com'),
        );
    }

    public function testPreviewRobotsDisallowsCrawling(): void
    {
        self::assertSame(
            "User-agent: *\nDisallow: /\n",
            (new GenerateRobots())->content(false, 'https://preview.example.com'),
        );
    }
}
