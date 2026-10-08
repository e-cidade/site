<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\NewsMediaAudit;
use PHPUnit\Framework\TestCase;

final class NewsMediaAuditTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/ecidade-media-audit-' . bin2hex(random_bytes(5));
        mkdir($this->root . '/source/_posts', 0775, true);
        mkdir($this->root . '/source/assets/images/migrated', 0775, true);
        mkdir($this->root . '/source/assets/images/news/10', 0775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    public function testFindsReferencedAndOrphanedMediaWithoutDeletingAnything(): void
    {
        file_put_contents($this->root . '/source/assets/images/migrated/used.png', 'used');
        file_put_contents($this->root . '/source/assets/images/migrated/orphan.png', 'orphan');
        file_put_contents(
            $this->root . '/source/_posts/example.md',
            "cover_image: "/assets/images/migrated/used.png"\n",
        );

        $result = (new NewsMediaAudit())->audit($this->root);

        self::assertContains('/assets/images/migrated/orphan.png', $result['orphaned']);
        self::assertNotContains('/assets/images/migrated/used.png', $result['orphaned']);
        self::assertFileExists($this->root . '/source/assets/images/migrated/orphan.png');
    }

    public function testNewEditorialMediaRequiresPerFileReuseSidecar(): void
    {
        file_put_contents($this->root . '/source/assets/images/news/10/cover.png', 'cover');
        file_put_contents(
            $this->root . '/source/_posts/example.md',
            "cover_image: "/assets/images/news/10/cover.png"\n",
        );

        $result = (new NewsMediaAudit())->audit($this->root);

        self::assertSame(
            ['/assets/images/news/10/cover.png'],
            $result['missing_sidecar'],
        );

        file_put_contents(
            $this->root . '/source/assets/images/news/10/cover.png.license',
            "SPDX-License-Identifier: CC0-1.0\n",
        );

        $result = (new NewsMediaAudit())->audit($this->root);
        self::assertSame([], $result['missing_sidecar']);
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
