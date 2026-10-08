<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

use InvalidArgumentException;

final class EditorialIssueEventHandler
{
    public function __construct(
        private readonly EditorialGateway $gateway,
        private readonly EditorialSyncService $syncService,
        private readonly EditorialLifecycle $lifecycle,
        private readonly PublishedNewsLocator $publishedNews,
        private readonly EditorialIssueDetector $detector = new EditorialIssueDetector(),
    ) {}

    /** @param array<string,mixed> $payload */
    public function handle(array $payload, bool $forced = false): int
    {
        $issue = $payload['issue'] ?? null;
        if (! is_array($issue)) {
            throw new InvalidArgumentException('Issue payload not found.');
        }

        $issueNumber = filter_var($issue['number'] ?? null, FILTER_VALIDATE_INT);
        if ($issueNumber === false) {
            throw new InvalidArgumentException('Issue number is invalid.');
        }

        $title = is_string($issue['title'] ?? null) ? $issue['title'] : '';
        $body = is_string($issue['body'] ?? null) ? $issue['body'] : '';
        $issueUrl = is_string($issue['html_url'] ?? null) ? $issue['html_url'] : '';
        $updatedAt = is_string($issue['updated_at'] ?? null) ? $issue['updated_at'] : gmdate(DATE_ATOM);
        $action = is_string($payload['action'] ?? null) ? $payload['action'] : 'dispatch';
        $labels = $this->labelNames($issue['labels'] ?? []);

        $branch = 'content/news-' . $issueNumber;
        $existingPullRequest = $this->gateway->findPullRequest($branch);
        $isNews = $this->detector->isNews(
            $body,
            $labels,
            $this->gateway->branchExists($branch),
            $existingPullRequest !== null,
            $forced,
        );

        if (! $isNews) {
            return 0;
        }

        if ($action === 'closed') {
            if ($this->publishedNews->existsForIssue($issueNumber)) {
                $this->lifecycle->updateState(
                    $issueNumber,
                    new EditorialStatus(EditorialState::Published),
                );

                return 0;
            }

            $this->lifecycle->discard($issueNumber, $branch);

            return 0;
        }

        $result = $this->syncService->synchronize(
            $issueNumber,
            $title,
            $body,
            $issueUrl,
            $updatedAt,
            $labels,
            $forced,
        );

        return $result['valid'] ? 0 : 1;
    }

    /** @return list<string> */
    private function labelNames(mixed $labels): array
    {
        if (! is_array($labels)) {
            return [];
        }

        $names = [];
        foreach ($labels as $label) {
            if (is_string($label)) {
                $names[] = $label;
                continue;
            }

            if (is_array($label) && is_string($label['name'] ?? null)) {
                $names[] = $label['name'];
            }
        }

        return $names;
    }
}
