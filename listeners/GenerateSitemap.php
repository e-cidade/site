<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners;

use samdark\sitemap\Sitemap;
use TightenCo\Jigsaw\Jigsaw;

final class GenerateSitemap
{
    public function handle(Jigsaw $jigsaw): void
    {
        if (! (bool) $jigsaw->getConfig('indexable')) {
            return;
        }

        $siteUrl = rtrim((string) $jigsaw->getConfig('baseUrl'), '/');
        if ($siteUrl === '') {
            return;
        }

        $sitemap = new Sitemap($jigsaw->getDestinationPath() . '/sitemap.xml');

        $jigsaw->getPages()->each(function ($pageData, $path) use ($siteUrl, $sitemap): void {
            $page = is_object($pageData) ? ($pageData->page ?? $pageData) : null;
            $normalizedPath = $this->resolvePath($page, (string) $path);

            if ((is_object($page) && (($page->indexable ?? true) === false || ($page->noindex ?? false)))
                || ! $this->isIndexableHtmlPath($normalizedPath)) {
                return;
            }

            $lastModified = is_object($page)
                ? ($this->resolveTimestamp($page->updated ?? null) ?? $this->resolveTimestamp($page->date ?? null))
                : null;

            $sitemap->addItem(
                $siteUrl . ($normalizedPath === '/' ? '/' : $normalizedPath),
                $lastModified,
                null,
                null,
                $this->resolveImages($siteUrl, $page),
            );
        });

        $sitemap->write();
    }

    /** @return list<string> */
    public function resolveImages(string $siteUrl, mixed $page): array
    {
        if (! is_object($page)) {
            return [];
        }

        foreach (['socialImage', 'cover_image', 'image'] as $property) {
            $value = $page->{$property} ?? null;
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return [$value];
            }

            return [rtrim($siteUrl, '/') . '/' . ltrim($value, '/')];
        }

        return [];
    }

    public function isIndexableHtmlPath(string $path): bool
    {
        if (str_starts_with($path, '/assets/')) {
            return false;
        }

        if (in_array($path, ['/robots.txt', '/sitemap.xml'], true)) {
            return false;
        }

        if ($path === '/404' || str_starts_with($path, '/404/')) {
            return false;
        }

        return pathinfo($path, PATHINFO_EXTENSION) === '' || str_ends_with($path, '.html');
    }

    private function resolvePath(mixed $page, string $fallback): string
    {
        if (is_object($page) && method_exists($page, 'getPath')) {
            return $this->normalizePath((string) $page->getPath());
        }

        return $this->normalizePath($fallback);
    }

    private function normalizePath(string $path): string
    {
        $urlPath = parse_url($path, PHP_URL_PATH);
        if (! is_string($urlPath) || $urlPath === '' || $urlPath === '/') {
            return '/';
        }

        return '/' . trim($urlPath, '/');
    }

    private function resolveTimestamp(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : $timestamp;
    }
}
