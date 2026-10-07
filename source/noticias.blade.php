---
title: Notícias
description: Notícias, iniciativas e atualizações do ecossistema e-Cidade.
pagination:
    collection: posts
    perPage: 6
---
{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}
@extends('_layouts.main')

@section('body')
<section class="page-hero">
    <div class="shell shell--narrow">
        <p class="eyebrow">Comunidade e-Cidade</p>
        <h1>Notícias</h1>
        <p>Atualizações sobre governança, capacitação, software público, bens públicos digitais e iniciativas relacionadas ao ecossistema.</p>
    </div>
</section>

<section class="section section--muted" aria-labelledby="news-submit-title">
    <div class="shell shell--narrow">
        <p class="eyebrow">Participe</p>
        <h2 id="news-submit-title">Compartilhe novidades da comunidade</h2>
        <p>Sua prefeitura está usando o e-Cidade? Houve uma capacitação, evento, implantação ou lançamento? Envie a notícia para revisão e publicação.</p>
        <div class="actions">
            <a
                class="button button--primary"
                href="https://github.com/e-cidade/site/issues/new?template=news.yml"
                target="_blank"
                rel="noopener noreferrer"
            >Enviar uma notícia →</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="shell">
        <div class="post-grid">
            @foreach ($pagination->items as $post)
                @include('_components.post-card', ['post' => $post])
            @endforeach
        </div>

        @if ($pagination->pages->count() > 1)
        <nav class="pagination" aria-label="Paginação de notícias">
            @if ($pagination->previous)
                <a href="{{ $pagination->previous }}">← Anterior</a>
            @endif
            @foreach ($pagination->pages as $pageNumber => $path)
                <a href="{{ $path }}" @if ($pagination->currentPage == $pageNumber) aria-current="page" @endif>{{ $pageNumber }}</a>
            @endforeach
            @if ($pagination->next)
                <a href="{{ $pagination->next }}">Próxima →</a>
            @endif
        </nav>
        @endif
    </div>
</section>
@endsection
