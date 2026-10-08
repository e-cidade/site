<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\Listeners\\';
        if (! str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $path = __DIR__ . '/../listeners/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

use App\Listeners\Editorial\NewsIssueSynchronizer;
use App\Listeners\Editorial\NewsMediaLocalizer;
use InvalidArgumentException;

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
$updatedAt = is_string($issue['updated_at'] ?? null) ? $issue['updated_at'] : null;

$isNews = getenv('NEWS_ISSUE') === 'true' || ($number !== false && $title !== '' && $url !== '');
if (getenv('NEWS_ISSUE') !== 'true') {
    $hasDate = str_contains($body, '### Data da notícia') || str_contains($body, '### Data da publicação');
    $isNews = $isNews
        && str_contains($body, '### Resumo')
        && $hasDate
        && str_contains($body, '### Texto da notícia');
}

if (! $isNews) {
    echo "is_news=false\nchanged=false\n";
    exit(0);
}

try {
    $result = (new NewsIssueSynchronizer(
        postsDirectory: __DIR__ . '/../source/_posts',
        mediaLocalizer: new NewsMediaLocalizer(
            __DIR__ . '/../source/assets/images/news',
        ),
    ))->synchronize((int) $number, $title, $body, $url, $updatedAt);
} catch (InvalidArgumentException $exception) {
    $message = str_replace(["\r", "\n"], ' ', $exception->getMessage());
    echo "is_news=true\n";
    echo "valid=false\n";
    echo "changed=false\n";
    echo 'validation_error=' . $message . "\n";
    exit(0);
}

echo "is_news=true\n";
echo "valid=true\n";
echo 'changed=' . ($result['changed'] ? 'true' : 'false') . "\n";
echo 'created=' . ($result['created'] ? 'true' : 'false') . "\n";
echo 'path=' . $result['path'] . "\n";
echo 'slug=' . $result['slug'] . "\n";
