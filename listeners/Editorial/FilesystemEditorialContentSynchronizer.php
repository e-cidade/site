<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class FilesystemEditorialContentSynchronizer implements EditorialContentSynchronizer
{
    public function __construct(
        private readonly NewsMediaFetcher $mediaFetcher = new NativeNewsMediaFetcher(),
    ) {}

    public function synchronize(
        EditorialWorkspace $workspace,
        int $issueNumber,
        string $title,
        string $body,
        string $issueUrl,
        string $updatedAt,
    ): array {
        $root = rtrim($workspace->path, '/');

        return (new NewsIssueSynchronizer(
            postsDirectory: $root . '/source/_posts',
            mediaLocalizer: new NewsMediaLocalizer(
                $root . '/source/assets/images/news',
                '/assets/images/news',
                $this->mediaFetcher,
            ),
        ))->synchronize(
            $issueNumber,
            $title,
            $body,
            $issueUrl,
            $updatedAt,
        );
    }
}
