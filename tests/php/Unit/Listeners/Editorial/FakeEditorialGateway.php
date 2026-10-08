<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\EditorialGateway;
use App\Listeners\Editorial\EditorialPullRequest;
use App\Listeners\Editorial\EditorialState;

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

    /** @var list<array{pr:int,reviewer:string}> */
    public array $reviewRequests = [];

    /** @var list<int> */
    public array $closedIssues = [];

    /** @var list<array{branch:string,issue:int}> */
    public array $commits = [];

    public bool $branchExists = false;
    public ?EditorialPullRequest $pullRequest = null;
    public ?EditorialPullRequest $commitPullRequest = null;
    public bool $prepared = false;
    public bool $createdPullRequest = false;

    public function issuePayload(int $issueNumber): array
    {
        return ['action' => 'dispatch', 'issue' => ['number' => $issueNumber]];
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

    public function prepareEditorialBranch(string $branch): void
    {
        $this->prepared = true;
    }

    public function commitAndPushEditorialChanges(string $branch, int $issueNumber): void
    {
        $this->commits[] = ['branch' => $branch, 'issue' => $issueNumber];
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
    }

    public function deleteBranch(string $branch): void
    {
        $this->deletedBranches[] = $branch;
    }

    public function requestReviewer(int $pullRequestNumber, string $reviewer): void
    {
        $this->reviewRequests[] = ['pr' => $pullRequestNumber, 'reviewer' => $reviewer];
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
