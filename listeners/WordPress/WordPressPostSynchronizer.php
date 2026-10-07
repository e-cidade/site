<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\WordPress;

final class WordPressPostSynchronizer
{
    public function __construct(
        private readonly WordPressClient $client,
        private readonly string $postsDirectory,
        private readonly string $mediaDirectory,
    ) {}

    /** @return array{created:int,updated:int,skipped:int,media:int} */
    public function synchronize(): array
    {
        $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'media' => 0];

        $this->ensureDirectory($this->postsDirectory);
        $this->ensureDirectory($this->mediaDirectory);

        foreach ($this->client->fetchPosts() as $post) {
            $normalized = $this->normalizePost($post);
            if ($normalized === null) {
                $summary['skipped']++;
                continue;
            }

            $targetPath = $this->postsDirectory . '/' . $normalized['slug'] . '.md';
            $managedPath = $this->findManagedPostPath($normalized['id']);
            $existingPath = $managedPath ?? (is_file($targetPath) ? $targetPath : null);
            $existing = $existingPath !== null ? (string) file_get_contents($existingPath) : null;

            if ($existing !== null && $managedPath === null) {
                $summary['skipped']++;
                continue;
            }

            [$content, $contentMediaCount] = $this->localizeContentImages(
                $normalized['content'],
                $normalized['slug'],
            );
            $normalized['content'] = $content;
            $summary['media'] += $contentMediaCount;

            [$coverImage, $coverChanged] = $this->synchronizeCoverImage($normalized);
            if ($coverChanged) {
                $summary['media']++;
            }

            $rendered = $this->renderPost($normalized, $coverImage);
            $samePath = $existingPath === null || $existingPath === $targetPath;
            if ($samePath && $existing === $rendered) {
                $summary['skipped']++;
                continue;
            }

            if (file_put_contents($targetPath, $rendered) === false) {
                throw new \RuntimeException('Unable to write synchronized post to ' . $targetPath);
            }

            if ($existingPath !== null && $existingPath !== $targetPath && is_file($existingPath)) {
                unlink($existingPath);
            }

            if ($existing === null) {
                $summary['created']++;
            } else {
                $summary['updated']++;
            }
        }

