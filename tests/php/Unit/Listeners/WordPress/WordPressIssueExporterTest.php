<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\WordPress;

use App\Listeners\WordPress\WordPressClient;
use App\Listeners\WordPress\WordPressIssueExporter;
use PHPUnit\Framework\TestCase;

final class WordPressIssueExporterTest extends TestCase
{
    public function testExportsWordPressPostAsDeterministicEditorialIssue(): void
    {
        $post = [
            'id' => 123,
            'slug' => 'noticia-legada',
            'date' => '2026-10-07T12:00:00',
            'link' => 'https://ecidade.softwarepublico.org/noticia-legada/',
            'title' => ['rendered' => 'Notícia &amp; legado'],
            'excerpt' => ['rendered' => '<p>Resumo da notícia.</p>'],
            'content' => ['rendered' => '<p>Conteúdo <strong>WordPress</strong>.</p>'],
            '_embedded' => [
                'author' => [['name' => 'administrador']],
                'wp:featuredmedia' => [[
                    'source_url' => 'https://ecidade.softwarepublico.org/wp-content/uploads/capa.jpg',
                    'alt_text' => 'Descrição da capa',
                ]],
            ],
        ];

        $exporter = new WordPressIssueExporter(new FakeIssueExporterWordPressClient([$post]));

        $first = $exporter->export();
        $second = $exporter->export();

        self::assertSame($first, $second);
        self::assertCount(1, $first);
        self::assertSame(123, $first[0]['wordpress_id']);
        self::assertSame('Notícia & legado', $first[0]['title']);
        self::assertStringContainsString('<!-- e-cidade-wordpress-id:123 -->', $first[0]['body']);
        self::assertStringContainsString('<!-- e-cidade-slug:noticia-legada -->', $first[0]['body']);
        self::assertStringContainsString('### Texto da notícia', $first[0]['body']);
        self::assertStringContainsString('https://ecidade.softwarepublico.org/wp-content/uploads/capa.jpg', $first[0]['body']);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first[0]['hash']);
    }

    public function testOmitsFeaturedImageWithoutAlternativeText(): void
    {
        $post = [
            'id' => 124,
            'slug' => 'sem-alt',
            'date' => '2026-10-07',
            'link' => 'https://ecidade.softwarepublico.org/sem-alt/',
            'title' => ['rendered' => 'Sem alt'],
            'excerpt' => ['rendered' => 'Resumo'],
            'content' => ['rendered' => '<p>Conteúdo.</p>'],
            '_embedded' => [
                'wp:featuredmedia' => [[
                    'source_url' => 'https://ecidade.softwarepublico.org/wp-content/uploads/capa.jpg',
                    'alt_text' => '',
                ]],
            ],
        ];

        $record = (new WordPressIssueExporter(new FakeIssueExporterWordPressClient([$post])))->export()[0];

        self::assertStringContainsString("### Imagem de capa\n\n_No response_", $record['body']);
    }
}

final class FakeIssueExporterWordPressClient extends WordPressClient
{
    /** @param list<array<string, mixed>> $posts */
    public function __construct(private readonly array $posts)
    {
        parent::__construct('https://example.test');
    }

    public function fetchPosts(): array
    {
        return $this->posts;
    }
}
