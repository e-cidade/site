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
        private readonly ?string $workspaceRoot = null,
        private readonly ?string $gitUserName = null,
        private readonly ?string $gitUserEmail = null,
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
        return $this->remoteBranchSha($branch) !== null;
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

    public function prepareEditorialBranch(string $branch): EditorialWorkspace
    {
        $remoteSha = $this->remoteBranchSha($branch);
        $fetch = ['git', 'fetch', 'origin', 'main'];
        if ($remoteSha !== null) {
            $fetch[] = $branch;
        }
        $this->mustRun($fetch);

        $mainSha = trim($this->mustRun(['git', 'rev-parse', 'origin/main'])->stdout);
        $path = $this->workspacePath($branch);
        $this->cleanupWorkspace($path);

        if ($remoteSha === null) {
            $this->mustRun([
                'git', 'worktree', 'add', '--force', '-B', $branch, $path, 'origin/main',
            ]);

            return new EditorialWorkspace($branch, $path, null, false);
        }

        $mergeBase = trim($this->mustRun([
            'git', 'merge-base', 'origin/main', 'origin/' . $branch,
        ])->stdout);

        if ($mergeBase === $mainSha) {
            $this->mustRun([
                'git', 'worktree', 'add', '--force', '-B', $branch, $path, 'origin/' . $branch,
            ]);

            return new EditorialWorkspace($branch, $path, $remoteSha, false);
        }

        $diff = $this->mustRun([
            'git', 'diff', '--name-status',
            'origin/main...origin/' . $branch,
            '--',
            'source/_posts',
            'source/assets/images/news',
            'LICENSES',
        ])->stdout;

        $this->mustRun([
            'git', 'worktree', 'add', '--force', '-B', $branch, $path, 'origin/main',
        ]);

        foreach ($this->editorialChanges($diff) as $change) {
            if ($change['status'] === 'D') {
                $this->runner->run(
                    ['git', '-C', $path, 'rm', '--ignore-unmatch', '--', $change['path']],
                    $this->workingDirectory,
                );
                continue;
            }

            $this->mustRun([
                'git', '-C', $path,
                'restore', '--source=origin/' . $branch,
                '--', $change['path'],
            ]);
        }

        return new EditorialWorkspace($branch, $path, $remoteSha, true);
    }

    public function commitAndPushEditorialChanges(EditorialWorkspace $workspace, int $issueNumber): void
    {
        $path = $workspace->path;
        $gitUserName = $this->gitUserName
            ?? (getenv('EDITORIAL_GIT_USER_NAME') ?: 'github-actions[bot]');
        $gitUserEmail = $this->gitUserEmail
            ?? (getenv('EDITORIAL_GIT_USER_EMAIL')
                ?: '41898282+github-actions[bot]@users.noreply.github.com');

        $this->mustRun(['git', '-C', $path, 'config', 'user.name', $gitUserName]);
        $this->mustRun([
            'git', '-C', $path, 'config', 'user.email', $gitUserEmail,
        ]);
        $this->mustRun([
            'git', '-C', $path, 'add',
            'source/_posts', 'source/assets/images/news', 'LICENSES',
        ]);
        $this->mustRun([
            'git', '-C', $path, 'commit', '--signoff',
            '-m', 'content: sync news issue #' . $issueNumber,
        ]);

        $push = ['git', '-C', $path, 'push'];
        if ($workspace->remoteSha !== null) {
            $push[] = '--force-with-lease=refs/heads/' . $workspace->branch . ':' . $workspace->remoteSha;
        }
        $push[] = '-u';
        $push[] = 'origin';
        $push[] = $workspace->branch;

        $this->mustRun($push);
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

    private function remoteBranchSha(string $branch): ?string
    {
        $result = $this->runner->run(
            ['git', 'ls-remote', '--heads', 'origin', $branch],
            $this->workingDirectory,
        );

        if (! $result->successful() || trim($result->stdout) === '') {
            return null;
        }

        $parts = preg_split('/\s+/', trim($result->stdout));

        return is_array($parts) && isset($parts[0]) ? $parts[0] : null;
    }

    private function workspacePath(string $branch): string
    {
        $root = rtrim($this->workspaceRoot ?? sys_get_temp_dir(), '/');

        return $root . '/e-cidade-editorial-' . substr(hash('sha256', $branch), 0, 12);
    }

    private function cleanupWorkspace(string $path): void
    {
        $this->runner->run(
            ['git', 'worktree', 'remove', '--force', $path],
            $this->workingDirectory,
        );
        $this->runner->run(
            ['git', 'worktree', 'prune'],
            $this->workingDirectory,
        );
    }

    /**
     * @return list<array{status:string,path:string}>
     */
    private function editorialChanges(string $diff): array
    {
        $changes = [];
        foreach (preg_split('/\R/', trim($diff)) ?: [] as $line) {
            if ($line === '') {
                continue;
            }

            $parts = explode("\t", $line);
            if (count($parts) < 2) {
                continue;
            }

            $status = substr($parts[0], 0, 1);
            $path = $parts[count($parts) - 1];
            if (! $this->isEditorialPath($path)) {
                throw new RuntimeException('Unexpected non-editorial path in editorial branch: ' . $path);
            }

            $changes[] = ['status' => $status, 'path' => $path];
        }

        return $changes;
    }

    private function isEditorialPath(string $path): bool
    {
        return str_starts_with($path, 'source/_posts/')
            || str_starts_with($path, 'source/assets/images/news/')
            || str_starts_with($path, 'LICENSES/');
    }

    /** @param list<string> $command */
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
