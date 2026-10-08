{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}

<article class="card post-card">
    @if ($post->cover_image)
        @php
            $coverImageUrl = str_starts_with((string) $post->cover_image, '/')
                ? rtrim((string) $page->baseUrl, '/') . $post->cover_image
                : $post->cover_image;
        @endphp
        <a class="post-card__media" href="{{ $post->getUrl() }}" tabindex="-1" aria-hidden="true">
            <img src="{{ $coverImageUrl }}" alt="" loading="lazy">
        </a>
    @endif
    <div class="post-card__body">
        <p class="eyebrow">{{ $post->getDate()->format('d/m/Y') }}</p>
        <h3><a href="{{ $post->getUrl() }}">{{ $post->title }}</a></h3>
        <p>{!! $post->getExcerpt() !!}</p>
        <a class="text-link" href="{{ $post->getUrl() }}">Ler notícia →</a>
    </div>
</article>
