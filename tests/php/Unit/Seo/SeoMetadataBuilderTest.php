<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Seo;

use App\Seo\PageUrlResolver;
use App\Seo\SeoMetadataBuilder;
use App\Seo\SocialImageResolver;
use App\Seo\StructuredDataBuilder;
use PHPUnit\Framework\TestCase;

final class SeoMetadataBuilderTest extends TestCase
{
    private function builder(): SeoMetadataBuilder
    {
        return new SeoMetadataBuilder(
            new PageUrlResolver(),
            new SocialImageResolver(),
            new StructuredDataBuilder(),
        );
    }

    public function testBuildsArticleMetadata(): void
    {
        $page = new SeoPageStub('/noticia/', [
            'baseUrl' => 'https://example.com',
            'production' => true,
            'indexable' => true,
            'siteName' => 'e-Cidade',
            'siteDescription' => 'Descrição do site',
            'siteAuthor' => 'Comunidade e-Cidade',
            'communityUrl' => 'https://github.com/e-cidade/e-cidade',
            'title' => 'Notícia',
            'description' => 'Descrição',
            'author' => 'Comunidade e-Cidade',
            'type' => 'article',
            'date' => strtotime('2026-10-07 12:00:00 UTC'),
            'cover_image' => '/cover.png',
            'cover_alt' => 'Capa',
            'logoUrl' => 'https://example.com/logo.png',
        ]);

        $metadata = $this->builder()->build($page);

        self::assertSame('article', $metadata['ogType']);
        self::assertSame('summary_large_image', $metadata['socialImage']['twitterCard']);
        self::assertTrue($metadata['indexable']);
        self::assertNotNull($metadata['publishedTime']);
        self::assertSame('Article', $metadata['structuredData']['@graph'][3]['@type']);
    }

    public function testPreviewAndNoindexPagesAreNotIndexable(): void
    {
        $base = [
            'baseUrl' => 'https://example.com',
            'production' => true,
            'siteName' => 'e-Cidade',
            'siteDescription' => 'Descrição do site',
            'siteAuthor' => 'Comunidade e-Cidade',
            'communityUrl' => 'https://github.com/e-cidade/e-cidade',
            'logoUrl' => 'https://example.com/logo.png',
        ];

        $preview = new SeoPageStub('/', $base + ['indexable' => false]);
        $notFound = new SeoPageStub('/404/', $base + ['indexable' => true, 'noindex' => true]);

        self::assertFalse($this->builder()->build($preview)['indexable']);
        self::assertFalse($this->builder()->build($notFound)['indexable']);
    }
}
