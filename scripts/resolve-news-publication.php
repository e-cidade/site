<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

require __DIR__ . '/../vendor/autoload.php';

use App\Listeners\Editorial\NewsPublicationResolver;

$issueNumber = filter_var($argv[1] ?? null, FILTER_VALIDATE_INT);
if ($issueNumber === false) {
    fwrite(STDERR, "Valid issue number is required.\n");
    exit(1);
}

$config = require __DIR__ . '/../config.production.php';
$baseUrl = is_array($config) && is_string($config['baseUrl'] ?? null)
    ? $config['baseUrl']
    : '';

if ($baseUrl === '') {
    fwrite(STDERR, "Production base URL is not configured.\n");
    exit(1);
}

echo (new NewsPublicationResolver(
    __DIR__ . '/../source/_posts',
))->resolveUrl((int) $issueNumber, $baseUrl);
