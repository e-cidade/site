<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

enum EditorialState: string
{
    case Draft = 'draft';
    case Invalid = 'invalid';
    case Review = 'review';
    case Ready = 'ready';
    case Published = 'published';
    case Discarded = 'discarded';

    public function label(): string
    {
        return 'editorial/' . $this->value;
    }

    public function title(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho editorial',
            self::Invalid => 'Ação necessária',
            self::Review => 'Em revisão',
            self::Ready => 'Aprovada para publicação',
            self::Published => 'Publicada',
            self::Discarded => 'Descartada',
        };
    }
}
