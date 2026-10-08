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
     *   media:list<array{path:string,public_path:string,category:string,referenced:bool,has_sidecar:bool}>,
     *   orphaned:list<string>,
     *   missing_sidecar:list<string>
     * }
     */
    public function audit(string $root): array
    {
        $posts = $this->postContents($root . '/source/_posts');
        $media = [];

        foreach ([
            'migrated' => $root . '/source/assets/images/migrated',
            'news' => $root . '/source/assets/images/news',
        ] as $category => $directory) {
            foreach ($this->mediaFiles($directory) as $path) {
                $relative = substr($path, strlen($root . '/source'));
                $publicPath = str_replace('\\', '/', $relative);
                $referenced = $this->isReferenced($publicPath, $posts);
                $hasSidecar = is_file($path . '.license');

                $media[] = [
                    'path' => $path,
                    'public_path' => $publicPath,
                    'category' => $category,
                    'referenced' => $referenced,
                    'has_sidecar' => $hasSidecar,
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

    /** @return list<string> */
    private function postContents(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $contents = [];
        foreach (glob($directory . '/*.md') ?: [] as $path) {
            $content = file_get_contents($path);
            if (is_string($content)) {
                $contents[] = $content;
            }
        }

        return $contents;
    }

    /** @param list<string> $posts */
    private function isReferenced(string $publicPath, array $posts): bool
    {
        foreach ($posts as $post) {
            if (str_contains($post, $publicPath)) {
                return true;
            }
        }

        return false;
    }
}
