<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\EditorialGateway;
use App\Listeners\Editorial\EditorialPullRequest;
use App\Listeners\Editorial\EditorialState;
use App\Listeners\Editorial\EditorialWorkspace;

final class FakeEditorialGateway implements EditorialGateway
{
    /** @var list<array{issue:int,state:EditorialState}> */
    public array $states = [];

    /** @var list<array{issue:int,marker:string,body:string}> */
    public array $statuses = [];

    /** @var list<int> */
    public array $closedPullRequests = [];

    /** @var list<string> */
    public array $deletedBranches = [];

    /** @var list<int> */
    public array $closedIssues = [];

    /** @var list<array{branch:string,issue:int}> */
    public array $commits = [];

    public bool $branchExists = false;
    public ?EditorialPullRequest $pullRequest = null;
    public ?EditorialPullRequest $commitPullRequest = null;
    public bool $prepared = false;
    public bool $createdPullRequest = false;
    public string $workspacePath;

    public function __construct()
    {
        $this->workspacePath = sys_get_temp_dir() . '/ecidade-fake-editorial-workspace-' . bin2hex(random_bytes(5));
    }

    /** @var array<int,array<string,mixed>> */
    public array $issuePayloads = [];

    public function issuePayload(int $issueNumber): array
    {
        return $this->issuePayloads[$issueNumber]
            ?? ['action' => 'dispatch', 'issue' => ['number' => $issueNumber]];
    }

    public function ensureLabels(array $definitions): void {}

    public function setIssueState(int $issueNumber, EditorialState $state, array $stateLabels): void
    {
        $this->states[] = ['issue' => $issueNumber, 'state' => $state];
    }

    public function upsertStatus(int $issueNumber, string $marker, string $body): void
    {
        $this->statuses[] = ['issue' => $issueNumber, 'marker' => $marker, 'body' => $body];
    }

    public function branchExists(string $branch): bool
    {
        return $this->branchExists;
    }

    public function findPullRequest(string $branch, string $state = 'open'): ?EditorialPullRequest
    {
        return $this->pullRequest;
    }

    public function prepareEditorialBranch(string $branch): EditorialWorkspace
    {
        $this->prepared = true;

        return new EditorialWorkspace($branch, $this->workspacePath, null, false);
    }

    public function commitAndPushEditorialChanges(EditorialWorkspace $workspace, int $issueNumber): void
    {
        $this->commits[] = ['branch' => $workspace->branch, 'issue' => $issueNumber];
    }

    public function createDraftPullRequest(string $branch, int $issueNumber): EditorialPullRequest
    {
        $this->createdPullRequest = true;

        return new EditorialPullRequest(
            999,
            'https://github.com/e-cidade/site/pull/999',
            $branch,
            true,
        );
    }

    public function closePullRequest(int $pullRequestNumber, string $comment): void
    {
        $this->closedPullRequests[] = $pullRequestNumber;
        if ($this->pullRequest?->number === $pullRequestNumber) {
            $this->pullRequest = null;
        }
    }

    public function deleteBranch(string $branch): void
    {
        $this->deletedBranches[] = $branch;
        $this->branchExists = false;
    }

    public function pullRequestForCommit(string $commitSha): ?EditorialPullRequest
    {
        return $this->commitPullRequest;
    }

    public function closeIssue(int $issueNumber): void
    {
        $this->closedIssues[] = $issueNumber;
    }
}
