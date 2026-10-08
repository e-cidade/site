<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class PublishedNewsLocator
{
    public function __construct(private readonly string $postsDirectory) {}

    public function existsForIssue(int $issueNumber): bool
    {
        foreach (glob(rtrim($this->postsDirectory, '/') . '/*.md') ?: [] as $path) {
            $content = file_get_contents($path);
            if (is_string($content) && preg_match(
                '/^github_issue:\s*' . preg_quote((string) $issueNumber, '/') . '\s*$/m',
                $content,
            ) === 1) {
                return true;
            }
        }

        return false;
    }
}
