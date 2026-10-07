<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

require __DIR__ . '/../vendor/autoload.php';

use App\Listeners\WordPress\WordPressClient;
use App\Listeners\WordPress\WordPressPostSynchronizer;

$sourceUrl = getenv('WORDPRESS_SOURCE_URL') ?: 'https://ecidade.softwarepublico.org';

$summary = (new WordPressPostSynchronizer(
    new WordPressClient($sourceUrl),
    __DIR__ . '/../source/_posts',
    __DIR__ . '/../source/assets/images/migrated',
))->synchronize();

printf(
    "WordPress sync: %d created, %d updated, %d skipped, %d media processed.\n",
    $summary['created'],
    $summary['updated'],
    $summary['skipped'],
    $summary['media'],
);
