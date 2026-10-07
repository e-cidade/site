<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\Listeners\Editorial;

use DateTimeImmutable;
use InvalidArgumentException;

final class NewsIssueContract
{
    public const DEFAULT_AUTHOR = 'Comunidade e-Cidade';

    /** @return array{issue:int,title:string,summary:string,published_at:string,author:string,content:string,cover:?string,cover_alt:?string,source_label:?string,source_url:?string,preferred_slug:?string} */
    public function parse(int $issueNumber, string $title, string $body): array
    {
        $title = trim($title);
        if ($title === '') {
            throw new InvalidArgumentException('O título da notícia é obrigatório.');
        }

        $sections = $this->sections($body);
        $summary = $this->required($sections, 'Resumo');
        $publishedAt = $this->required($sections, 'Data da publicação');
        $content = $this->required($sections, 'Texto da notícia');

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $publishedAt);
        if ($date === false || $date->format('Y-m-d') !== $publishedAt) {
            throw new InvalidArgumentException('A data da publicação deve usar o formato AAAA-MM-DD.');
        }

        $author = $this->optional($sections, 'Autor') ?? self::DEFAULT_AUTHOR;
        $cover = $this->optional($sections, 'Imagem de capa');
        $coverAlt = $this->optional($sections, 'Texto alternativo da imagem');
        $sourceLabel = $this->optional($sections, 'Fonte original');
        $sourceUrl = $this->optional($sections, 'URL da fonte');
        $preferredSlug = null;

        if (preg_match('/<!--\s*e-cidade-slug:([a-z0-9]+(?:-[a-z0-9]+)*)\s*-->/', $body, $match) === 1) {
            $preferredSlug = $match[1];
        }

        if ($cover !== null && $coverAlt === null) {
            throw new InvalidArgumentException('A imagem de capa exige texto alternativo.');
        }

        if (($sourceLabel === null) !== ($sourceUrl === null)) {
            throw new InvalidArgumentException('O nome e a URL da fonte original devem ser informados juntos.');
        }

        if ($sourceUrl !== null && filter_var($sourceUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('A URL da fonte original é inválida.');
        }

        return [
            'issue' => $issueNumber,
            'title' => $title,
            'summary' => $summary,
            'published_at' => $publishedAt,
            'author' => $author,
            'content' => $content,
            'cover' => $cover,
            'cover_alt' => $coverAlt,
            'source_label' => $sourceLabel,
            'source_url' => $sourceUrl,
            'preferred_slug' => $preferredSlug,
        ];
    }

    /** @return array<string, string> */
    private function sections(string $body): array
    {
        preg_match_all('/^### (.+?)\R(.*?)(?=^### |\z)/ms', $body, $matches, PREG_SET_ORDER);

        $sections = [];
        foreach ($matches as $match) {
            $sections[trim($match[1])] = trim($match[2]);
        }

        return $sections;
    }

    /** @param array<string, string> $sections */
    private function required(array $sections, string $name): string
    {
        $value = $this->optional($sections, $name);
        if ($value === null) {
            throw new InvalidArgumentException(sprintf('O campo obrigatório "%s" está ausente.', $name));
        }

        return $value;
    }

    /** @param array<string, string> $sections */
    private function optional(array $sections, string $name): ?string
    {
        $value = trim($sections[$name] ?? '');
        if ($value === '' || $value === '_No response_') {
            return null;
        }

        return $value;
    }
}
