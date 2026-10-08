<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class GitHubIssueNewsSource
{
    public function __construct(
        private readonly NewsIssueContract $contract = new NewsIssueContract(),
        private readonly NewsSlugger $slugger = new NewsSlugger(),
        private readonly NewsEntryValidator $validator = new NewsEntryValidator(),
    ) {}

    public function fromIssue(
        int $issueNumber,
        string $title,
        string $body,
        string $issueUrl,
        ?string $existingSlug = null,
        ?string $updatedAt = null,
    ): NewsEntry {
        $data = $this->contract->parse($issueNumber, $title, $body);
        $slug = $existingSlug !== null && trim($existingSlug) !== ''
            ? trim($existingSlug)
            : ($data['preferred_slug'] ?? $this->slugger->slug($data['title']));

        $entry = new NewsEntry(
            source: 'github',
            externalId: 'github-issue-' . $issueNumber,
            issueNumber: $issueNumber,
            title: $data['title'],
            slug: $slug,
            description: $data['summary'],
            publishedAt: $data['published_at'],
            updatedAt: $updatedAt,
            author: $data['author'],
            category: 'Notícias',
            body: $data['content'],
            cover: $data['cover'],
            coverAlt: $data['cover_alt'],
            sourceUrl: $data['source_url'],
            sourceLabel: $data['source_label'],
            editorUrl: $issueUrl,
            mediaCopyright: $data['media_copyright'],
            mediaLicense: $data['media_license'],
            mediaCredit: $data['media_credit'],
        );

        $this->validator->validate($entry);

        return $entry;
    }
}
