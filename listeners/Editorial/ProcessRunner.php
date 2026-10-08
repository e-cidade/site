<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

interface ProcessRunner
{
    /** @param list<string> $command */
    public function run(array $command, ?string $workingDirectory = null): ProcessResult;
}
