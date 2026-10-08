{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}

@extends('_layouts.main')

@php
    $page->type = 'article';
@endphp

@section('body')
<article class="article shell shell--narrow">
    <a class="eyebrow link-back" href="{{ $page->baseUrl }}/noticias">← Notícias</a>
    <header class="article-header">
        <p class="eyebrow">{{ $page->category ?? 'Notícias' }}</p>
        <h1>{{ $page->title }}</h1>
        <p class="article-meta">
            <time datetime="{{ $page->getDate()->format('Y-m-d') }}">{{ $page->getDate()->format('d/m/Y') }}</time>
            <span aria-hidden="true">•</span>
            <span>{{ $page->author }}</span>
        </p>
        @if ($page->cover_image)
            @php
                $coverImageUrl = str_starts_with((string) $page->cover_image, '/')
                    ? rtrim((string) $page->baseUrl, '/') . $page->cover_image
                    : $page->cover_image;
            @endphp
            <figure class="article-cover">
                <img src="{{ $coverImageUrl }}" alt="{{ $page->cover_alt ?? '' }}">
                @if ($page->cover_caption)
                    <figcaption>{{ $page->cover_caption }}</figcaption>
                @endif
            </figure>
        @endif
    </header>

    <div class="prose">
        @yield('content')
    </div>

    @if ($page->source_url)
        <p class="source-note">Fonte original: <a href="{{ $page->source_url }}">{{ $page->source_label ?? $page->source_url }}</a></p>
    @endif

    <section class="card" aria-labelledby="news-submit-title">
        <p class="eyebrow">Participe</p>
        <h3 id="news-submit-title">Tem uma novidade sobre o e-Cidade?</h3>
        <p>Compartilhe uma implantação, capacitação, evento, lançamento ou outra iniciativa da comunidade.</p>
        <div class="actions">
            <a
                class="button button--secondary"
                href="https://github.com/e-cidade/site/issues/new?template=news.yml"
                target="_blank"
                rel="noopener noreferrer"
            >Enviar uma notícia →</a>
        </div>
    </section>

    <nav class="post-nav" aria-label="Navegação entre notícias">
        <div>
            @if ($next = $page->getNext())
                <span>Anterior</span>
                <a href="{{ $next->getUrl() }}">{{ $next->title }}</a>
            @endif
        </div>
        <div>
            @if ($previous = $page->getPrevious())
                <span>Próxima</span>
                <a href="{{ $previous->getUrl() }}">{{ $previous->title }}</a>
            @endif
        </div>
    </nav>
</article>
@endsection
