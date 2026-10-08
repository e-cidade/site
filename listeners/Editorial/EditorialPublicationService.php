<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class EditorialPublicationService
{
    public function __construct(
        private readonly EditorialGateway $gateway,
        private readonly EditorialLifecycle $lifecycle,
        private readonly NewsPublicationResolver $resolver,
        private readonly string $baseUrl,
    ) {}

    public function publishForCommit(string $commitSha): bool
    {
        $pullRequest = $this->gateway->pullRequestForCommit($commitSha);
        if ($pullRequest === null) {
            return false;
        }

        $issueNumber = $pullRequest->issueNumber();
        if ($issueNumber === null) {
            return false;
        }

        $publicationUrl = $this->resolver->resolveUrl($issueNumber, $this->baseUrl);

        $this->lifecycle->updateState(
            $issueNumber,
            new EditorialStatus(
                EditorialState::Published,
                'A notícia foi aprovada, incorporada ao site e publicada com sucesso.',
                pullRequestUrl: $pullRequest->url,
                publicationUrl: $publicationUrl,
            ),
        );
        $this->gateway->closeIssue($issueNumber);

        return true;
    }
}
