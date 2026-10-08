<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners;

use App\Listeners\RewriteLocalImageUrls;
use PHPUnit\Framework\TestCase;

final class RewriteLocalImageUrlsTest extends TestCase
{
    public function testPrefixesRootAbsoluteContentImagesWithPreviewBaseUrl(): void
    {
        $html = '<p><img src="/assets/images/migrated/example.png" alt="Exemplo"></p>';

        self::assertSame(
            '<p><img src="https://example.com/pr-preview/pr-104/assets/images/migrated/example.png" alt="Exemplo"></p>',
            (new RewriteLocalImageUrls())->rewrite($html, 'https://example.com/pr-preview/pr-104'),
        );
    }

    public function testPreservesAlreadyAbsoluteImageUrls(): void
    {
        $html = '<img src="https://cdn.example.com/example.png" alt="Exemplo">';

        self::assertSame(
            $html,
            (new RewriteLocalImageUrls())->rewrite($html, 'https://example.com/pr-preview/pr-104'),
        );
    }
}
