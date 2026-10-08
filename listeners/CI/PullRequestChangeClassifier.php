<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\CI;

final class PullRequestChangeClassifier
{
    /**
     * @param list<string> $paths
     */
    public function isEditorialOnly(array $paths): bool
    {
        if ($paths === []) {
            return false;
        }

        foreach ($paths as $path) {
            if (! $this->isEditorialPath($path)) {
                return false;
            }
        }

        return true;
    }

    public function isEditorialPath(string $path): bool
    {
        return str_starts_with($path, 'source/_posts/')
            || str_starts_with($path, 'source/assets/images/news/');
    }
}
