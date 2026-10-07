<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Tests\Unit\Seo;

#[\AllowDynamicProperties]
final class SeoPageStub
{
    public function __construct(private readonly string $path, array $properties = [])
    {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDate(): \DateTime
    {
        return \DateTime::createFromFormat('U', (string) $this->date);
    }
}
