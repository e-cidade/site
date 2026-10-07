<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

require_once __DIR__ . '/src/Seo/PageUrlResolver.php';
require_once __DIR__ . '/src/Seo/SocialImageResolver.php';
require_once __DIR__ . '/src/Seo/StructuredDataBuilder.php';
require_once __DIR__ . '/src/Seo/SeoMetadataBuilder.php';

use App\Seo\PageUrlResolver;
use App\Seo\SeoMetadataBuilder;
use App\Seo\SocialImageResolver;
use App\Seo\StructuredDataBuilder;

$seoMetadataBuilder = new SeoMetadataBuilder(
    new PageUrlResolver(),
    new SocialImageResolver(),
    new StructuredDataBuilder(),
);

return [
    'seoMetadata' => static function ($page) use ($seoMetadataBuilder): array {
        return $seoMetadataBuilder->build($page);
    },
];
