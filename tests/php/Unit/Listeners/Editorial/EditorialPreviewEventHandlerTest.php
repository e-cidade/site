<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\EditorialLifecycle;
use App\Listeners\Editorial\EditorialPreviewEventHandler;
use App\Listeners\Editorial\EditorialState;
use PHPUnit\Framework\TestCase;

final class EditorialPreviewEventHandlerTest extends TestCase
{
    public function testSuccessfulPreviewUpdatesExistingEditorialStateAndStatus(): void
    {
        $gateway = new FakeEditorialGateway();
        $gateway->issuePayloads[102] = [
            'issue' => [
                'number' => 102,
                'labels' => [
                    ['name' => 'editorial/review'],
                ],
            ],
        ];

        $handler = new EditorialPreviewEventHandler(
            $gateway,
            new EditorialLifecycle($gateway),
            'https://site.example/pr-preview',
        );

        $handler->handle([
            'pull_request' => [
                'number' => 104,
                'html_url' => 'https://github.com/e-cidade/site/pull/104',
                'head' => ['ref' => 'content/news-102'],
            ],
        ]);

        self::assertSame(EditorialState::Review, $gateway->states[0]['state']);
        self::assertStringContainsString(
            'A prévia foi gerada com sucesso',
            $gateway->statuses[0]['body'],
        );
        self::assertStringContainsString(
            'https://site.example/pr-preview/pr-104/',
            $gateway->statuses[0]['body'],
        );
    }

    public function testIgnoresNonEditorialPullRequest(): void
    {
        $gateway = new FakeEditorialGateway();
        $handler = new EditorialPreviewEventHandler(
            $gateway,
            new EditorialLifecycle($gateway),
            'https://site.example/pr-preview',
        );

        $handler->handle([
            'pull_request' => [
                'number' => 200,
                'html_url' => 'https://github.com/e-cidade/site/pull/200',
                'head' => ['ref' => 'feature/example'],
            ],
        ]);

        self::assertSame([], $gateway->states);
        self::assertSame([], $gateway->statuses);
    }
}
