<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\EditorialLifecycle;
use App\Listeners\Editorial\EditorialState;
use App\Listeners\Editorial\EditorialSyncService;
use PHPUnit\Framework\TestCase;

final class EditorialSyncServiceTest extends TestCase
{
    private string $postsDirectory;

    protected function setUp(): void
    {
        $this->postsDirectory = sys_get_temp_dir() . '/ecidade-editorial-sync-' . bin2hex(random_bytes(5));
    }

    protected function tearDown(): void
    {
        if (! is_dir($this->postsDirectory)) {
            return;
        }

        $this->removeDirectory($this->postsDirectory);
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = $directory . '/' . $entry;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }

    public function testValidIssueCreatesSnapshotPullRequestAndDraftStatus(): void
    {
        $gateway = new FakeEditorialGateway();
        $gateway->workspacePath = $this->postsDirectory . '/workspace';
        $service = new EditorialSyncService(
            $gateway,
            new TestEditorialContentSynchronizer(),
            new EditorialLifecycle($gateway),
        );

        $result = $service->synchronize(
            321,
            'Notícia de teste',
            $this->validBody(),
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-08T00:00:00Z',
            ['editorial/news'],
        );

        self::assertTrue($result['valid']);
        self::assertTrue($result['changed']);
        self::assertTrue($gateway->prepared);
        self::assertSame([['branch' => 'content/news-321', 'issue' => 321]], $gateway->commits);
        self::assertTrue($gateway->createdPullRequest);
        self::assertSame(EditorialState::Draft, $gateway->states[0]['state']);
        self::assertStringContainsString('/pull/999', $gateway->statuses[0]['body']);
    }

    public function testInvalidIssueReportsInvalidStateWithoutCommit(): void
    {
        $gateway = new FakeEditorialGateway();
        $gateway->workspacePath = $this->postsDirectory . '/workspace';
        $service = new EditorialSyncService(
            $gateway,
            new TestEditorialContentSynchronizer(),
            new EditorialLifecycle($gateway),
        );

        $result = $service->synchronize(
            321,
            'Notícia inválida',
            str_replace('2026-10-07', '07/10/2026', $this->validBody()),
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-08T00:00:00Z',
            ['editorial/news'],
        );

        self::assertFalse($result['valid']);
        self::assertSame([], $gateway->commits);
        self::assertFalse($gateway->createdPullRequest);
        self::assertSame(EditorialState::Invalid, $gateway->states[0]['state']);
        self::assertStringContainsString('AAAA-MM-DD', $gateway->statuses[0]['body']);
    }

    public function testEditingDraftReusesTheSamePullRequestAndSkipsIdenticalSnapshots(): void
    {
        $gateway = new FakeEditorialGateway();
        $gateway->workspacePath = $this->postsDirectory . '/workspace';
        $service = new EditorialSyncService(
            $gateway,
            new TestEditorialContentSynchronizer(),
            new EditorialLifecycle($gateway),
        );

        $first = $service->synchronize(
            321,
            'Notícia original',
            $this->validBody(),
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-08T00:00:00Z',
            ['editorial/news'],
        );

        self::assertNotNull($first['pull_request']);
        $gateway->pullRequest = $first['pull_request'];
        $gateway->branchExists = true;

        $updated = $service->synchronize(
            321,
            'Notícia corrigida',
            $this->validBody(),
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-08T00:00:00Z',
            ['editorial/news'],
        );
        $unchanged = $service->synchronize(
            321,
            'Notícia corrigida',
            $this->validBody(),
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-08T00:00:00Z',
            ['editorial/news'],
        );

        self::assertTrue($updated['changed']);
        self::assertFalse($unchanged['changed']);
        self::assertSame($first['pull_request'], $updated['pull_request']);
        self::assertSame($first['pull_request'], $unchanged['pull_request']);
        self::assertCount(2, $gateway->commits);
        self::assertSame(EditorialState::Draft, $gateway->states[2]['state']);
        self::assertCount(3, $gateway->statuses);
    }

    public function testInvalidEditLeavesAnExistingDraftSnapshotAndPullRequestUntouched(): void
    {
        $gateway = new FakeEditorialGateway();
        $gateway->workspacePath = $this->postsDirectory . '/workspace';
        $service = new EditorialSyncService(
            $gateway,
            new TestEditorialContentSynchronizer(),
            new EditorialLifecycle($gateway),
        );

        $first = $service->synchronize(
            321,
            'Notícia válida',
            $this->validBody(),
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-08T00:00:00Z',
            ['editorial/news'],
        );

        self::assertNotNull($first['pull_request']);
        $gateway->pullRequest = $first['pull_request'];
        $gateway->branchExists = true;

        $posts = glob($gateway->workspacePath . '/source/_posts/*.md') ?: [];
        self::assertCount(1, $posts);
        $originalSnapshot = file_get_contents($posts[0]);

        $invalid = $service->synchronize(
            321,
            'Notícia com data inválida',
            str_replace('2026-10-07', '07/10/2026', $this->validBody()),
            'https://github.com/e-cidade/site/issues/321',
            '2026-10-08T00:00:00Z',
            ['editorial/news'],
        );

        self::assertFalse($invalid['valid']);
        self::assertFalse($invalid['changed']);
        self::assertSame($first['pull_request'], $invalid['pull_request']);
        self::assertCount(1, $gateway->commits);
        self::assertSame($originalSnapshot, file_get_contents($posts[0]));
        self::assertSame(EditorialState::Invalid, $gateway->states[1]['state']);
        self::assertStringContainsString('AAAA-MM-DD', $gateway->statuses[1]['body']);
    }

    private function validBody(): string
    {
        return <<<'MD'
### Resumo

Resumo de teste.

### Data da notícia

2026-10-07

### Autor

_No response_

### Texto da notícia

Conteúdo sem mídia.

### Imagem de capa

_No response_

### Texto alternativo da imagem

_No response_

### Detentor dos direitos das imagens

_No response_

### Licença/permissão das imagens

_No response_

### Crédito das imagens

_No response_

### Autorização para publicar as imagens

_No response_

### Fonte original

_No response_

### URL da fonte

_No response_
MD;
    }
}
