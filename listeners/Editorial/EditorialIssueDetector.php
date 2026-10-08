<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class EditorialIssueDetector
{
    /** @param list<string> $labels */
    public function isNews(
        string $body,
        array $labels,
        bool $branchExists = false,
        bool $pullRequestExists = false,
        bool $forced = false,
    ): bool {
        if ($forced || in_array(EditorialLabels::NEWS, $labels, true)) {
            return true;
        }

        $hasLegacyContract = str_contains($body, '### Resumo')
            && (str_contains($body, '### Data da notícia') || str_contains($body, '### Data da publicação'))
            && str_contains($body, '### Texto da notícia');

        return $hasLegacyContract || $branchExists || $pullRequestExists;
    }
}
