<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class NewsPublicationResolver
{
    public function __construct(private readonly string $postsDirectory) {}

    public function resolveUrl(int $issueNumber, string $baseUrl): string
    {
        foreach (glob($this->postsDirectory . '/*.md') ?: [] as $path) {
            $content = (string) file_get_contents($path);
            if (preg_match('/^github_issue:\s*' . preg_quote((string) $issueNumber, '/') . '\s*$/m', $content) !== 1) {
                continue;
            }

            $slug = pathinfo($path, PATHINFO_FILENAME);

            return rtrim($baseUrl, '/') . '/' . $slug;
        }

        throw new \RuntimeException('Published news post not found for issue #' . $issueNumber);
    }
}
