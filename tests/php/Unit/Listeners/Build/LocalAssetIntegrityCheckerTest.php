<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Build;

use App\Listeners\Build\LocalAssetIntegrityChecker;
use PHPUnit\Framework\TestCase;

final class LocalAssetIntegrityCheckerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/ecidade-build-integrity-' . bin2hex(random_bytes(5));
        mkdir($this->directory, 0775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->directory);
    }

    public function testPassesWhenLocalAssetsExist(): void
    {
        $this->write('assets/images/news/1/cover.png', 'png');
        $this->write('assets/app.js', 'js');
        $this->write('noticia/index.html', '<img src="/assets/images/news/1/cover.png"><script src="/assets/app.js"></script>');

        self::assertSame(
            [],
            (new LocalAssetIntegrityChecker())->check($this->directory),
        );
    }

    public function testReportsMissingLocalAssetWithSourcePage(): void
    {
        $this->write('noticia/index.html', '<img src="/assets/images/news/1/missing.png">');

        $errors = (new LocalAssetIntegrityChecker())->check($this->directory);

        self::assertCount(1, $errors);
        self::assertStringEndsWith('/noticia/index.html', $errors[0]['html']);
        self::assertSame('/assets/images/news/1/missing.png', $errors[0]['url']);
        self::assertSame('local target does not exist', $errors[0]['reason']);
    }

    public function testResolvesPreviewBasePath(): void
    {
        $this->write('assets/images/news/1/cover.png', 'png');
        $this->write(
            'noticia/index.html',
            '<img src="/pr-preview/pr-104/assets/images/news/1/cover.png">',
        );

        self::assertSame(
            [],
            (new LocalAssetIntegrityChecker())->check(
                $this->directory,
                'https://site.example/pr-preview/pr-104',
            ),
        );
    }

    public function testTreatsAbsolutePreviewUrlAsLocalAndReportsMissingAsset(): void
    {
        $this->write(
            'noticia/index.html',
            '<img src="https://site.example/pr-preview/pr-104/assets/images/news/1/missing.png">',
        );

        $errors = (new LocalAssetIntegrityChecker())->check(
            $this->directory,
            'https://site.example/pr-preview/pr-104',
        );

        self::assertCount(1, $errors);
        self::assertSame(
            'https://site.example/pr-preview/pr-104/assets/images/news/1/missing.png',
            $errors[0]['url'],
        );
        self::assertSame('local target does not exist', $errors[0]['reason']);
    }

    public function testAcceptsAbsolutePreviewUrlWhenLocalAssetExists(): void
    {
        $this->write('assets/images/news/1/cover.png', 'png');
        $this->write(
            'noticia/index.html',
            '<img src="https://site.example/pr-preview/pr-104/assets/images/news/1/cover.png">',
        );

        self::assertSame(
            [],
            (new LocalAssetIntegrityChecker())->check(
                $this->directory,
                'https://site.example/pr-preview/pr-104',
            ),
        );
    }

    public function testRejectsRootAbsoluteAssetInsidePreview(): void
    {
        $this->write('assets/images/news/1/cover.png', 'png');
        $this->write('noticia/index.html', '<img src="/assets/images/news/1/cover.png">');

        $errors = (new LocalAssetIntegrityChecker())->check(
            $this->directory,
            'https://site.example/pr-preview/pr-104',
        );

        self::assertCount(1, $errors);
        self::assertSame(
            'preview-local asset does not include the preview base path',
            $errors[0]['reason'],
        );
    }

    public function testIgnoresExternalAndNonFileReferences(): void
    {
        $this->write(
            'index.html',
            '<a href="https://example.org/page">External</a><a href="#section">Anchor</a><img src="data:image/png;base64,AA==">',
        );

        self::assertSame(
            [],
            (new LocalAssetIntegrityChecker())->check($this->directory),
        );
    }

    private function write(string $path, string $content): void
    {
        $fullPath = $this->directory . '/' . $path;
        $directory = dirname($fullPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        file_put_contents($fullPath, $content);
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = $directory . '/' . $entry;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
