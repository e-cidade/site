<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\WordPress;

use DateTimeImmutable;

final class WordPressIssueExporter
{
    public function __construct(private readonly WordPressClient $client) {}

    /** @return list<array{wordpress_id:int,title:string,body:string,hash:string}> */
    public function export(): array
    {
        $records = [];

        foreach ($this->client->fetchPosts() as $post) {
            $record = $this->normalize($post);
            if ($record !== null) {
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * @param array<string, mixed> $post
     * @return array{wordpress_id:int,title:string,body:string,hash:string}|null
     */
    private function normalize(array $post): ?array
    {
        $id = filter_var($post['id'] ?? null, FILTER_VALIDATE_INT);
        $slug = $this->safeSlug((string) ($post['slug'] ?? ''));
        $title = $this->decodeRenderedText($post['title']['rendered'] ?? null);
        $description = $this->decodeRenderedText($post['excerpt']['rendered'] ?? null);
        $content = isset($post['content']['rendered']) && is_string($post['content']['rendered'])
            ? trim($post['content']['rendered'])
            : '';
        $date = $this->normalizeDate($post['date'] ?? null);
        $sourceUrl = isset($post['link']) && is_string($post['link']) ? trim($post['link']) : '';

        if ($id === false || $slug === '' || $title === '' || $description === '' || $content === '' || $date === null) {
            return null;
        }

        $author = 'Comunidade e-Cidade';
        $coverUrl = null;
        $coverAlt = null;

        $embedded = is_array($post['_embedded'] ?? null) ? $post['_embedded'] : [];
        $authors = $embedded['author'] ?? [];
        if (is_array($authors) && isset($authors[0]['name']) && is_string($authors[0]['name'])) {
            $candidate = $this->decodeRenderedText($authors[0]['name']);
            if ($candidate !== '' && mb_strtolower($candidate) !== 'administrador') {
                $author = $candidate;
            }
        }

        $media = $embedded['wp:featuredmedia'] ?? [];
        if (is_array($media) && isset($media[0]) && is_array($media[0])) {
            $candidateUrl = isset($media[0]['source_url']) && is_string($media[0]['source_url'])
                ? trim($media[0]['source_url'])
                : '';
            $candidateAlt = isset($media[0]['alt_text']) && is_string($media[0]['alt_text'])
                ? trim($media[0]['alt_text'])
                : '';

            if ($candidateUrl !== '' && $candidateAlt !== '') {
                $coverUrl = $candidateUrl;
                $coverAlt = $candidateAlt;
            }
        }

        $payload = [
            'wordpress_id' => (int) $id,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'date' => $date,
            'author' => $author,
            'content' => $content,
            'cover_url' => $coverUrl,
            'cover_alt' => $coverAlt,
            'source_url' => $sourceUrl,
        ];

        $hash = hash('sha256', json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ));

        $body = sprintf(
            "<!-- e-cidade-wordpress-id:%d -->\n<!-- e-cidade-wordpress-hash:%s -->\n<!-- e-cidade-slug:%s -->\n\n"
            . "### Resumo\n\n%s\n\n"
            . "### Data da publicação\n\n%s\n\n"
            . "### Autor\n\n%s\n\n"
            . "### Texto da notícia\n\n%s\n\n"
            . "### Imagem de capa\n\n%s\n\n"
            . "### Texto alternativo da imagem\n\n%s\n\n"
            . "### Fonte original\n\n%s\n\n"
            . "### URL da fonte\n\n%s\n",
            (int) $id,
            $hash,
            $slug,
            $description,
            $date,
            $author,
            $content,
            $coverUrl ?? '_No response_',
            $coverAlt ?? '_No response_',
            $sourceUrl !== '' ? 'Site e-Cidade' : '_No response_',
            $sourceUrl !== '' ? $sourceUrl : '_No response_',
        );

        return [
            'wordpress_id' => (int) $id,
            'title' => $title,
            'body' => $body,
            'hash' => $hash,
        ];
    }

    private function decodeRenderedText(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function safeSlug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9-]+/', '-', $value) ?? '';

        return trim(preg_replace('/-+/', '-', $value) ?? '', '-');
    }
}
