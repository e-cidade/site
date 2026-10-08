<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

use App\Listeners\Editorial\EditorialIssueEventHandler;
use App\Listeners\Editorial\EditorialLifecycle;
use App\Listeners\Editorial\EditorialSyncService;
use App\Listeners\Editorial\GhEditorialGateway;
use App\Listeners\Editorial\NewsIssueSynchronizer;
use App\Listeners\Editorial\NewsMediaLocalizer;
use App\Listeners\Editorial\PublishedNewsLocator;

$root = require __DIR__ . '/bootstrap.php';
$repository = getenv('GITHUB_REPOSITORY') ?: '';
if ($repository === '') {
    fwrite(STDERR, "GITHUB_REPOSITORY is required.\n");
    exit(2);
}

$gateway = new GhEditorialGateway($repository, $root);
$lifecycle = new EditorialLifecycle($gateway);
$synchronizer = new NewsIssueSynchronizer(
    postsDirectory: $root . '/source/_posts',
    mediaLocalizer: new NewsMediaLocalizer(
        $root . '/source/assets/images/news',
    ),
);
$service = new EditorialSyncService($gateway, $synchronizer, $lifecycle);
$handler = new EditorialIssueEventHandler(
    $gateway,
    $service,
    $lifecycle,
    new PublishedNewsLocator($root . '/source/_posts'),
);

$dispatchIssue = filter_var(getenv('DISPATCH_ISSUE_NUMBER') ?: null, FILTER_VALIDATE_INT);
if ($dispatchIssue !== false && $dispatchIssue !== null) {
    $payload = $gateway->issuePayload((int) $dispatchIssue);
    exit($handler->handle($payload, true));
}

$eventPath = $argv[1] ?? getenv('GITHUB_EVENT_PATH') ?: '';
if ($eventPath === '' || ! is_file($eventPath)) {
    fwrite(STDERR, "GitHub event payload not found.\n");
    exit(2);
}

$payload = json_decode((string) file_get_contents($eventPath), true, 512, JSON_THROW_ON_ERROR);
if (! is_array($payload)) {
    fwrite(STDERR, "GitHub event payload is invalid.\n");
    exit(2);
}

exit($handler->handle($payload));
