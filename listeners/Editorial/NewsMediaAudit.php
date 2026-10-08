<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class NewsMediaAudit
{
    /**
     * @return array{
     *   media:list<array{
     *     path:string,
     *     public_path:string,
     *     category:string,
     *     referenced:bool,
     *     references:list<array{post:string,source_url:?string}>,
     *     has_sidecar:bool,
     *     status:string
     *   }>,
     *   orphaned:list<string>,
     *   missing_sidecar:list<string>
     * }
     */
    public function audit(string $root): array
    {
        $posts = $this->posts($root . '/source/_posts');
        $media = [];

        foreach ([
            'migrated' => $root . '/source/assets/images/migrated',
            'news' => $root . '/source/assets/images/news',
        ] as $category => $directory) {
            foreach ($this->mediaFiles($directory) as $path) {
                $relative = substr($path, strlen($root . '/source'));
                $publicPath = str_replace('\\', '/', $relative);
                $references = $this->references($publicPath, $posts);
                $referenced = $references !== [];
                $hasSidecar = is_file($path . '.license');

                $media[] = [
                    'path' => $path,
                    'public_path' => $publicPath,
                    'category' => $category,
                    'referenced' => $referenced,
                    'references' => $references,
                    'has_sidecar' => $hasSidecar,
                    'status' => $this->status($category, $referenced, $hasSidecar),
                ];
            }
        }

        return [
            'media' => $media,
            'orphaned' => array_values(array_map(
                static fn(array $item): string => $item['public_path'],
                array_filter($media, static fn(array $item): bool => ! $item['referenced']),
            )),
            'missing_sidecar' => array_values(array_map(
                static fn(array $item): string => $item['public_path'],
                array_filter(
                    $media,
                    static fn(array $item): bool => $item['category'] === 'news' && ! $item['has_sidecar'],
                ),
            )),
        ];
    }

    /** @return list<string> */
    private function mediaFiles(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            if (str_ends_with($path, '.license')) {
                continue;
            }

            $files[] = $path;
        }

        sort($files);

        return $files;
    }

    /**
     * @return list<array{path:string,content:string,source_url:?string}>
     */
    private function posts(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $posts = [];
        foreach (glob($directory . '/*.md') ?: [] as $path) {
            $content = file_get_contents($path);
            if (! is_string($content)) {
                continue;
            }

            $posts[] = [
                'path' => basename($path),
                'content' => $content,
                'source_url' => $this->frontMatterValue($content, 'source_url'),
            ];
        }

        return $posts;
    }

    /**
     * @param list<array{path:string,content:string,source_url:?string}> $posts
     * @return list<array{post:string,source_url:?string}>
     */
    private function references(string $publicPath, array $posts): array
    {
        $references = [];
        foreach ($posts as $post) {
            if (! str_contains($post['content'], $publicPath)) {
                continue;
            }

            $references[] = [
                'post' => $post['path'],
                'source_url' => $post['source_url'],
            ];
        }

        return $references;
    }

    private function frontMatterValue(string $content, string $key): ?string
    {
        if (preg_match(
            '/^' . preg_quote($key, '/') . ':\s*(.+)$/m',
            $content,
            $match,
        ) !== 1) {
            return null;
        }

        $value = trim($match[1], " \t\n\r\0\x0B\"'");

        return $value === '' ? null : $value;
    }

    private function status(string $category, bool $referenced, bool $hasSidecar): string
    {
        if (! $referenced) {
            return 'orphan-candidate';
        }

        if ($category === 'migrated') {
            return 'historical-pending';
        }

        if (! $hasSidecar) {
            return 'missing-reuse-sidecar';
        }

        return 'managed';
    }
}
