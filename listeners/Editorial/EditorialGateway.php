<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

interface EditorialGateway
{
    /** @param array<string, array{color:string,description:string}> $definitions */
    public function ensureLabels(array $definitions): void;

    /** @param list<string> $stateLabels */
    public function setIssueState(int $issueNumber, EditorialState $state, array $stateLabels): void;

    public function upsertStatus(int $issueNumber, string $marker, string $body): void;

    public function branchExists(string $branch): bool;

    public function findPullRequest(string $branch, string $state = 'open'): ?EditorialPullRequest;

    public function prepareEditorialBranch(string $branch): void;

    public function commitAndPushEditorialChanges(string $branch, int $issueNumber): void;

    public function createDraftPullRequest(string $branch, int $issueNumber): EditorialPullRequest;

    public function closePullRequest(int $pullRequestNumber, string $comment): void;

    public function deleteBranch(string $branch): void;

    public function requestReviewer(int $pullRequestNumber, string $reviewer): void;

    public function pullRequestForCommit(string $commitSha): ?EditorialPullRequest;

    public function closeIssue(int $issueNumber): void;
}
