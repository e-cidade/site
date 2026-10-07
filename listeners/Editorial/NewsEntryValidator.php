<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

use DateTimeImmutable;
use InvalidArgumentException;

final class NewsEntryValidator
{
    public function validate(NewsEntry $entry): void
    {
        $this->required($entry->externalId, 'External ID');
        $this->required($entry->title, 'Title');
        $this->required($entry->slug, 'Slug');
        $this->required($entry->description, 'Description');
        $this->required($entry->author, 'Author');
        $this->required($entry->category, 'Category');
        $this->required($entry->body, 'Body');

        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $entry->slug) !== 1) {
            throw new InvalidArgumentException('Slug must contain only lowercase letters, numbers and hyphens.');
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $entry->publishedAt);
        if ($date === false || $date->format('Y-m-d') !== $entry->publishedAt) {
            throw new InvalidArgumentException('Published date must use YYYY-MM-DD.');
        }

        if (($entry->cover === null) !== ($entry->coverAlt === null)) {
            throw new InvalidArgumentException('Cover image and alternative text must be provided together.');
        }

        if (($entry->sourceUrl === null) !== ($entry->sourceLabel === null)) {
            throw new InvalidArgumentException('Original source URL and label must be provided together.');
        }

        if ($entry->sourceUrl !== null && filter_var($entry->sourceUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Original source URL is invalid.');
        }

        if ($entry->editorUrl !== null && filter_var($entry->editorUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Editor URL is invalid.');
        }
    }

    private function required(string $value, string $name): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException($name . ' is required.');
        }
    }
}
