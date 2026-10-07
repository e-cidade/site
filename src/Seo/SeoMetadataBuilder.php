<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Seo;

final class SeoMetadataBuilder
{
    public function __construct(
        private readonly PageUrlResolver $urlResolver,
        private readonly SocialImageResolver $socialImageResolver,
        private readonly StructuredDataBuilder $structuredDataBuilder,
    ) {}

    /** @return array<string,mixed> */
    public function build(object $page): array
    {
        $urlData = $this->urlResolver->resolve($page);
        $pageTitle = (string) ($page->title ?? $page->siteName);
        $documentTitle = ($page->title ?? null)
            ? $pageTitle . ' | ' . $page->siteName
            : $pageTitle;
        $description = (string) ($page->description ?? $page->siteDescription);
        $socialImage = $this->socialImageResolver->resolve($page, $urlData['siteUrl'], $pageTitle);

        return [
            'pageTitle' => $pageTitle,
            'documentTitle' => $documentTitle,
            'description' => $description,
            'authorName' => (string) ($page->author ?? $page->siteAuthor),
            'canonicalUrl' => $urlData['canonicalUrl'],
            'indexable' => (bool) ($page->production ?? false)
                && (bool) ($page->indexable ?? true)
                && ! (bool) ($page->noindex ?? false),
            'ogType' => $urlData['isArticle'] ? 'article' : 'website',
            'ogLocale' => 'pt_BR',
            'siteName' => (string) $page->siteName,
            'publishedTime' => $urlData['isArticle'] && ($page->date ?? null)
                ? $page->getDate()->format(DATE_ATOM)
                : null,
            'socialImage' => $socialImage,
            'structuredData' => $this->structuredDataBuilder->build(
                $page,
                $urlData,
                $pageTitle,
                $description,
                $socialImage['url'],
            ),
        ];
    }
}
