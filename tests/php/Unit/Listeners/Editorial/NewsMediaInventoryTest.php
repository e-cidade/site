<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\NewsMediaAudit;
use PHPUnit\Framework\TestCase;

final class NewsMediaInventoryTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/ecidade-media-inventory-' . bin2hex(random_bytes(5));
        mkdir($this->root . '/source/_posts', 0775, true);
        mkdir($this->root . '/source/assets/images/migrated', 0775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    public function testReportsWhichPostAndSourceUrlReferenceHistoricalMedia(): void
    {
        file_put_contents($this->root . '/source/assets/images/migrated/photo.png', 'image');
        file_put_contents(
            $this->root . '/source/_posts/example.md',
            "---\nsource_url: https://example.org/original\n---\n"
            . "![](/assets/images/migrated/photo.png)\n",
        );

        $result = (new NewsMediaAudit())->audit($this->root);

        self::assertSame('historical-pending', $result['media'][0]['status']);
        self::assertSame(
            [[
                'post' => 'example.md',
                'source_url' => 'https://example.org/original',
            ]],
            $result['media'][0]['references'],
        );
    }

    public function testMarksUnreferencedHistoricalFileAsOrphanCandidate(): void
    {
        file_put_contents($this->root . '/source/assets/images/migrated/orphan.png', 'image');

        $result = (new NewsMediaAudit())->audit($this->root);

        self::assertSame('orphan-candidate', $result['media'][0]['status']);
        self::assertSame(['/assets/images/migrated/orphan.png'], $result['orphaned']);
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
