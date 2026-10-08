<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

interface EditorialContentSynchronizer
{
    /** @return array{changed:bool,created:bool,path:string,slug:string} */
    public function synchronize(
        EditorialWorkspace $workspace,
        int $issueNumber,
        string $title,
        string $body,
        string $issueUrl,
        string $updatedAt,
    ): array;
}
