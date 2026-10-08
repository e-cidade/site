<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

use App\Listeners\Editorial\EditorialLabels;
use App\Listeners\Editorial\GhEditorialGateway;

$root = require __DIR__ . '/bootstrap.php';
$repository = getenv('GITHUB_REPOSITORY') ?: '';

if ($repository === '') {
    fwrite(STDERR, "GITHUB_REPOSITORY is required.\n");
    exit(2);
}

(new GhEditorialGateway($repository, $root))->ensureLabels(EditorialLabels::definitions());
