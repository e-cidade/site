{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}

<article class="card post-card">
    @if ($post->cover_image)
        <a class="post-card__media" href="{{ $post->getUrl() }}" tabindex="-1" aria-hidden="true">
            <img src="{{ $post->cover_image }}" alt="" loading="lazy" decoding="async">
        </a>
    @endif

    <div class="post-card__body">
        <p class="eyebrow">{{ $post->getDate()->format('d/m/Y') }}</p>
        <h3><a href="{{ $post->getUrl() }}">{{ $post->title }}</a></h3>
        <div class="post-card__excerpt">{!! $post->getExcerpt() !!}</div>
        <a class="text-link" href="{{ $post->getUrl() }}">Ler notícia →</a>
    </div>
</article>
