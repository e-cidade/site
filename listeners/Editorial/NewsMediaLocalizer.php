<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

use InvalidArgumentException;

final class NewsMediaLocalizer
{
    private const MAX_BYTES = 10_000_000;

    /** @var list<string> */
    private const ALLOWED_HOSTS = [
        'github.com',
        'user-images.githubusercontent.com',
        'private-user-images.githubusercontent.com',
        'ecidade.softwarepublico.org',
    ];

    /** @var array<string, string> */
    private array $cache = [];

    public function __construct(
        private readonly string $mediaDirectory,
        private readonly string $publicBasePath = '/assets/images/news',
        private readonly NewsMediaFetcher $fetcher = new NativeNewsMediaFetcher(),
    ) {}

    public function localize(NewsEntry $entry): NewsEntry
    {
        if ($entry->issueNumber === null) {
            return $entry;
        }

        $issueDirectory = $this->mediaDirectory . '/' . $entry->issueNumber;
        $this->ensureDirectory($issueDirectory);

        $cover = null;
        if ($entry->cover !== null) {
            $coverValue = trim($entry->cover);
            if (str_starts_with($coverValue, '/assets/images/')) {
                $cover = $coverValue;
            } else {
                $coverUrl = $this->extractUrl($coverValue);
                $cover = $this->localizeUrl($coverUrl, $issueDirectory, (string) $entry->issueNumber, 'cover');
            }
        }

        $body = preg_replace_callback(
            '/!\[([^\]]*)\]\((https:\/\/[^)\s]+)\)/i',
            function (array $matches) use ($issueDirectory, $entry): string {
                $url = $matches[2];
                $basename = 'image-' . substr(hash('sha256', $url), 0, 12);
                $local = $this->localizeUrl($url, $issueDirectory, (string) $entry->issueNumber, $basename);

                return '![' . $matches[1] . '](' . $local . ')';
            },
            $entry->body,
        ) ?? $entry->body;

        $body = preg_replace_callback(
            '/<img\b([^>]*?)\bsrc=(["\'])(https:\/\/[^"\']+)\2([^>]*)>/i',
            function (array $matches) use ($issueDirectory, $entry): string {
                $url = $matches[3];
                $basename = 'image-' . substr(hash('sha256', $url), 0, 12);
                $local = $this->localizeUrl($url, $issueDirectory, (string) $entry->issueNumber, $basename);

                return '<img' . $matches[1] . 'src=' . $matches[2] . $local . $matches[2] . $matches[4] . '>';
            },
            $body,
        ) ?? $body;

        return $entry->withLocalizedMedia($body, $cover);
    }

    private function extractUrl(string $value): string
    {
        $value = trim($value);

        if (preg_match("/<img\\b[^>]*?\\bsrc=([\"'])(https:\\/\\/[^\"']+)\\1/i", $value, $match) === 1) {
            return $match[2];
        }

        if (preg_match('/!\\[[^\\]]*\\]\\((https:\\/\\/[^)\\s]+)\\)/i', $value, $match) === 1) {
            return $match[1];
        }

        if (preg_match("/https:\\/\\/[^\\s\"'<>)]*/i", $value, $match) === 1) {
            return $match[0];
        }

        throw new InvalidArgumentException('A imagem de capa não contém uma URL HTTPS válida.');
    }

    private function localizeUrl(
        string $url,
        string $issueDirectory,
        string $issueNumber,
        string $basename,
    ): string {
        if (isset($this->cache[$url])) {
            return $this->cache[$url];
        }

        $this->assertAllowedUrl($url);
        $binary = $this->fetcher->fetch($url);

        if (strlen($binary) > self::MAX_BYTES) {
            throw new InvalidArgumentException('News media exceeds the 10 MB size limit.');
        }

        $extension = $this->extensionFor($binary);
        $filename = $basename . '.' . $extension;
        $path = $issueDirectory . '/' . $filename;

        if (! is_file($path) || hash_file('sha256', $path) !== hash('sha256', $binary)) {
            if (file_put_contents($path, $binary) === false) {
                throw new \RuntimeException('Unable to write localized news media to ' . $path);
            }
        }

        $publicPath = rtrim($this->publicBasePath, '/') . '/' . $issueNumber . '/' . $filename;
        $this->cache[$url] = $publicPath;

        return $publicPath;
    }

    private function assertAllowedUrl(string $url): void
    {
        if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new InvalidArgumentException('News media must use HTTPS.');
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (! in_array($host, self::ALLOWED_HOSTS, true)) {
            throw new InvalidArgumentException('News media host is not allowed: ' . $host);
        }
    }

    private function extensionFor(string $binary): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binary);

        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
            default => throw new InvalidArgumentException('Unsupported news media type: ' . (string) $mime),
        };
    }

    private function ensureDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Unable to create news media directory ' . $directory);
        }
    }
}