        return $summary;
    }

    /**
     * @param array<string, mixed> $post
     * @return array{id:int,slug:string,title:string,description:string,date:string,modified:string,author:string,category:string,content:string,source_url:string,featured_media_url:?string,featured_media_alt:string}|null
     */
    private function normalizePost(array $post): ?array
    {
        $id = filter_var($post['id'] ?? null, FILTER_VALIDATE_INT);
        $slug = $this->safeSlug((string) ($post['slug'] ?? ''));
        $title = $this->decodeRenderedText($post['title']['rendered'] ?? null);
        $content = isset($post['content']['rendered']) && is_string($post['content']['rendered'])
            ? trim($post['content']['rendered'])
            : '';
        $date = $this->normalizeDate($post['date'] ?? null);
        $modified = $this->normalizeDateTime($post['modified_gmt'] ?? $post['modified'] ?? null);
        $sourceUrl = isset($post['link']) && is_string($post['link']) ? $post['link'] : '';

        if ($id === false || $slug === '' || $title === '' || $content === '' || $date === null || $modified === null) {
            return null;
        }

        $description = $this->decodeRenderedText($post['excerpt']['rendered'] ?? null);
        $author = 'Comunidade e-Cidade';
        $category = 'Notícias';
        $featuredMediaUrl = null;
        $featuredMediaAlt = '';

        $embedded = is_array($post['_embedded'] ?? null) ? $post['_embedded'] : [];

        $authors = $embedded['author'] ?? [];
        if (is_array($authors) && isset($authors[0]['name']) && is_string($authors[0]['name'])) {
            $candidate = trim(html_entity_decode(strip_tags($authors[0]['name']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($candidate !== '' && mb_strtolower($candidate) !== 'administrador') {
                $author = $candidate;
            }
        }

        $termGroups = $embedded['wp:term'] ?? [];
        if (is_array($termGroups)) {
            foreach ($termGroups as $terms) {
                if (! is_array($terms)) {
                    continue;
                }
                foreach ($terms as $term) {
                    if (! is_array($term) || ($term['taxonomy'] ?? null) !== 'category') {
                        continue;
                    }
                    $candidate = $this->decodeRenderedText($term['name'] ?? null);
                    if ($candidate !== '' && mb_strtolower($candidate) !== 'sem categoria') {
                        $category = $candidate;
                        break 2;
                    }
                }
            }
        }

        $media = $embedded['wp:featuredmedia'] ?? [];
        if (is_array($media) && isset($media[0]) && is_array($media[0])) {
            if (isset($media[0]['source_url']) && is_string($media[0]['source_url'])) {
                $featuredMediaUrl = $media[0]['source_url'];
            }
            if (isset($media[0]['alt_text']) && is_string($media[0]['alt_text'])) {
                $featuredMediaAlt = trim($media[0]['alt_text']);
            }
        }

        return [
            'id' => $id,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'date' => $date,
            'modified' => $modified,
            'author' => $author,
            'category' => $category,
            'content' => $content,
            'source_url' => $sourceUrl,
            'featured_media_url' => $featuredMediaUrl,
            'featured_media_alt' => $featuredMediaAlt,
        ];
    }

    /**
     * @param array{slug:string,featured_media_url:?string} $post
     * @return array{0:?string,1:bool}
     */
    private function synchronizeCoverImage(array $post): array
    {
        $url = $post['featured_media_url'];
        if ($url === null || $url === '') {
            return [null, false];
        }

        [$localUrl, $changed] = $this->downloadImage($url, $post['slug'] . '-cover');

        return [$localUrl, $changed];
    }

    /** @return array{0:string,1:int} */
    private function localizeContentImages(string $content, string $slug): array
    {
        $mediaCount = 0;
        $urls = [];
        $counter = 0;

        $content = preg_replace_callback(
            '/\bsrc=("|\')(https?:\/\/[^"\']+)\1/i',
            function (array $matches) use ($slug, &$urls, &$counter, &$mediaCount): string {
                $counter++;
                [$localUrl, $changed] = $this->downloadImageOnce($matches[2], $slug . '-' . $counter, $urls);
                if ($changed) {
                    $mediaCount++;
                }

                return 'src=' . $matches[1] . $localUrl . $matches[1];
            },
            $content,
        ) ?? $content;

        $content = preg_replace_callback(
            '/\bsrcset=("|\')([^"\']+)\1/i',
            function (array $matches) use ($slug, &$urls, &$counter, &$mediaCount): string {
                $candidates = array_map('trim', explode(',', $matches[2]));
                foreach ($candidates as &$candidate) {
                    if (! preg_match('/^(https?:\/\/\S+)(\s+.+)?$/i', $candidate, $parts)) {
                        continue;
                    }
                    $counter++;
                    [$localUrl, $changed] = $this->downloadImageOnce($parts[1], $slug . '-' . $counter, $urls);
                    if ($changed) {
                        $mediaCount++;
                    }
                    $candidate = $localUrl . ($parts[2] ?? '');
                }
                unset($candidate);

                return 'srcset=' . $matches[1] . implode(', ', $candidates) . $matches[1];
            },
            $content,
        ) ?? $content;

        return [$content, $mediaCount];
    }

    /**
     * @param array<string, string> $cache
     * @return array{0:string,1:bool}
     */
    private function downloadImageOnce(string $url, string $basename, array &$cache): array
    {
        if (isset($cache[$url])) {
            return [$cache[$url], false];
        }

        [$localUrl, $changed] = $this->downloadImage($url, $basename);
        $cache[$url] = $localUrl;

        return [$localUrl, $changed];
    }

    /** @return array{0:string,1:bool} */
    private function downloadImage(string $url, string $basename): array
    {
        $pathPart = (string) parse_url($url, PHP_URL_PATH);
        $extension = strtolower((string) pathinfo($pathPart, PATHINFO_EXTENSION));
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            $extension = 'jpg';
        }

        $filename = $basename . '.' . $extension;
        $path = $this->mediaDirectory . '/' . $filename;
        $binary = $this->client->fetchBinary(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if (is_file($path) && hash('sha256', (string) file_get_contents($path)) === hash('sha256', $binary)) {
            return ['/assets/images/migrated/' . $filename, false];
        }

        if (file_put_contents($path, $binary) === false) {
            throw new \RuntimeException('Unable to write WordPress media to ' . $path);
        }

        return ['/assets/images/migrated/' . $filename, true];
    }

    /**
     * @param array{id:int,title:string,description:string,date:string,modified:string,author:string,category:string,content:string,source_url:string,featured_media_alt:string} $post
     */
    private function renderPost(array $post, ?string $coverImage): string
    {
        $frontMatter = [
            'extends: _layouts.post',
            'section: content',
            'title: ' . $this->yamlString($post['title']),
            'description: ' . $this->yamlString($post['description']),
            'date: ' . $post['date'],
            'author: ' . $this->yamlString($post['author']),
            'category: ' . $this->yamlString($post['category']),
        ];

        if ($coverImage !== null) {
            $frontMatter[] = 'cover_image: ' . $this->yamlString($coverImage);
            $frontMatter[] = 'cover_alt: ' . $this->yamlString($post['featured_media_alt']);
        }

        $frontMatter[] = 'source_url: ' . $this->yamlString($post['source_url']);
        $frontMatter[] = 'source_label: ' . $this->yamlString('Site e-Cidade');
        $frontMatter[] = 'wordpress_id: ' . $post['id'];
        $frontMatter[] = 'wordpress_modified: ' . $this->yamlString($post['modified']);

        return "---\n"
            . implode("\n", $frontMatter)
            . "\n---\n<!--\nSPDX-FileCopyrightText: 2026 e-Cidade community\nSPDX-License-Identifier: AGPL-3.0-or-later\n-->\n\n"
            . $post['content']
            . "\n";
    }

    private function findManagedPostPath(int $wordpressId): ?string
    {
        foreach (glob($this->postsDirectory . '/*.md') ?: [] as $path) {
            $content = (string) file_get_contents($path);
            if ($this->isManagedPost($content, $wordpressId)) {
                return $path;
            }
        }

        return null;
    }

    private function isManagedPost(string $content, int $wordpressId): bool
    {
        return preg_match('/^wordpress_id:\s*' . preg_quote((string) $wordpressId, '/') . '\s*$/m', $content) === 1;
    }

    private function decodeRenderedText(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->format(DATE_ATOM);
        } catch (\Exception) {
            return null;
        }
    }

    private function safeSlug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9-]+/', '-', $value) ?? '';

        return trim(preg_replace('/-+/', '-', $value) ?? '', '-');
    }

    private function yamlString(string $value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
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
