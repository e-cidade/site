<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

use RuntimeException;

final class GhEditorialGateway implements EditorialGateway
{
    public function __construct(
        private readonly string $repository,
        private readonly string $workingDirectory,
        private readonly ProcessRunner $runner = new NativeProcessRunner(),
    ) {}

    public function issuePayload(int $issueNumber): array
    {
        $result = $this->mustRun([
            'gh', 'api',
            'repos/' . $this->repository . '/issues/' . $issueNumber,
        ]);

        $issue = json_decode($result->stdout, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($issue)) {
            throw new RuntimeException('Unable to read Issue #' . $issueNumber . '.');
        }

        return ['action' => 'dispatch', 'issue' => $issue];
    }

    public function ensureLabels(array $definitions): void
    {
        foreach ($definitions as $name => $definition) {
            $this->mustRun([
                'gh', 'label', 'create', $name,
                '--repo', $this->repository,
                '--color', $definition['color'],
                '--description', $definition['description'],
                '--force',
            ]);
        }
    }

    public function setIssueState(int $issueNumber, EditorialState $state, array $stateLabels): void
    {
        foreach ($stateLabels as $label) {
            if ($label === $state->label()) {
                continue;
            }

            $this->runner->run(
                ['gh', 'issue', 'edit', (string) $issueNumber, '--repo', $this->repository, '--remove-label', $label],
                $this->workingDirectory,
            );
        }

        $this->mustRun([
            'gh', 'issue', 'edit', (string) $issueNumber,
            '--repo', $this->repository,
            '--add-label', EditorialLabels::NEWS,
            '--add-label', $state->label(),
        ]);
    }

    public function upsertStatus(int $issueNumber, string $marker, string $body): void
    {
        $result = $this->mustRun([
            'gh', 'api',
            'repos/' . $this->repository . '/issues/' . $issueNumber . '/comments',
            '--paginate',
        ]);

        $comments = json_decode($result->stdout, true, 512, JSON_THROW_ON_ERROR);
        $commentId = null;
        foreach (is_array($comments) ? $comments : [] as $comment) {
            if (is_array($comment) && str_contains((string) ($comment['body'] ?? ''), $marker)) {
                $commentId = (int) ($comment['id'] ?? 0);
                break;
            }
        }

        if ($commentId > 0) {
            $this->mustRun([
                'gh', 'api', '--method', 'PATCH',
                'repos/' . $this->repository . '/issues/comments/' . $commentId,
                '-f', 'body=' . $body,
            ]);

            return;
        }

        $this->mustRun([
            'gh', 'api', '--method', 'POST',
            'repos/' . $this->repository . '/issues/' . $issueNumber . '/comments',
            '-f', 'body=' . $body,
        ]);
    }

    public function branchExists(string $branch): bool
    {
        return $this->runner->run(
            ['git', 'ls-remote', '--exit-code', '--heads', 'origin', $branch],
            $this->workingDirectory,
        )->successful();
    }

    public function findPullRequest(string $branch, string $state = 'open'): ?EditorialPullRequest
    {
        $result = $this->mustRun([
            'gh', 'pr', 'list',
            '--repo', $this->repository,
            '--head', $branch,
            '--state', $state,
            '--json', 'number,url,headRefName,isDraft',
        ]);

        $items = json_decode($result->stdout, true, 512, JSON_THROW_ON_ERROR);
        $item = is_array($items) ? ($items[0] ?? null) : null;
        if (! is_array($item)) {
            return null;
        }

        return new EditorialPullRequest(
            (int) $item['number'],
            (string) $item['url'],
            (string) $item['headRefName'],
            (bool) $item['isDraft'],
        );
    }

    public function prepareEditorialBranch(string $branch): void
    {
        $this->mustRun(['git', 'config', 'user.name', 'github-actions[bot]']);
        $this->mustRun(['git', 'config', 'user.email', '41898282+github-actions[bot]@users.noreply.github.com']);

        if ($this->branchExists($branch)) {
            $this->mustRun(['git', 'fetch', 'origin', 'main', $branch]);
            $this->mustRun(['git', 'checkout', '-B', $branch, 'origin/' . $branch]);
            $this->mustRun(['git', 'rebase', 'origin/main']);

            return;
        }

        $this->mustRun(['git', 'fetch', 'origin', 'main']);
        $this->mustRun(['git', 'checkout', '-B', $branch, 'origin/main']);
    }

    public function commitAndPushEditorialChanges(string $branch, int $issueNumber): void
    {
        $this->mustRun(['git', 'add', 'source/_posts', 'source/assets/images/news', 'LICENSES']);
        $this->mustRun(['git', 'commit', '--signoff', '-m', 'content: sync news issue #' . $issueNumber]);
        $this->mustRun(['git', 'push', '--force-with-lease', '-u', 'origin', $branch]);
    }

    public function createDraftPullRequest(string $branch, int $issueNumber): EditorialPullRequest
    {
        $result = $this->mustRun([
            'gh', 'pr', 'create',
            '--repo', $this->repository,
            '--draft',
            '--base', 'main',
            '--head', $branch,
            '--title', 'content: publish news from issue #' . $issueNumber,
            '--body', 'Generated automatically from #' . $issueNumber . '. The Issue is the editorial source; this PR is the reviewable publication snapshot.',
        ]);

        $url = trim($result->stdout);
        if (preg_match('~/pull/(\d+)$~', $url, $match) !== 1) {
            throw new RuntimeException('Unable to determine created pull request number.');
        }

        return new EditorialPullRequest((int) $match[1], $url, $branch, true);
    }

    public function closePullRequest(int $pullRequestNumber, string $comment): void
    {
        $this->mustRun([
            'gh', 'pr', 'close', (string) $pullRequestNumber,
            '--repo', $this->repository,
            '--comment', $comment,
        ]);
    }

    public function deleteBranch(string $branch): void
    {
        $this->mustRun(['git', 'push', 'origin', '--delete', $branch]);
    }

    public function requestReviewer(int $pullRequestNumber, string $reviewer): void
    {
        $this->mustRun([
            'gh', 'pr', 'edit', (string) $pullRequestNumber,
            '--repo', $this->repository,
            '--add-reviewer', $reviewer,
        ]);
    }

    public function pullRequestForCommit(string $commitSha): ?EditorialPullRequest
    {
        $result = $this->mustRun([
            'gh', 'api',
            'repos/' . $this->repository . '/commits/' . $commitSha . '/pulls',
        ]);

        $items = json_decode($result->stdout, true, 512, JSON_THROW_ON_ERROR);
        foreach (is_array($items) ? $items : [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $headRef = (string) ($item['head']['ref'] ?? '');
            if (! str_starts_with($headRef, 'content/news-')) {
                continue;
            }

            return new EditorialPullRequest(
                (int) $item['number'],
                (string) $item['html_url'],
                $headRef,
                false,
            );
        }

        return null;
    }

    public function closeIssue(int $issueNumber): void
    {
        $this->mustRun([
            'gh', 'issue', 'close', (string) $issueNumber,
            '--repo', $this->repository,
            '--reason', 'completed',
        ]);
    }

    private function mustRun(array $command): ProcessResult
    {
        $result = $this->runner->run($command, $this->workingDirectory);
        if (! $result->successful()) {
            throw new RuntimeException(
                sprintf(
                    "Command failed (%d): %s\n%s",
                    $result->exitCode,
                    implode(' ', $command),
                    trim($result->stderr),
                ),
            );
        }

        return $result;
    }
}
