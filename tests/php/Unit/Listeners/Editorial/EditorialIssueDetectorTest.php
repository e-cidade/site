<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\EditorialIssueDetector;
use App\Listeners\Editorial\EditorialLabels;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EditorialIssueDetectorTest extends TestCase
{
    #[DataProvider('cases')]
    public function testDetectsOnlyEditorialIssues(
        string $body,
        array $labels,
        bool $branchExists,
        bool $pullRequestExists,
        bool $forced,
        bool $expected,
    ): void {
        self::assertSame(
            $expected,
            (new EditorialIssueDetector())->isNews(
                $body,
                $labels,
                $branchExists,
                $pullRequestExists,
                $forced,
            ),
        );
    }

    public static function cases(): iterable
    {
        yield 'stable label survives destroyed body' => [
            'conteúdo sem formulário',
            [EditorialLabels::NEWS],
            false,
            false,
            false,
            true,
        ];
        yield 'ordinary issue is ignored' => ['bug comum', [], false, false, false, false];
        yield 'legacy form remains compatible' => [
            "### Resumo\nX\n### Data da publicação\n2026-10-07\n### Texto da notícia\nY",
            [],
            false,
            false,
            false,
            true,
        ];
        yield 'existing editorial branch preserves identity' => ['corrompida', [], true, false, false, true];
        yield 'manual dispatch is explicit' => ['qualquer conteúdo', [], false, false, true, true];
    }
}
