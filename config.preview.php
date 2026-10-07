<?php

declare(strict_types=1);

$previewBaseUrl = getenv('PREVIEW_BASE_URL');

if ($previewBaseUrl === false || $previewBaseUrl === '') {
    throw new RuntimeException('PREVIEW_BASE_URL must be defined for preview builds.');
}

return [
    'baseUrl' => rtrim($previewBaseUrl, '/'),
    'production' => true,
];
