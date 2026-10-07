<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

use Illuminate\Support\Str;

return [
    'baseUrl' => '',
    'production' => false,
    'siteName' => 'e-Cidade',
    'siteDescription' => 'Software livre para gestão pública municipal integrada, desenvolvido de forma colaborativa pelo ecossistema e-Cidade.',
    'siteAuthor' => 'Comunidade e-Cidade',
    'communityUrl' => 'https://github.com/e-cidade/e-cidade',
    'telegramUrl' => 'https://t.me/eCidadeCE',
    'forumUrl' => 'https://ecidades.popsolutions.co',
    'spbUrl' => 'https://softwarepublico.gov.br/social/e-cidade/',

    'collections' => [
        'posts' => [
            'author' => 'Comunidade e-Cidade',
            'sort' => '-date',
            'path' => '{filename}',
        ],
    ],

    'getDate' => function ($page) {
        return DateTime::createFromFormat('U', (string) $page->date);
    },

    'getExcerpt' => function ($page, $length = 220) {
        if ($page->excerpt) {
            return $page->excerpt;
        }

        $content = preg_split('/<!-- more -->/m', $page->getContent(), 2);
        $cleaned = trim(strip_tags($content[0]));
        if (mb_strlen($cleaned) <= $length) {
            return $cleaned;
        }

        return rtrim(mb_substr($cleaned, 0, $length)) . '…';
    },

    'isActive' => function ($page, $path) {
        $current = trim(trimPath($page->getPath()), '/');
        $target = trim(trimPath($path), '/');

        return $current === $target || ($target !== '' && Str::startsWith($current, $target . '/'));
    },
];
