<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\NewsIssueContract;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NewsEditorialContractRegressionTest extends TestCase
{
    public function testAcceptsLegacyPublicationDateHeading(): void
    {
        $body = str_replace(
            '### Data da notícia',
            '### Data da publicação',
            $this->fixture(),
        );

        $result = (new NewsIssueContract())->parse(321, 'Notícia', $body);

        self::assertSame('2026-10-07', $result['published_at']);
    }

    public function testRejectsMediaWithoutRightsHolder(): void
    {
        $body = preg_replace(
            '/### Detentor dos direitos das imagens\R.*?(?=### Licença\/permissão das imagens)/s',
            '',
            $this->fixture(),
        );
        self::assertIsString($body);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Informe o detentor dos direitos das imagens.');

        (new NewsIssueContract())->parse(321, 'Notícia', $body);
    }

    public function testRejectsMediaWithoutAuthorizationConfirmation(): void
    {
        $body = str_replace(
            '- [x] Confirmo que tenho autorização para enviar e permitir a publicação das imagens informadas nesta notícia.',
            '- [ ] Confirmo que tenho autorização para enviar e permitir a publicação das imagens informadas nesta notícia.',
            $this->fixture(),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Confirme que possui autorização para permitir a publicação das imagens.');

        (new NewsIssueContract())->parse(321, 'Notícia', $body);
    }

    private function fixture(): string
    {
        $content = file_get_contents(__DIR__ . '/../../../Fixtures/Editorial/valid-news-issue.md');
        self::assertIsString($content);

        return $content;
    }
}
