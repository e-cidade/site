<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

require __DIR__ . '/../vendor/autoload.php';

use App\Listeners\Editorial\NewsIssueSynchronizer;

$eventPath = $argv[1] ?? getenv('GITHUB_EVENT_PATH') ?: '';
if ($eventPath === '' || ! is_file($eventPath)) {
    fwrite(STDERR, "GitHub event payload not found.\n");
    exit(1);
}

$event = json_decode((string) file_get_contents($eventPath), true, 512, JSON_THROW_ON_ERROR);
$issue = is_array($event['issue'] ?? null) ? $event['issue'] : null;

if ($issue === null) {
    fwrite(STDERR, "Issue payload not found.\n");
    exit(1);
}

$number = filter_var($issue['number'] ?? null, FILTER_VALIDATE_INT);
$title = is_string($issue['title'] ?? null) ? $issue['title'] : '';
$body = is_string($issue['body'] ?? null) ? $issue['body'] : '';
$url = is_string($issue['html_url'] ?? null) ? $issue['html_url'] : '';

$requiredSections = ['### Resumo', '### Data da publicação', '### Texto da notícia'];
$isNews = $number !== false
    && $title !== ''
    && $url !== ''
    && array_all($requiredSections, static fn (string $section): bool => str_contains($body, $section));

if (! $isNews) {
    echo "is_news=false\nchanged=false\n";
    exit(0);
}

$result = (new NewsIssueSynchronizer(
    __DIR__ . '/../source/_posts',
))->synchronize((int) $number, $title, $body, $url);

echo "is_news=true\n";
echo 'changed=' . ($result['changed'] ? 'true' : 'false') . "\n";
echo 'created=' . ($result['created'] ? 'true' : 'false') . "\n";
echo 'path=' . $result['path'] . "\n";
echo 'slug=' . $result['slug'] . "\n";
