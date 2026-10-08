<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\CI;

use App\Listeners\CI\PullRequestChangeClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PullRequestChangeClassifierTest extends TestCase
{
    #[DataProvider('scopes')]
    public function testClassifiesPullRequestScope(array $paths, bool $expected): void
    {
        self::assertSame(
            $expected,
            (new PullRequestChangeClassifier())->isEditorialOnly($paths),
        );
    }

    public static function scopes(): iterable
    {
        yield 'post only' => [
            ['source/_posts/example.md'],
            true,
        ];

        yield 'post and media' => [
            [
                'source/_posts/example.md',
                'source/assets/images/news/102/cover.png',
                'source/assets/images/news/102/cover.png.license',
            ],
            true,
        ];

        yield 'php code requires full ci' => [
            [
                'source/_posts/example.md',
                'listeners/Editorial/EditorialLifecycle.php',
            ],
            false,
        ];

        yield 'workflow requires full ci' => [
            ['.github/workflows/phpunit.yml'],
            false,
        ];

        yield 'dependency file requires full ci' => [
            ['composer.lock'],
            false,
        ];

        yield 'license policy change requires full ci' => [
            ['LICENSES/LicenseRef-eCidade-Editorial-Permission.txt'],
            false,
        ];

        yield 'empty diff is conservative' => [
            [],
            false,
        ];
    }
}
