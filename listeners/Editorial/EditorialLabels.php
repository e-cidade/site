<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

final class EditorialLabels
{
    public const NEWS = 'editorial/news';

    /** @return array<string, array{color:string,description:string}> */
    public static function definitions(): array
    {
        return [
            self::NEWS => [
                'color' => '0E8A16',
                'description' => 'Identifica Issues que pertencem ao fluxo editorial de notícias',
            ],
            EditorialState::Draft->label() => [
                'color' => 'D4C5F9',
                'description' => 'Notícia em rascunho editorial',
            ],
            EditorialState::Invalid->label() => [
                'color' => 'D73A4A',
                'description' => 'Notícia com dados editoriais inválidos',
            ],
            EditorialState::Review->label() => [
                'color' => 'FBCA04',
                'description' => 'Notícia aguardando revisão editorial',
            ],
            EditorialState::Ready->label() => [
                'color' => '1D76DB',
                'description' => 'Notícia aprovada e pronta para publicação',
            ],
            EditorialState::Published->label() => [
                'color' => '0E8A16',
                'description' => 'Notícia publicada',
            ],
            EditorialState::Discarded->label() => [
                'color' => '6E7781',
                'description' => 'Proposta editorial descartada',
            ],
        ];
    }

    /** @return list<string> */
    public static function stateLabels(): array
    {
        return array_map(
            static fn (EditorialState $state): string => $state->label(),
            EditorialState::cases(),
        );
    }
}
