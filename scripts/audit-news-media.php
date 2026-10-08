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
$format = $argv[1] ?? 'json';

if ($format === '--markdown') {
    fwrite(STDOUT, "| Mídia | Estado | Referências | Fonte editorial | Evidência de proveniência |\n");
    fwrite(STDOUT, "| --- | --- | --- | --- | --- |\n");

    foreach ($result['media'] as $item) {
        $references = array_map(
            static fn(array $reference): string => $reference['post'],
            $item['references'],
        );
        $sources = array_values(array_unique(array_filter(array_map(
            static fn(array $reference): ?string => $reference['source_url'],
            $item['references'],
        ))));

        fwrite(
            STDOUT,
            sprintf(
                "| `%s` | %s | %s | %s | %s |\n",
                ltrim($item['public_path'], '/'),
                $item['status'],
                $references === [] ? '—' : implode('<br>', $references),
                $sources === [] ? '—' : implode('<br>', $sources),
                $item['evidence_urls'] === []
                    ? '—'
                    : implode('<br>', $item['evidence_urls']),
            ),
        );
    }
} else {
    fwrite(
        STDOUT,
        json_encode(
            $result,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ) . "\n",
    );
}

if ($result['missing_sidecar'] !== []) {
    exit(1);
}
