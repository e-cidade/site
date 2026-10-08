<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\NewsIssueContract;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NewsMediaLicenseContractTest extends TestCase
{
    public function testAcceptsCcByWithSourceAndCredit(): void
    {
        $body = $this->body('CC-BY-4.0', 'https://example.org/photo', 'Foto: Example');

        $result = (new NewsIssueContract())->parse(321, 'Notícia', $body);

        self::assertSame('CC-BY-4.0', $result['media_license']);
        self::assertSame('https://example.org/photo', $result['media_source_url']);
        self::assertSame('Foto: Example', $result['media_credit']);
    }

    public function testRequiresSourceForPublicLicense(): void
    {
        $body = $this->body('CC0-1.0', null, null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Informe a URL de origem das imagens quando usar uma licença pública.');

        (new NewsIssueContract())->parse(321, 'Notícia', $body);
    }

    public function testRequiresCreditForAttributionLicense(): void
    {
        $body = $this->body('CC-BY-SA-4.0', 'https://example.org/photo', null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Informe o crédito das imagens para a licença selecionada.');

        (new NewsIssueContract())->parse(321, 'Notícia', $body);
    }

    public function testSpecificPermissionDoesNotRequireExternalMediaSource(): void
    {
        $body = $this->body('LicenseRef-eCidade-Editorial-Permission', null, null);

        $result = (new NewsIssueContract())->parse(321, 'Notícia', $body);

        self::assertNull($result['media_source_url']);
    }

    private function body(string $license, ?string $mediaSourceUrl, ?string $credit): string
    {
        $source = $mediaSourceUrl ?? '_No response_';
        $creditValue = $credit ?? '_No response_';

        return <<<MD
### Resumo

Resumo.

### Data da notícia

2026-10-07

### Autor

Comunidade e-Cidade

### Texto da notícia

Conteúdo.

### Imagem de capa

https://github.com/user-attachments/assets/example-image

### Texto alternativo da imagem

Descrição.

### Detentor dos direitos das imagens

Example

### Licença/permissão das imagens

{$license}

### Fonte das imagens

{$source}

### Crédito das imagens

{$creditValue}

### Declaração sobre as imagens

- [x] Confirmo que verifiquei a origem e que a licença/permissão informada permite ao projeto publicar estas imagens.

### Fonte original

_No response_

### URL da fonte

_No response_
MD;
    }
}
