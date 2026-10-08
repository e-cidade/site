<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final readonly class EditorialStatus
{
    public function __construct(
        public EditorialState $state,
        public ?string $detail = null,
        public ?string $pullRequestUrl = null,
        public ?string $previewUrl = null,
        public ?string $publicationUrl = null,
    ) {}
}
