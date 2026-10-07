<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Seo;

use App\Seo\SocialImageResolver;
use PHPUnit\Framework\TestCase;

final class SocialImageResolverTest extends TestCase
{
    public function testCoverImageTakesPriorityOverLogo(): void
    {
        $page = new SeoPageStub('/', [
            'cover_image' => '/assets/cover.png',
            'cover_alt' => 'Capa',
            'logoUrl' => 'https://example.com/logo.png',
        ]);

        $result = (new SocialImageResolver())->resolve($page, 'https://example.com', 'Notícia');

        self::assertSame('https://example.com/assets/cover.png', $result['url']);
        self::assertSame('summary_large_image', $result['twitterCard']);
        self::assertSame('Capa', $result['alt']);
    }

    public function testFallsBackToLogo(): void
    {
        $page = new SeoPageStub('/', ['logoUrl' => 'https://example.com/logo.png']);

        $result = (new SocialImageResolver())->resolve($page, 'https://example.com', 'Página');

        self::assertSame('https://example.com/logo.png', $result['url']);
        self::assertSame('summary', $result['twitterCard']);
    }
}
