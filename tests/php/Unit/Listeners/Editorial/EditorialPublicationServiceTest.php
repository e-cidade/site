<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\EditorialLifecycle;
use App\Listeners\Editorial\EditorialPublicationService;
use App\Listeners\Editorial\EditorialPullRequest;
use App\Listeners\Editorial\EditorialState;
use App\Listeners\Editorial\NewsPublicationResolver;
use PHPUnit\Framework\TestCase;

final class EditorialPublicationServiceTest extends TestCase
{
    private string $postsDirectory;

    protected function setUp(): void
    {
        $this->postsDirectory = sys_get_temp_dir()
            . '/ecidade-publication-' . bin2hex(random_bytes(5));
        mkdir($this->postsDirectory, 0775, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->postsDirectory . '/*') ?: [] as $path) {
            unlink($path);
        }

        if (is_dir($this->postsDirectory)) {
            rmdir($this->postsDirectory);
        }
    }

    public function testPublishedEditorialCommitReportsUrlAndClosesIssue(): void
    {
        file_put_contents(
            $this->postsDirectory . '/noticia-de-teste.md',
            "---\ngithub_issue: 102\n---\n\nConteúdo.\n",
        );

        $gateway = new FakeEditorialGateway();
        $gateway->commitPullRequest = new EditorialPullRequest(
            104,
            'https://github.com/e-cidade/site/pull/104',
            'content/news-102',
            false,
        );

        $published = (new EditorialPublicationService(
            $gateway,
            new EditorialLifecycle($gateway),
            new NewsPublicationResolver($this->postsDirectory),
            'https://site.example',
        ))->publishForCommit('merge-sha');

        self::assertTrue($published);
        self::assertSame(EditorialState::Published, $gateway->states[0]['state']);
        self::assertSame([102], $gateway->closedIssues);
        self::assertStringContainsString(
            'Publicação: https://site.example/noticia-de-teste',
            $gateway->statuses[0]['body'],
        );
    }

    public function testUnrelatedCommitDoesNotTouchEditorialState(): void
    {
        $gateway = new FakeEditorialGateway();

        $published = (new EditorialPublicationService(
            $gateway,
            new EditorialLifecycle($gateway),
            new NewsPublicationResolver($this->postsDirectory),
            'https://site.example',
        ))->publishForCommit('unrelated-sha');

        self::assertFalse($published);
        self::assertSame([], $gateway->states);
        self::assertSame([], $gateway->closedIssues);
    }
}
