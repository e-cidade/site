<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

use DateTimeImmutable;
use InvalidArgumentException;

final class WordPressNewsSource
{
    public function __construct(
        private readonly NewsEntryValidator $validator = new NewsEntryValidator(),
    ) {}

    /**
     * Accepts the normalized shape already produced by the WordPress synchronization layer.
     *
     * @param array{id:int,slug:string,title:string,description:string,date:string,modified:string,author:string,category:string,content:string,source_url:string,featured_media_url:?string,featured_media_alt:string} $post
     */
    public function fromNormalizedPost(array $post, ?string $localCover = null): NewsEntry
    {
        $updatedAt = $this->normalizeDateTime($post['modified']);

        $entry = new NewsEntry(
            source: 'wordpress',
            externalId: 'wordpress-' . $post['id'],
            issueNumber: null,
            title: trim($post['title']),
            slug: trim($post['slug']),
            description: trim($post['description']),
            publishedAt: trim($post['date']),
            updatedAt: $updatedAt,
            author: trim($post['author']),
            category: trim($post['category']),
            body: trim($post['content']),
            cover: $localCover,
            coverAlt: $localCover !== null ? trim($post['featured_media_alt']) : null,
            sourceUrl: trim($post['source_url']) !== '' ? trim($post['source_url']) : null,
            sourceLabel: trim($post['source_url']) !== '' ? 'Site e-Cidade' : null,
            editorUrl: trim($post['source_url']) !== '' ? trim($post['source_url']) : null,
        );

        $this->validator->validate($entry);

        return $entry;
    }

    private function normalizeDateTime(string $value): ?string
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->format(DATE_ATOM);
        } catch (\Exception $exception) {
            throw new InvalidArgumentException('Updated date is invalid.', previous: $exception);
        }
    }
}
