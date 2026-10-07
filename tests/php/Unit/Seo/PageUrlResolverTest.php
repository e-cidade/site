<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Seo;

use App\Seo\PageUrlResolver;
use PHPUnit\Framework\TestCase;

final class PageUrlResolverTest extends TestCase
{
    public function testResolvesCanonicalUrlWithoutTrailingSlash(): void
    {
        $page = new SeoPageStub('/noticias/exemplo/', ['baseUrl' => 'https://example.com']);

        $result = (new PageUrlResolver())->resolve($page);

        self::assertSame('https://example.com/noticias/exemplo', $result['canonicalUrl']);
    }

    public function testDetectsArticles(): void
    {
        $page = new SeoPageStub('/noticia/', ['baseUrl' => 'https://example.com', 'type' => 'article']);

        self::assertTrue((new PageUrlResolver())->resolve($page)['isArticle']);
    }
}
