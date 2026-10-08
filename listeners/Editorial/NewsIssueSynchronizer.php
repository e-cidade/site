<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class NewsIssueSynchronizer
{
    public function __construct(
        private readonly string $postsDirectory,
        private readonly GitHubIssueNewsSource $source = new GitHubIssueNewsSource(),
        private readonly NewsMarkdownWriter $writer = new NewsMarkdownWriter(),
        private readonly ?NewsMediaLocalizer $mediaLocalizer = null,
    ) {}

    /**
     * @return array{changed:bool,created:bool,path:string,slug:string}
     */
    public function synchronize(
        int $issueNumber,
        string $title,
        string $body,
        string $issueUrl,
        ?string $updatedAt = null,
        bool $publishedRevision = false,
    ): array {
        $this->ensureDirectory($this->postsDirectory);

        $existingPath = $this->findManagedPostPath($issueNumber);
        $existingSlug = $existingPath !== null
            ? pathinfo($existingPath, PATHINFO_FILENAME)
            : null;

        if ($publishedRevision && $existingPath === null) {
            throw new \InvalidArgumentException(
                'A notícia publicada não foi localizada. A revisão foi interrompida para preservar a URL e a data original.',
            );
        }

        $originalPublishedAt = $publishedRevision && $existingPath !== null
            ? $this->publishedDate($existingPath)
            : null;

        $entry = $this->source->fromIssue(
            $issueNumber,
            $title,
            $body,
            $issueUrl,
            $existingSlug,
            $publishedRevision && $existingPath !== null ? $updatedAt : null,
            $originalPublishedAt,
        );

        if ($this->mediaLocalizer !== null) {
            $entry = $this->mediaLocalizer->localize($entry);
        }

        $targetPath = $this->postsDirectory . '/' . $entry->slug . '.md';
        $rendered = $this->writer->render($entry);
        $existing = is_file($targetPath) ? (string) file_get_contents($targetPath) : null;

        if (
            $existing === $rendered
            || (
                $publishedRevision
                && $existing !== null
                && $this->withoutRevisionTimestamp($existing) === $this->withoutRevisionTimestamp($rendered)
            )
        ) {
            return [
                'changed' => false,
                'created' => false,
                'path' => $targetPath,
                'slug' => $entry->slug,
            ];
        }

        if (file_put_contents($targetPath, $rendered) === false) {
            throw new \RuntimeException('Unable to write synchronized news post to ' . $targetPath);
        }

        if ($existingPath !== null && $existingPath !== $targetPath && is_file($existingPath)) {
            unlink($existingPath);
        }

        return [
            'changed' => true,
            'created' => $existingPath === null,
            'path' => $targetPath,
            'slug' => $entry->slug,
        ];
    }

    private function withoutRevisionTimestamp(string $content): string
    {
        if (! str_starts_with($content, "---\\n")) {
            return $content;
        }

        $frontMatterEnd = strpos($content, "\\n---\\n", 4);
        if ($frontMatterEnd === false) {
            return $content;
        }

        $frontMatter = substr($content, 0, $frontMatterEnd + 1);
        $body = substr($content, $frontMatterEnd + 1);

        return (preg_replace('/^updated_at:[^\\r\\n]*\\r?\\n/m', '', $frontMatter) ?? $frontMatter) . $body;
    }

    private function publishedDate(string $path): string
    {
        $content = (string) file_get_contents($path);
        if (preg_match('/^date:\\s*"?(\\d{4}-\\d{2}-\\d{2})"?\\s*$/m', $content, $matches) !== 1) {
            throw new \InvalidArgumentException(
                'A data original da notícia publicada não foi encontrada. A revisão foi interrompida.',
            );
        }

        return $matches[1];
    }

    private function findManagedPostPath(int $issueNumber): ?string
    {
        foreach (glob($this->postsDirectory . '/*.md') ?: [] as $path) {
            $content = (string) file_get_contents($path);
            if (preg_match('/^github_issue:\s*' . preg_quote((string) $issueNumber, '/') . '\s*$/m', $content) === 1) {
                return $path;
            }
        }

        return null;
    }

    private function ensureDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Unable to create directory ' . $directory);
        }
    }
}
