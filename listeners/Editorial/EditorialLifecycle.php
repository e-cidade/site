<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class EditorialLifecycle
{
    public function __construct(
        private readonly EditorialGateway $gateway,
        private readonly EditorialStatusRenderer $renderer = new EditorialStatusRenderer(),
    ) {}

    public function updateState(int $issueNumber, EditorialStatus $status): void
    {
        $this->gateway->setIssueState($issueNumber, $status->state, EditorialLabels::stateLabels());
        $this->gateway->upsertStatus(
            $issueNumber,
            EditorialStatusRenderer::MARKER,
            $this->renderer->render($status),
        );
    }

    public function discard(int $issueNumber, string $branch): void
    {
        $pullRequest = $this->gateway->findPullRequest($branch);
        if ($pullRequest !== null) {
            $this->gateway->closePullRequest(
                $pullRequest->number,
                'Ciclo editorial encerrado porque a Issue de origem foi fechada antes da publicação.',
            );
        }

        if ($this->gateway->branchExists($branch)) {
            $this->gateway->deleteBranch($branch);
        }

        $this->updateState(
            $issueNumber,
            new EditorialStatus(
                EditorialState::Discarded,
                'A proposta foi encerrada antes da publicação. O Pull Request e a branch editorial pendentes foram encerrados quando existiam.',
            ),
        );
    }

    public function reviewState(
        EditorialPullRequest $pullRequest,
        string $action,
        ?string $reviewState,
        string $previewBaseUrl,
    ): void {
        $issueNumber = $pullRequest->issueNumber();
        if ($issueNumber === null) {
            return;
        }

        $state = EditorialState::Review;
        if ($action === 'converted_to_draft' || $pullRequest->draft) {
            $state = EditorialState::Draft;
        } elseif ($action === 'submitted' && $reviewState === 'approved') {
            $state = EditorialState::Ready;
        }

        if ($action === 'ready_for_review') {
            $state = EditorialState::Review;
        }

        $this->updateState(
            $issueNumber,
            new EditorialStatus(
                $state,
                pullRequestUrl: $pullRequest->url,
                previewUrl: rtrim($previewBaseUrl, '/') . '/pr-' . $pullRequest->number . '/',
            ),
        );
    }
}
