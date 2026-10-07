<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\WordPress;

class WordPressClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeout = 20,
    ) {}

    /** @return list<array<string, mixed>> */
    public function fetchPosts(): array
    {
        $posts = [];
        $page = 1;
        $totalPages = 1;

        do {
            $url = $this->buildUrl('/wp-json/wp/v2/posts?' . http_build_query([
                'per_page' => 100,
                'page' => $page,
                '_embed' => '1',
                'orderby' => 'date',
                'order' => 'asc',
                'status' => 'publish',
            ]));

            $response = $this->fetchJson($url);
            if ($response === null) {
                throw new \RuntimeException('Unable to fetch public WordPress posts from ' . $url);
            }

            foreach ($response as $post) {
                if (is_array($post)) {
                    $posts[] = $post;
                }
            }

            $headers = $this->fetchHeaders($url);
            $totalPages = $this->extractTotalPages($headers);
            $page++;
        } while ($page <= $totalPages);

        return $posts;
    }

    public function fetchBinary(string $url): string
    {
        $content = $this->fetchContent($url);
        if ($content === false) {
            throw new \RuntimeException('Unable to download WordPress media from ' . $url);
        }

        return $content;
    }

    protected function fetchContent(string $url): string|false
    {
        return @file_get_contents($url, false, $this->createStreamContext());
    }

    /** @return array<string, mixed>|null */
    protected function fetchJson(string $url): ?array
    {
        $content = $this->fetchContent($url);
        if ($content === false || $content === '') {
            return null;
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : null;
    }

    /** @return array<string, mixed> */
    protected function fetchHeaders(string $url): array
    {
        $headers = @get_headers($url, true, $this->createStreamContext());

        return is_array($headers) ? $headers : [];
    }

    protected function createStreamContext()
    {
        return stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\nUser-Agent: e-Cidade-site-wordpress-sync/1.0",
            ],
        ]);
    }

    /** @param array<string, mixed> $headers */
    private function extractTotalPages(array $headers): int
    {
        $value = $headers['X-WP-TotalPages'] ?? $headers['x-wp-totalpages'] ?? 1;
        if (is_array($value)) {
            $value = end($value);
        }

        $pages = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $pages === false ? 1 : $pages;
    }

    private function buildUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/') . $path;
    }
}
