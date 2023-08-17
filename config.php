<?php

use Illuminate\Support\Str;

return [
    'baseUrl' => '',
    'production' => false,
    'siteName' => 'e-Cidade',
    'siteDescription' => 'O e-cidade destina-se a informatizar a gestão dos Municípios Brasileiros de forma integrada. Esta informatização contempla a integração entre os entes municipais: Prefeitura Municipal, Câmara Municipal, Autarquias, Fundações e outros.',
    'siteAuthor' => 'LibreCode coop',

    // collections
    'collections' => [
        'posts' => [
            'author' => 'LibreCode coop', // Default author, if not provided in a post
            'sort' => '-date',
            'path' => 'blog/{filename}',
        ],
        'categorias' => [
            'path' => '/blog/categorias/{filename}',
            'posts' => function ($page, $allPosts) {
                return $allPosts->filter(function ($post) use ($page) {
                    return $post->categorias ? in_array($page->getFilename(), $post->categorias, true) : false;
                });
            },
        ],
    ],

    // helpers
    'getDate' => function ($page) {
        return Datetime::createFromFormat('U', $page->date);
    },
    'getExcerpt' => function ($page, $length = 255) {
        if ($page->excerpt) {
            return $page->excerpt;
        }

        $content = preg_split('/<!-- more -->/m', $page->getContent(), 2);
        $cleaned = trim(
            strip_tags(
                preg_replace(['/<pre>[\w\W]*?<\/pre>/', '/<h\d>[\w\W]*?<\/h\d>/'], '', $content[0]),
                '<code>'
            )
        );

        if (count($content) > 1) {
            return $cleaned;
        }

        $truncated = substr($cleaned, 0, $length);

        if (substr_count($truncated, '<code>') > substr_count($truncated, '</code>')) {
            $truncated .= '</code>';
        }

        return strlen($cleaned) > $length
            ? preg_replace('/\s+?(\S+)?$/', '', $truncated) . '...'
            : $cleaned;
    },
    'isActive' => function ($page, $path) {
        return Str::endsWith(trimPath($page->getPath()), trimPath($path));
    },
];
