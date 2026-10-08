<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\EditorialContentSynchronizer;
use App\Listeners\Editorial\EditorialWorkspace;
use App\Listeners\Editorial\NewsIssueSynchronizer;

final class TestEditorialContentSynchronizer implements EditorialContentSynchronizer
{
    public function synchronize(
        EditorialWorkspace $workspace,
        int $issueNumber,
        string $title,
        string $body,
        string $issueUrl,
        string $updatedAt,
    ): array {
        $postsDirectory = $workspace->path . '/source/_posts';
        if (! is_dir($postsDirectory)) {
            mkdir($postsDirectory, 0775, true);
        }

        return (new NewsIssueSynchronizer($postsDirectory))->synchronize(
            $issueNumber,
            $title,
            $body,
            $issueUrl,
            $updatedAt,
        );
    }
}
