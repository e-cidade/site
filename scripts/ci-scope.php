<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

use App\Listeners\CI\PullRequestChangeClassifier;

require dirname(__DIR__) . '/listeners/CI/PullRequestChangeClassifier.php';

$base = $argv[1] ?? '';
$head = $argv[2] ?? '';

if ($base === '' || $head === '') {
    fwrite(STDERR, "Usage: php scripts/ci-scope.php <base-sha> <head-sha>\n");
    exit(2);
}

$command = [
    'git',
    'diff',
    '--name-only',
    '--diff-filter=ACDMRTUXB',
    $base . '...' . $head,
];

$pipes = [];
$process = proc_open(
    $command,
    [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes,
);

if (! is_resource($process)) {
    fwrite(STDERR, "Unable to start git diff.\n");
    exit(2);
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);

$exitCode = proc_close($process);
if ($exitCode !== 0) {
    fwrite(STDERR, is_string($stderr) ? $stderr : "git diff failed.\n");
    exit($exitCode);
}

$paths = array_values(array_filter(
    preg_split('/\R/', trim(is_string($stdout) ? $stdout : '')) ?: [],
    static fn (string $path): bool => $path !== '',
));

$editorialOnly = (new PullRequestChangeClassifier())->isEditorialOnly($paths);

fwrite(STDOUT, 'editorial_only=' . ($editorialOnly ? 'true' : 'false') . "\n");
fwrite(STDOUT, 'changed_count=' . count($paths) . "\n");
