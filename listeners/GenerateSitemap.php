<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners;

use Illuminate\Support\Str;
use samdark\sitemap\Sitemap;
use TightenCo\Jigsaw\Jigsaw;

final class GenerateSitemap
{
    /** @var list<string> */
    private const EXCLUDED_PATHS = [
        '/assets/*',
        '*/favicon.ico',
        '*/404*',
    ];

    public function handle(Jigsaw $jigsaw): void
    {
        $baseUrl = $jigsaw->getConfig('baseUrl');

        if (! is_string($baseUrl) || $baseUrl === '') {
            return;
        }

        $sitemap = new Sitemap($jigsaw->getDestinationPath() . '/sitemap.xml');

        collect($jigsaw->getOutputPaths())
            ->reject(fn(string $path): bool => $this->isExcluded($path))
            ->each(function (string $path) use ($baseUrl, $sitemap): void {
                $sitemap->addItem(rtrim($baseUrl, '/') . $path, time(), Sitemap::DAILY);
            });

        $sitemap->write();
    }

    public function isExcluded(string $path): bool
    {
        return Str::is(self::EXCLUDED_PATHS, $path);
    }
}
