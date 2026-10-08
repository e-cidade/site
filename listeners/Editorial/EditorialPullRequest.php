<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final readonly class EditorialPullRequest
{
    public function __construct(
        public int $number,
        public string $url,
        public string $headRef,
        public bool $draft = true,
    ) {}

    public function issueNumber(): ?int
    {
        if (preg_match('/^content\/news-(\d+)$/', $this->headRef, $match) !== 1) {
            return null;
        }

        return (int) $match[1];
    }
}
