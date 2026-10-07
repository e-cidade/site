{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}

<article class="card post-card">
    <p class="eyebrow">{{ $post->getDate()->format('d/m/Y') }}</p>
    <h3><a href="{{ $post->getUrl() }}">{{ $post->title }}</a></h3>
    <p>{!! $post->getExcerpt() !!}</p>
    <a class="text-link" href="{{ $post->getUrl() }}">Ler notícia →</a>
</article>
