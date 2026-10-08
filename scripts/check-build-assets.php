<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

use App\Listeners\Build\LocalAssetIntegrityChecker;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (! is_file($autoload)) {
    fwrite(STDERR, "Composer autoload not found.\n");
    exit(2);
}
require $autoload;

$buildDirectory = $argv[1] ?? '';
$expectedBaseUrl = $argv[2] ?? null;

if ($buildDirectory === '' || ! is_dir($buildDirectory)) {
    fwrite(STDERR, "Build directory not found: {$buildDirectory}\n");
    exit(2);
}

$errors = (new LocalAssetIntegrityChecker())->check($buildDirectory, $expectedBaseUrl);

if ($errors === []) {
    exit(0);
}

foreach ($errors as $error) {
    fwrite(
        STDERR,
        sprintf(
            "Broken local reference in %s: %s -> %s (%s)\n",
            $error['html'],
            $error['url'],
            $error['expected'],
            $error['reason'],
        ),
    );
}

exit(1);
