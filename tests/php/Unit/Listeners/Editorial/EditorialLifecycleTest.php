<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\EditorialLifecycle;
use App\Listeners\Editorial\EditorialPullRequest;
use App\Listeners\Editorial\EditorialState;
use App\Listeners\Editorial\EditorialStatus;
use App\Listeners\Editorial\EditorialStatusRenderer;
use PHPUnit\Framework\TestCase;

final class EditorialLifecycleTest extends TestCase
{
    public function testStatusRendererProducesSingleStableComment(): void
    {
        $body = (new EditorialStatusRenderer())->render(new EditorialStatus(
            EditorialState::Review,
            'Aguardando revisão.',
            'https://github.com/e-cidade/site/pull/104',
            'https://site.example/pr-preview/pr-104/',
        ));

        self::assertStringStartsWith(EditorialStatusRenderer::MARKER, $body);
        self::assertStringContainsString('**Em revisão**', $body);
        self::assertStringContainsString('Aguardando revisão.', $body);
        self::assertStringContainsString('Pull Request: https://github.com/e-cidade/site/pull/104', $body);
        self::assertStringContainsString('Prévia: https://site.example/pr-preview/pr-104/', $body);
    }

    public function testDiscardClosesPullRequestDeletesBranchAndUpdatesState(): void
    {
        $gateway = new FakeEditorialGateway();
        $gateway->branchExists = true;
        $gateway->pullRequest = new EditorialPullRequest(
            104,
            'https://github.com/e-cidade/site/pull/104',
            'content/news-102',
            true,
        );

        (new EditorialLifecycle($gateway))->discard(102, 'content/news-102');

        self::assertSame([104], $gateway->closedPullRequests);
        self::assertSame(['content/news-102'], $gateway->deletedBranches);
        self::assertSame(EditorialState::Discarded, $gateway->states[0]['state']);
        self::assertStringContainsString('**Descartada**', $gateway->statuses[0]['body']);
    }

    public function testDiscardIsIdempotentForExternalEffects(): void
    {
        $gateway = new FakeEditorialGateway();
        $gateway->branchExists = true;
        $gateway->pullRequest = new EditorialPullRequest(
            104,
            'https://github.com/e-cidade/site/pull/104',
            'content/news-102',
            true,
        );

        $lifecycle = new EditorialLifecycle($gateway);
        $lifecycle->discard(102, 'content/news-102');
        $lifecycle->discard(102, 'content/news-102');

        self::assertSame([104], $gateway->closedPullRequests);
        self::assertSame(['content/news-102'], $gateway->deletedBranches);
        self::assertCount(2, $gateway->states);
        self::assertSame(EditorialState::Discarded, $gateway->states[1]['state']);
    }

    public function testApprovedReviewMovesIssueToReady(): void
    {
        $gateway = new FakeEditorialGateway();
        $pullRequest = new EditorialPullRequest(
            104,
            'https://github.com/e-cidade/site/pull/104',
            'content/news-102',
            false,
        );

        (new EditorialLifecycle($gateway))->reviewState(
            $pullRequest,
            'submitted',
            'approved',
            'https://site.example/pr-preview',
        );

        self::assertSame(EditorialState::Ready, $gateway->states[0]['state']);
        self::assertStringContainsString('**Aprovada para publicação**', $gateway->statuses[0]['body']);
    }

    public function testReadyForReviewMovesIssueToReviewState(): void
    {
        $gateway = new FakeEditorialGateway();
        $pullRequest = new EditorialPullRequest(
            104,
            'https://github.com/e-cidade/site/pull/104',
            'content/news-102',
            false,
        );

        (new EditorialLifecycle($gateway))->reviewState(
            $pullRequest,
            'ready_for_review',
            null,
            'https://site.example/pr-preview',
        );

        self::assertSame(EditorialState::Review, $gateway->states[0]['state']);
    }
}
