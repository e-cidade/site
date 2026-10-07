<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners;

use TightenCo\Jigsaw\Jigsaw;

final class GenerateRobots
{
    public function handle(Jigsaw $jigsaw): void
    {
        $content = $this->content(
            (bool) $jigsaw->getConfig('indexable'),
            rtrim((string) $jigsaw->getConfig('baseUrl'), '/'),
        );

        file_put_contents($jigsaw->getDestinationPath() . '/robots.txt', $content);
    }

    public function content(bool $indexable, string $siteUrl): string
    {
        if (! $indexable) {
            return "User-agent: *\nDisallow: /\n";
        }

        return "User-agent: *\nDisallow:\n\nSitemap: " . rtrim($siteUrl, '/') . "/sitemap.xml\n";
    }
}
