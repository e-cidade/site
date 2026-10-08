<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

use InvalidArgumentException;

final class EditorialSyncService
{
    public function __construct(
        private readonly EditorialGateway $gateway,
        private readonly EditorialContentSynchronizer $synchronizer,
        private readonly EditorialLifecycle $lifecycle,
        private readonly EditorialIssueDetector $detector = new EditorialIssueDetector(),
    ) {}

    /**
     * @param list<string> $labels
     * @return array{handled:bool,valid:bool,changed:bool,pull_request:?EditorialPullRequest,error:?string}
     */
    public function synchronize(
        int $issueNumber,
        string $title,
        string $body,
        string $issueUrl,
        string $updatedAt,
        array $labels,
        bool $forced = false,
    ): array {
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
            return [
                'handled' => false,
                'valid' => true,
                'changed' => false,
                'pull_request' => null,
                'error' => null,
            ];
        }

        $workspace = $this->gateway->prepareEditorialBranch($branch);

        try {
            $result = $this->synchronizer->synchronize(
                $workspace,
                $issueNumber,
                $title,
                $body,
                $issueUrl,
                $updatedAt,
            );
        } catch (InvalidArgumentException $exception) {
            $this->lifecycle->updateState(
                $issueNumber,
                new EditorialStatus(EditorialState::Invalid, $exception->getMessage()),
            );

            return [
                'handled' => true,
                'valid' => false,
                'changed' => false,
                'pull_request' => $existingPullRequest,
                'error' => $exception->getMessage(),
            ];
        }

        $needsPush = $result['changed'] || $workspace->refreshRequired;
        if ($needsPush) {
            $this->gateway->commitAndPushEditorialChanges($workspace, $issueNumber);
        }

        $pullRequest = $existingPullRequest;
        if ($pullRequest === null && $needsPush) {
            $pullRequest = $this->gateway->createDraftPullRequest($branch, $issueNumber);
        }

        if ($pullRequest !== null) {
            $this->lifecycle->updateState(
                $issueNumber,
                new EditorialStatus(
                    $pullRequest->draft ? EditorialState::Draft : EditorialState::Review,
                    'A notícia está sincronizada. A prévia só será informada depois de sua geração e validação.',
                    pullRequestUrl: $pullRequest->url,
                ),
            );
        }

        return [
            'handled' => true,
            'valid' => true,
            'changed' => $needsPush,
            'pull_request' => $pullRequest,
            'error' => null,
        ];
    }
}
