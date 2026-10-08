<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use TightenCo\Jigsaw\Jigsaw;

final class RewriteLocalImageUrls
{
    public function handle(Jigsaw $jigsaw): void
    {
        $baseUrl = rtrim((string) $jigsaw->getConfig('baseUrl'), '/');
        if ($baseUrl === '') {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $jigsaw->getDestinationPath(),
                RecursiveDirectoryIterator::SKIP_DOTS,
            ),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || strtolower($file->getExtension()) !== 'html') {
                continue;
            }

            $path = $file->getPathname();
            $content = file_get_contents($path);
            if (! is_string($content)) {
                continue;
            }

            $rewritten = $this->rewrite($content, $baseUrl);
            if ($rewritten !== $content) {
                file_put_contents($path, $rewritten);
            }
        }
    }

    public function rewrite(string $html, string $baseUrl): string
    {
        $baseUrl = rtrim($baseUrl, '/');
        if ($baseUrl === '') {
            return $html;
        }

        return preg_replace(
            '/\bsrc=(["\'])\/assets\/images\//i',
            'src=$1' . $baseUrl . '/assets/images/',
            $html,
        ) ?? $html;
    }
}
