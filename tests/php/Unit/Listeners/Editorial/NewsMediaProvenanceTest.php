<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\NewsEntry;
use App\Listeners\Editorial\NewsMediaFetcher;
use App\Listeners\Editorial\NewsMediaLocalizer;
use PHPUnit\Framework\TestCase;

final class NewsMediaProvenanceTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/ecidade-media-provenance-' . bin2hex(random_bytes(6));
    }

    protected function tearDown(): void
    {
        if (! is_dir($this->directory)) {
            return;
        }

        foreach (glob($this->directory . '/321/*') ?: [] as $path) {
            unlink($path);
        }
        if (is_dir($this->directory . '/321')) {
            rmdir($this->directory . '/321');
        }
        rmdir($this->directory);
    }

    public function testWritesReuseSidecarForLocalizedMedia(): void
    {
        $localizer = new NewsMediaLocalizer(
            $this->directory,
            '/assets/images/news',
            new ProvenanceMediaFetcher(),
        );

        $entry = new NewsEntry(
            source: 'github',
            externalId: 'github-issue-321',
            issueNumber: 321,
            title: 'Notícia',
            slug: 'noticia',
            description: 'Resumo',
            publishedAt: '2026-10-07',
            updatedAt: null,
            author: 'Comunidade e-Cidade',
            category: 'Notícias',
            body: 'Texto.',
            cover: 'https://github.com/user-attachments/assets/example',
            coverAlt: 'Descrição',
            editorUrl: 'https://github.com/e-cidade/site/issues/321',
            mediaCopyright: 'Prefeitura de Exemplo',
            mediaLicense: 'LicenseRef-eCidade-Editorial-Permission',
            mediaCredit: 'Foto: Prefeitura de Exemplo',
        );

        $localized = $localizer->localize($entry);

        self::assertSame('/assets/images/news/321/cover.png', $localized->cover);
        $sidecar = (string) file_get_contents($this->directory . '/321/cover.png.license');
        self::assertStringContainsString('SPDX-FileCopyrightText: Prefeitura de Exemplo', $sidecar);
        self::assertStringContainsString(
            'SPDX-License-' . 'Identifier: LicenseRef-eCidade-Editorial-Permission',
            $sidecar,
        );
    }
}

final class ProvenanceMediaFetcher implements NewsMediaFetcher
{
    public function fetch(string $url): string
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZbK0AAAAASUVORK5CYII=',
            true,
        );

        if (! is_string($png)) {
            throw new \RuntimeException('Unable to create PNG fixture.');
        }

        return $png;
    }
}
