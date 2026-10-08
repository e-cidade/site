<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class EditorialPreviewEventHandler
{
    public function __construct(
        private readonly EditorialGateway $gateway,
        private readonly EditorialLifecycle $lifecycle,
        private readonly string $previewBaseUrl,
    ) {}

    /** @param array<string,mixed> $payload */
    public function handle(array $payload, string $outcome = 'success'): void
    {
        $pullRequest = $payload['pull_request'] ?? null;
        if (! is_array($pullRequest)) {
            return;
        }

        $headRef = is_string($pullRequest['head']['ref'] ?? null)
            ? $pullRequest['head']['ref']
            : '';

        if (preg_match('/^content\/news-(\d+)$/', $headRef, $match) !== 1) {
            return;
        }

        $issueNumber = (int) $match[1];
        $prNumber = (int) ($pullRequest['number'] ?? 0);
        $prUrl = is_string($pullRequest['html_url'] ?? null)
            ? $pullRequest['html_url']
            : '';

        if ($prNumber < 1 || $prUrl === '') {
            return;
        }

        $issuePayload = $this->gateway->issuePayload($issueNumber);
        $state = $this->stateFromIssue($issuePayload['issue'] ?? null);

        $success = $outcome === 'success';
        $this->lifecycle->updateState(
            $issueNumber,
            new EditorialStatus(
                $state,
                $success
                    ? 'A prévia foi gerada com sucesso para a versão atual desta notícia.'
                    : 'Não foi possível gerar a prévia para a versão atual. Consulte os checks do Pull Request para identificar a falha.',
                pullRequestUrl: $prUrl,
                previewUrl: $success
                    ? rtrim($this->previewBaseUrl, '/') . '/pr-' . $prNumber . '/'
                    : null,
            ),
        );
    }

    private function stateFromIssue(mixed $issue): EditorialState
    {
        if (! is_array($issue) || ! is_array($issue['labels'] ?? null)) {
            return EditorialState::Draft;
        }

        $labels = [];
        foreach ($issue['labels'] as $label) {
            if (is_array($label) && is_string($label['name'] ?? null)) {
                $labels[] = $label['name'];
            }
        }

        foreach (EditorialState::cases() as $state) {
            if (in_array($state->label(), $labels, true)) {
                return $state;
            }
        }

        return EditorialState::Draft;
    }
}
