<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

use App\Listeners\Editorial\EditorialLifecycle;
use App\Listeners\Editorial\EditorialPreviewEventHandler;
use App\Listeners\Editorial\GhEditorialGateway;

$root = require __DIR__ . '/bootstrap.php';
$repository = getenv('GITHUB_REPOSITORY') ?: '';
$eventPath = $argv[1] ?? getenv('GITHUB_EVENT_PATH') ?: '';

if ($repository === '' || $eventPath === '' || ! is_file($eventPath)) {
    fwrite(STDERR, "Repository and event payload are required.\n");
    exit(2);
}

$payload = json_decode((string) file_get_contents($eventPath), true, 512, JSON_THROW_ON_ERROR);
if (! is_array($payload)) {
    fwrite(STDERR, "GitHub event payload is invalid.\n");
    exit(2);
}

$gateway = new GhEditorialGateway($repository, $root);
(new EditorialPreviewEventHandler(
    $gateway,
    new EditorialLifecycle($gateway),
    getenv('EDITORIAL_PREVIEW_BASE_URL') ?: 'https://site-ecidade.librecode.coop/pr-preview',
))->handle($payload, getenv('EDITORIAL_PREVIEW_OUTCOME') ?: 'success');
