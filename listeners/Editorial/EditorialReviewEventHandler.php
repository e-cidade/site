<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class EditorialReviewEventHandler
{
    public function __construct(
        private readonly EditorialLifecycle $lifecycle,
        private readonly string $reviewer,
        private readonly string $previewBaseUrl,
    ) {}

    /** @param array<string,mixed> $payload */
    public function handle(array $payload): void
    {
        $pullRequest = $payload['pull_request'] ?? null;
        if (! is_array($pullRequest)) {
            return;
        }

        $headRef = is_string($pullRequest['head']['ref'] ?? null)
            ? $pullRequest['head']['ref']
            : '';
        if (! str_starts_with($headRef, 'content/news-')) {
            return;
        }

        $number = (int) ($pullRequest['number'] ?? 0);
        $url = is_string($pullRequest['html_url'] ?? null) ? $pullRequest['html_url'] : '';
        $draft = (bool) ($pullRequest['draft'] ?? false);
        if ($number < 1 || $url === '') {
            return;
        }

        $action = is_string($payload['action'] ?? null) ? $payload['action'] : '';
        $reviewState = is_string($payload['review']['state'] ?? null)
            ? strtolower($payload['review']['state'])
            : null;

        $this->lifecycle->reviewState(
            new EditorialPullRequest($number, $url, $headRef, $draft),
            $action,
            $reviewState,
            $this->reviewer,
            $this->previewBaseUrl,
        );
    }
}
