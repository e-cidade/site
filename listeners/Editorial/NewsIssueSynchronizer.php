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

        $entry = $this->source->fromIssue(
            $issueNumber,
            $title,
            $body,
            $issueUrl,
            $existingSlug,
            $publishedRevision && $existingPath !== null ? $updatedAt : null,
        );

        if ($this->mediaLocalizer !== null) {
            $entry = $this->mediaLocalizer->localize($entry);
        }

        $targetPath = $this->postsDirectory . '/' . $entry->slug . '.md';
        $rendered = $this->writer->render($entry);
        $existing = is_file($targetPath) ? (string) file_get_contents($targetPath) : null;

        if ($existing === $rendered) {
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
