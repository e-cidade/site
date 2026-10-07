<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\NewsIssueContract;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NewsIssueContractTest extends TestCase
{
    public function testParsesIssueFormBodyIntoStableEditorialContract(): void
    {
        $result = (new NewsIssueContract())->parse(
            123,
            'Prefeitura de Exemplo adota o e-Cidade',
            $this->fixture('valid-news-issue.md'),
        );

        self::assertSame(123, $result['issue']);
        self::assertSame('Prefeitura de Exemplo adota o e-Cidade', $result['title']);
        self::assertSame('Município adota o e-Cidade para modernizar sua gestão.', $result['summary']);
        self::assertSame('2026-10-07', $result['published_at']);
        self::assertSame(NewsIssueContract::DEFAULT_AUTHOR, $result['author']);
        self::assertStringContainsString('## Próximos passos', $result['content']);
        self::assertSame('Equipe municipal durante a capacitação.', $result['cover_alt']);
        self::assertSame('Prefeitura de Exemplo', $result['source_label']);
        self::assertSame('https://exemplo.gov.br/noticia', $result['source_url']);
    }

    public function testRejectsInvalidIssueFormData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Publication date must use YYYY-MM-DD.');

        (new NewsIssueContract())->parse(124, 'Notícia inválida', $this->fixture('invalid-news-issue.md'));
    }

    public function testRequiresAlternativeTextWhenCoverIsPresent(): void
    {
        $body = str_replace(
            "### Data da publicação\n\n07/10/2026",
            "### Data da publicação\n\n2026-10-07",
            $this->fixture('invalid-news-issue.md'),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cover image requires alternative text.');

        (new NewsIssueContract())->parse(125, 'Notícia sem texto alternativo', $body);
    }

    private function fixture(string $name): string
    {
        $content = file_get_contents(__DIR__ . '/../../../Fixtures/Editorial/' . $name);
        self::assertIsString($content);

        return $content;
    }
}
