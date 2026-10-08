<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final readonly class EditorialWorkspace
{
    public function __construct(
        public string $branch,
        public string $path,
        public ?string $remoteSha,
        public bool $refreshRequired,
    ) {}
}
