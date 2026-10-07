<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

$previewBaseUrl = getenv('PREVIEW_BASE_URL');

if ($previewBaseUrl === false || $previewBaseUrl === '') {
    throw new RuntimeException('PREVIEW_BASE_URL must be defined for preview builds.');
}

return [
    'baseUrl' => rtrim($previewBaseUrl, '/'),
    'production' => true,
    'indexable' => false,
];
