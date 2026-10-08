<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

use App\Listeners\Editorial\NewsMediaAudit;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (! is_file($autoload)) {
    fwrite(STDERR, "Composer autoload not found.\n");
    exit(2);
}

require $autoload;

$root = dirname(__DIR__);
$result = (new NewsMediaAudit())->audit($root);

fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");

if ($result['missing_sidecar'] !== []) {
    exit(1);
}
