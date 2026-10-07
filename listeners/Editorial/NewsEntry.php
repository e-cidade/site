<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final readonly class NewsEntry
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public string $source,
        public string $externalId,
        public ?int $issueNumber,
        public string $title,
        public string $slug,
        public string $description,
        public string $publishedAt,
        public ?string $updatedAt,
        public string $author,
        public string $category,
        public string $body,
        public array $tags = [],
        public ?string $cover = null,
        public ?string $coverAlt = null,
        public ?string $coverCaption = null,
        public ?string $sourceUrl = null,
        public ?string $sourceLabel = null,
        public ?string $editorUrl = null,
    ) {}
}
