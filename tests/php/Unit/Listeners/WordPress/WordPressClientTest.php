<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\WordPress;

use App\Listeners\WordPress\WordPressClient;
use PHPUnit\Framework\TestCase;

final class WordPressClientTest extends TestCase
{
    public function testFetchPostsReadsEveryPublicApiPage(): void
    {
        $client = new FakeWordPressClient([
            1 => [['id' => 10]],
            2 => [['id' => 11]],
        ], 2);

        self::assertSame([['id' => 10], ['id' => 11]], $client->fetchPosts());
        self::assertCount(2, $client->requestedUrls);
        self::assertStringContainsString('per_page=100', $client->requestedUrls[0]);
        self::assertStringContainsString('_embed=1', $client->requestedUrls[0]);
        self::assertStringContainsString('page=2', $client->requestedUrls[1]);
    }

    public function testFetchPostsFailsWhenPublicApiCannotBeRead(): void
    {
        $client = new FakeWordPressClient([], 1, true);

        $this->expectException(\RuntimeException::class);
        $client->fetchPosts();
    }
}

final class FakeWordPressClient extends WordPressClient
{
    /** @var list<string> */
    public array $requestedUrls = [];

    /** @param array<int, list<array<string, mixed>>> $pages */
    public function __construct(
        private readonly array $pages,
        private readonly int $totalPages,
        private readonly bool $fail = false,
    ) {
        parent::__construct('https://example.test');
    }

    protected function fetchJson(string $url): ?array
    {
        $this->requestedUrls[] = $url;
        if ($this->fail) {
            return null;
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return $this->pages[(int) ($query['page'] ?? 1)] ?? [];
    }

    protected function fetchHeaders(string $url): array
    {
        return ['X-WP-TotalPages' => (string) $this->totalPages];
    }
}
