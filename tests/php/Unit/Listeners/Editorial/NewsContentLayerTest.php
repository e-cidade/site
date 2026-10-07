<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\GitHubIssueNewsSource;
use App\Listeners\Editorial\NewsEntry;
use App\Listeners\Editorial\NewsEntryValidator;
use App\Listeners\Editorial\NewsMarkdownWriter;
use App\Listeners\Editorial\WordPressNewsSource;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NewsContentLayerTest extends TestCase
{
    public function testNormalizesGitHubIssueAndPreservesExistingSlug(): void
    {
        $body = (string) file_get_contents(__DIR__ . '/../../../Fixtures/Editorial/valid-news-issue.md');
        $source = new GitHubIssueNewsSource();

        $first = $source->fromIssue(
            123,
            'Prefeitura de Exemplo adota o e-Cidade',
            $body,
            'https://github.com/e-cidade/site/issues/123',
        );

        $updated = $source->fromIssue(
            123,
            'Título editorial alterado depois da publicação',
            $body,
            'https://github.com/e-cidade/site/issues/123',
            $first->slug,
        );

        self::assertSame('github-issue-123', $first->externalId);
        self::assertSame(123, $first->issueNumber);
        self::assertSame('prefeitura-de-exemplo-adota-o-e-cidade', $first->slug);
        self::assertSame($first->slug, $updated->slug);
        self::assertSame('Comunidade e-Cidade', $first->author);
    }

    public function testNormalizesWordPressIntoSameCanonicalEntry(): void
    {
        /** @var array{id:int,slug:string,title:string,description:string,date:string,modified:string,author:string,category:string,content:string,source_url:string,featured_media_url:?string,featured_media_alt:string} $post */
        $post = require __DIR__ . '/../../../Fixtures/Editorial/normalized-wordpress-news.php';

        $entry = (new WordPressNewsSource())->fromNormalizedPost(
            $post,
            '/assets/images/migrated/nova-noticia-cover.jpg',
        );

        self::assertInstanceOf(NewsEntry::class, $entry);
        self::assertSame('wordpress', $entry->source);
        self::assertSame('wordpress-123', $entry->externalId);
        self::assertSame('nova-noticia', $entry->slug);
        self::assertSame('Site e-Cidade', $entry->sourceLabel);
    }

    public function testWriterIsDeterministicAndCompatibleWithJigsawPostFrontMatter(): void
    {
        $body = (string) file_get_contents(__DIR__ . '/../../../Fixtures/Editorial/valid-news-issue.md');
        $entry = (new GitHubIssueNewsSource())->fromIssue(
            123,
            'Prefeitura de Exemplo adota o e-Cidade',
            $body,
            'https://github.com/e-cidade/site/issues/123',
        );

        $writer = new NewsMarkdownWriter();
        $first = $writer->render($entry);
        $second = $writer->render($entry);

        self::assertSame($first, $second);
        self::assertStringContainsString('extends: _layouts.post', $first);
        self::assertStringContainsString('section: content', $first);
        self::assertStringContainsString('github_issue: 123', $first);
        self::assertStringContainsString('content_source: "github"', $first);
        self::assertStringContainsString('external_id: "github-issue-123"', $first);
        self::assertStringContainsString('## Próximos passos', $first);
    }

    public function testValidatorRejectsInvalidCanonicalEntry(): void
    {
        $entry = new NewsEntry(
            source: 'github',
            externalId: 'github-issue-1',
            issueNumber: 1,
            title: 'Notícia',
            slug: 'Slug inválido',
            description: 'Resumo',
            publishedAt: '2026-10-07',
            updatedAt: null,
            author: 'Comunidade e-Cidade',
            category: 'Notícias',
            body: 'Conteúdo',
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Slug must contain only lowercase letters, numbers and hyphens.');

        (new NewsEntryValidator())->validate($entry);
    }
}
