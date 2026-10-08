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
        public ?string $mediaCopyright = null,
        public ?string $mediaLicense = null,
        public ?string $mediaSourceUrl = null,
        public ?string $mediaCredit = null,
    ) {}

    public function withLocalizedMedia(string $body, ?string $cover): self
    {
        return new self(
            source: $this->source,
            externalId: $this->externalId,
            issueNumber: $this->issueNumber,
            title: $this->title,
            slug: $this->slug,
            description: $this->description,
            publishedAt: $this->publishedAt,
            updatedAt: $this->updatedAt,
            author: $this->author,
            category: $this->category,
            body: $body,
            tags: $this->tags,
            cover: $cover,
            coverAlt: $cover !== null ? $this->coverAlt : null,
            coverCaption: $this->coverCaption,
            sourceUrl: $this->sourceUrl,
            sourceLabel: $this->sourceLabel,
            editorUrl: $this->editorUrl,
            mediaCopyright: $this->mediaCopyright,
            mediaLicense: $this->mediaLicense,
            mediaSourceUrl: $this->mediaSourceUrl,
            mediaCredit: $this->mediaCredit,
        );
    }
}
