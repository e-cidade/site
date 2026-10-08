<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Listeners\Editorial;

use App\Listeners\Editorial\ProcessResult;
use App\Listeners\Editorial\ProcessRunner;
use RuntimeException;

final class RecordingProcessRunner implements ProcessRunner
{
    /** @var list<array{command:list<string>,workingDirectory:?string}> */
    public array $calls = [];

    /** @var list<ProcessResult> */
    private array $results = [];

    public function queue(ProcessResult ...$results): void
    {
        array_push($this->results, ...$results);
    }

    public function run(array $command, ?string $workingDirectory = null): ProcessResult
    {
        $this->calls[] = ['command' => $command, 'workingDirectory' => $workingDirectory];

        if ($this->results === []) {
            throw new RuntimeException('No queued process result for: ' . implode(' ', $command));
        }

        return array_shift($this->results);
    }
}
