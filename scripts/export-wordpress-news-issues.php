<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

require __DIR__ . '/../vendor/autoload.php';

use App\Listeners\WordPress\WordPressClient;
use App\Listeners\WordPress\WordPressIssueExporter;

$sourceUrl = getenv('WORDPRESS_SOURCE_URL') ?: 'https://ecidade.softwarepublico.org';

$records = (new WordPressIssueExporter(new WordPressClient($sourceUrl)))->export();

echo json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
echo PHP_EOL;
