<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Seo;

final class StructuredDataBuilder
{
    /** @param array{siteUrl:string,canonicalUrl:string,isArticle:bool,pageType:string} $urlData
     *  @return array<string,mixed>
     */
    public function build(
        object $page,
        array $urlData,
        string $pageTitle,
        string $description,
        string $socialImage,
    ): array {
        $websiteId = $urlData['siteUrl'] . '/#website';
        $softwareId = $urlData['siteUrl'] . '/#software';
        $webpageId = $urlData['canonicalUrl'] . '#webpage';

        $graph = [
            [
                '@type' => 'WebSite',
                '@id' => $websiteId,
                'url' => $urlData['siteUrl'] . '/',
                'name' => $page->siteName,
                'description' => $page->siteDescription,
                'inLanguage' => 'pt-BR',
            ],
            [
                '@type' => 'SoftwareApplication',
                '@id' => $softwareId,
                'name' => $page->siteName,
                'url' => $urlData['siteUrl'] . '/',
                'description' => $page->siteDescription,
                'applicationCategory' => 'GovernmentApplication',
                'operatingSystem' => 'Web',
                'isAccessibleForFree' => true,
                'sameAs' => [$page->communityUrl],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $webpageId,
                'url' => $urlData['canonicalUrl'],
                'name' => $pageTitle,
                'description' => $description,
                'image' => $socialImage,
                'inLanguage' => 'pt-BR',
                'isPartOf' => ['@id' => $websiteId],
                'about' => ['@id' => $softwareId],
            ],
        ];

        if ($urlData['isArticle']) {
            $article = [
                '@type' => 'Article',
                '@id' => $urlData['canonicalUrl'] . '#article',
                'url' => $urlData['canonicalUrl'],
                'headline' => $pageTitle,
                'description' => $description,
                'image' => $socialImage,
                'inLanguage' => 'pt-BR',
                'mainEntityOfPage' => ['@id' => $webpageId],
                'author' => [
                    '@type' => 'Organization',
                    'name' => $page->author ?? $page->siteAuthor,
                ],
            ];

            if ($page->date ?? null) {
                $article['datePublished'] = $page->getDate()->format(DATE_ATOM);
            }

            $graph[] = $article;
            $graph[2]['mainEntity'] = ['@id' => $article['@id']];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }
}
