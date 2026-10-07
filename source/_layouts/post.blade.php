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
    </header>

    <div class="prose">
        @yield('content')
    </div>

    @if ($page->source_url)
        <p class="source-note">Fonte original: <a href="{{ $page->source_url }}">{{ $page->source_label ?? $page->source_url }}</a></p>
    @endif

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
