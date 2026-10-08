<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class NewsMarkdownWriter
{
    public function __construct(
        private readonly NewsEntryValidator $validator = new NewsEntryValidator(),
    ) {}

    public function render(NewsEntry $entry): string
    {
        $this->validator->validate($entry);

        $frontMatter = [
            'extends: _layouts.post',
            'section: content',
            'title: ' . $this->yamlString($entry->title),
            'description: ' . $this->yamlString($entry->description),
            'date: ' . $entry->publishedAt,
            'author: ' . $this->yamlString($entry->author),
            'category: ' . $this->yamlString($entry->category),
        ];

        if ($entry->cover !== null) {
            $frontMatter[] = 'cover_image: ' . $this->yamlString($entry->cover);
            $frontMatter[] = 'cover_alt: ' . $this->yamlString((string) $entry->coverAlt);
        }

        if ($entry->coverCaption !== null) {
            $frontMatter[] = 'cover_caption: ' . $this->yamlString($entry->coverCaption);
        }

        if ($entry->sourceUrl !== null) {
            $frontMatter[] = 'source_url: ' . $this->yamlString($entry->sourceUrl);
            $frontMatter[] = 'source_label: ' . $this->yamlString((string) $entry->sourceLabel);
        }

        if ($entry->mediaCopyright !== null) {
            $frontMatter[] = 'media_copyright: ' . $this->yamlString($entry->mediaCopyright);
        }

        if ($entry->mediaLicense !== null) {
            $frontMatter[] = 'media_license: ' . $this->yamlString($entry->mediaLicense);
        }

        if ($entry->mediaSourceUrl !== null) {
            $frontMatter[] = 'media_source_url: ' . $this->yamlString($entry->mediaSourceUrl);
        }

        if ($entry->mediaCredit !== null) {
            $frontMatter[] = 'media_credit: ' . $this->yamlString($entry->mediaCredit);
        }

        $frontMatter[] = 'content_source: ' . $this->yamlString($entry->source);
        $frontMatter[] = 'external_id: ' . $this->yamlString($entry->externalId);

        if ($entry->issueNumber !== null) {
            $frontMatter[] = 'github_issue: ' . $entry->issueNumber;
        }

        if ($entry->updatedAt !== null) {
            $frontMatter[] = ($entry->source === 'github' ? 'updated_at: ' : 'source_updated_at: ')
                . $this->yamlString($entry->updatedAt);
        }

        if ($entry->editorUrl !== null) {
            $frontMatter[] = 'editor_url: ' . $this->yamlString($entry->editorUrl);
        }

        if ($entry->tags !== []) {
            $frontMatter[] = 'tags: ' . $this->yamlStringArray($entry->tags);
        }

        return "---\n"
            . implode("\n", $frontMatter)
            . "\n---\n<!--\nSPDX-FileCopyrightText: 2026 e-Cidade community\nSPDX-License-Identifier: AGPL-3.0-or-later\n-->\n\n"
            . rtrim($entry->body)
            . "\n";
    }

    private function yamlString(string $value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /** @param list<string> $values */
    private function yamlStringArray(array $values): string
    {
        return (string) json_encode(array_values($values), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
