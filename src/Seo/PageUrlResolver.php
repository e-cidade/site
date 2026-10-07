<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Seo;

final class PageUrlResolver
{
    /** @return array{siteUrl:string,path:string,canonicalUrl:string,isArticle:bool,pageType:string} */
    public function resolve(object $page): array
    {
        $siteUrl = rtrim((string) ($page->baseUrl ?? ''), '/');
        $path = $this->normalizePath('/' . ltrim((string) $page->getPath(), '/'));
        $isArticle = ($page->type ?? null) === 'article' || ($page->schemaType ?? null) === 'Article';

        return [
            'siteUrl' => $siteUrl,
            'path' => $path,
            'canonicalUrl' => $siteUrl . ($path === '/' ? '/' : $path),
            'isArticle' => $isArticle,
            'pageType' => $isArticle ? 'Article' : 'WebPage',
        ];
    }

    private function normalizePath(string $path): string
    {
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
