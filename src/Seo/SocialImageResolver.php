<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Seo;

final class SocialImageResolver
{
    /** @return array{url:string,width:int,height:int,alt:string,twitterCard:string} */
    public function resolve(object $page, string $siteUrl, string $pageTitle): array
    {
        $explicit = $this->firstNonEmptyString([
            $page->socialImage ?? null,
            $page->cover_image ?? null,
            $page->image ?? null,
        ]);
        $image = $explicit ?? $this->firstNonEmptyString([$page->logoUrl ?? null]);

        if ($image === null) {
            throw new \RuntimeException('No social image or logo is configured.');
        }

        $hasPageImage = $explicit !== null;

        return [
            'url' => $this->absoluteUrl($siteUrl, $image),
            'width' => $this->firstPositiveInt([
                $page->socialImageWidth ?? null,
                $page->imageWidth ?? null,
            ]),
            'height' => $this->firstPositiveInt([
                $page->socialImageHeight ?? null,
                $page->imageHeight ?? null,
            ]),
            'alt' => $this->firstNonEmptyString([
                $page->socialImageAlt ?? null,
                $page->cover_alt ?? null,
            ]) ?? $pageTitle,
            'twitterCard' => $hasPageImage ? 'summary_large_image' : 'summary',
        ];
    }

    private function absoluteUrl(string $siteUrl, string $image): string
    {
        if (preg_match('#^https?://#i', $image) === 1) {
            return $image;
        }

        if (str_starts_with($image, '//')) {
            return 'https:' . $image;
        }

        return rtrim($siteUrl, '/') . '/' . ltrim($image, '/');
    }

    /** @param list<mixed> $values */
    private function firstNonEmptyString(array $values): ?string
    {
        foreach ($values as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $candidate = trim((string) $value);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    /** @param list<mixed> $values */
    private function firstPositiveInt(array $values): int
    {
        foreach ($values as $value) {
            $candidate = (int) $value;
            if ($candidate > 0) {
                return $candidate;
            }
        }

        return 0;
    }
}
