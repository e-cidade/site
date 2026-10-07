<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class NativeNewsMediaFetcher implements NewsMediaFetcher
{
    public function fetch(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 20,
                'ignore_errors' => false,
                'header' => "User-Agent: e-Cidade-news-media-sync/1.0\r\n",
            ],
        ]);

        $content = @file_get_contents($url, false, $context);
        if ($content === false) {
            throw new \RuntimeException('Unable to download news media from ' . $url);
        }

        return $content;
    }
}
