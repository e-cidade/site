<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

use App\Listeners\Editorial\EditorialLifecycle;
use App\Listeners\Editorial\EditorialPublicationService;
use App\Listeners\Editorial\GhEditorialGateway;
use App\Listeners\Editorial\NewsPublicationResolver;

$root = require __DIR__ . '/bootstrap.php';
$repository = getenv('GITHUB_REPOSITORY') ?: '';
$commitSha = $argv[1] ?? getenv('GITHUB_SHA') ?: '';
$baseUrl = getenv('EDITORIAL_BASE_URL') ?: 'https://site-ecidade.librecode.coop';

if ($repository === '' || $commitSha === '') {
    fwrite(STDERR, "Repository and commit SHA are required.\n");
    exit(2);
}

$gateway = new GhEditorialGateway($repository, $root);
$service = new EditorialPublicationService(
    $gateway,
    new EditorialLifecycle($gateway),
    new NewsPublicationResolver($root . '/source/_posts'),
    $baseUrl,
);

$service->publishForCommit($commitSha);
