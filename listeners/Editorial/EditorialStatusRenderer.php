<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class EditorialStatusRenderer
{
    public const MARKER = '<!-- e-cidade-editorial-status -->';

    public function render(EditorialStatus $status): string
    {
        $lines = [
            self::MARKER,
            '### Status editorial',
            '',
            '**' . $status->state->title() . '**',
        ];

        if ($status->detail !== null && trim($status->detail) !== '') {
            $lines[] = '';
            $lines[] = trim($status->detail);
        }

        if ($status->pullRequestUrl !== null) {
            $lines[] = '';
            $lines[] = 'Pull Request: ' . $status->pullRequestUrl;
        }

        if ($status->previewUrl !== null) {
            $lines[] = 'Prévia: ' . $status->previewUrl;
        }

        if ($status->publicationUrl !== null) {
            $lines[] = 'Publicação: ' . $status->publicationUrl;
        }

        $lines[] = '';
        $lines[] = '_Este comentário é atualizado automaticamente pelo fluxo editorial._';

        return implode("\n", $lines) . "\n";
    }
}
