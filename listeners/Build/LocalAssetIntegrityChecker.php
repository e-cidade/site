<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Build;

use DOMDocument;
use DOMElement;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class LocalAssetIntegrityChecker
{
    /**
     * @return list<array{html:string,url:string,expected:string,reason:string}>
     */
    public function check(string $buildDirectory, ?string $expectedBaseUrl = null): array
    {
        $errors = [];
        $basePath = $this->basePath($expectedBaseUrl);
        $baseOrigin = $this->baseOrigin($expectedBaseUrl);

        foreach ($this->htmlFiles($buildDirectory) as $htmlPath) {
            $html = file_get_contents($htmlPath);
            if (! is_string($html)) {
                continue;
            }

            foreach ($this->references($html) as $url) {
                $resolved = $this->resolve(
                    $url,
                    $htmlPath,
                    $buildDirectory,
                    $basePath,
                    $baseOrigin,
                );
                if ($resolved === null) {
                    continue;
                }

                if ($resolved['reason'] !== '') {
                    $errors[] = [
                        'html' => $htmlPath,
                        'url' => $url,
                        'expected' => $resolved['path'],
                        'reason' => $resolved['reason'],
                    ];
                    continue;
                }

                if (! $this->targetExists($resolved['path'])) {
                    $errors[] = [
                        'html' => $htmlPath,
                        'url' => $url,
                        'expected' => $resolved['path'],
                        'reason' => 'local target does not exist',
                    ];
                }
            }
        }

        return $errors;
    }

    /** @return list<string> */
    private function htmlFiles(string $buildDirectory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $buildDirectory,
                RecursiveDirectoryIterator::SKIP_DOTS,
            ),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'html') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /** @return list<string> */
    private function references(string $html): array
    {
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $references = [];

        foreach ([
            ['img', 'src'],
            ['script', 'src'],
            ['source', 'src'],
            ['link', 'href'],
            ['a', 'href'],
        ] as [$tag, $attribute]) {
            foreach ($dom->getElementsByTagName($tag) as $node) {
                if (! $node instanceof DOMElement || ! $node->hasAttribute($attribute)) {
                    continue;
                }

                $value = trim($node->getAttribute($attribute));
                if ($value !== '') {
                    $references[] = $value;
                }
            }
        }

        return array_values(array_unique($references));
    }

    /**
     * @return array{path:string,reason:string}|null
     */
    private function resolve(
        string $url,
        string $htmlPath,
        string $buildDirectory,
        string $basePath,
        string $baseOrigin,
    ): ?array {
        if (
            str_starts_with($url, '#')
            || str_starts_with($url, 'data:')
            || str_starts_with($url, 'mailto:')
            || str_starts_with($url, 'tel:')
            || str_starts_with($url, 'javascript:')
            || str_starts_with($url, '//')
        ) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (is_string($scheme) && $scheme !== '') {
            if (! in_array(strtolower($scheme), ['http', 'https'], true)) {
                return null;
            }

            if ($baseOrigin === '' || ! str_starts_with($url, $baseOrigin . '/')) {
                return null;
            }
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return null;
        }

        if ($basePath !== '' && str_starts_with($path, '/')) {
            if ($path === $basePath || str_starts_with($path, $basePath . '/')) {
                $path = substr($path, strlen($basePath));
                if ($path === '') {
                    $path = '/';
                }
            } elseif (str_starts_with($path, '/assets/')) {
                return [
                    'path' => rtrim($buildDirectory, '/') . $path,
                    'reason' => 'preview-local asset does not include the preview base path',
                ];
            }
        }

        if (str_starts_with($path, '/')) {
            $target = rtrim($buildDirectory, '/') . $path;
        } else {
            $target = dirname($htmlPath) . '/' . $path;
        }

        $target = $this->normalizePath($target);

        return ['path' => $target, 'reason' => ''];
    }

    private function targetExists(string $target): bool
    {
        if (is_file($target)) {
            return true;
        }

        if (is_dir($target) && is_file(rtrim($target, '/') . '/index.html')) {
            return true;
        }

        if (pathinfo($target, PATHINFO_EXTENSION) === '') {
            if (is_file($target . '.html')) {
                return true;
            }

            if (is_file(rtrim($target, '/') . '/index.html')) {
                return true;
            }
        }

        return false;
    }

    private function baseOrigin(?string $expectedBaseUrl): string
    {
        if ($expectedBaseUrl === null || trim($expectedBaseUrl) === '') {
            return '';
        }

        $scheme = parse_url($expectedBaseUrl, PHP_URL_SCHEME);
        $host = parse_url($expectedBaseUrl, PHP_URL_HOST);
        $port = parse_url($expectedBaseUrl, PHP_URL_PORT);

        if (! is_string($scheme) || ! is_string($host) || $scheme === '' || $host === '') {
            return '';
        }

        return strtolower($scheme) . '://' . strtolower($host)
            . (is_int($port) ? ':' . $port : '');
    }

    private function basePath(?string $expectedBaseUrl): string
    {
        if ($expectedBaseUrl === null || trim($expectedBaseUrl) === '') {
            return '';
        }

        $path = parse_url($expectedBaseUrl, PHP_URL_PATH);
        if (! is_string($path) || $path === '/' || $path === '') {
            return '';
        }

        return '/' . trim($path, '/');
    }

    private function normalizePath(string $path): string
    {
        $prefix = str_starts_with($path, '/') ? '/' : '';
        $parts = [];

        foreach (explode('/', str_replace('\\', '/', $path)) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($parts);
                continue;
            }

            $parts[] = $part;
        }

        return $prefix . implode('/', $parts);
    }
}
