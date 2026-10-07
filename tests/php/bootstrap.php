<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__, 2) . '/src/Seo/PageUrlResolver.php';
require_once dirname(__DIR__, 2) . '/src/Seo/SocialImageResolver.php';
require_once dirname(__DIR__, 2) . '/src/Seo/StructuredDataBuilder.php';
require_once dirname(__DIR__, 2) . '/src/Seo/SeoMetadataBuilder.php';
require_once dirname(__DIR__, 2) . '/listeners/GenerateRobots.php';
