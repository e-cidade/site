---
title: e-Cidade
description: Software livre para gestão pública municipal integrada, construído e mantido por um ecossistema de organizações, empresas, comunidades e profissionais.
---
@extends('_layouts.main')

@section('body')
<section class="hero">
    <div class="shell hero-grid">
        <div>
            <p class="eyebrow">Software público · Código aberto · Gestão municipal</p>
            <h1>Gestão pública integrada com liberdade para evoluir.</h1>
            <p class="hero-lead">O e-Cidade informatiza a gestão dos municípios brasileiros e integra prefeitura, câmara, autarquias, fundações e outros entes municipais em uma plataforma de software livre.</p>
            <div class="actions">
                <a class="button button--primary" href="{{ $page->communityUrl }}">Explorar o ecossistema no GitHub</a>
                <a class="button button--secondary" href="{{ $page->baseUrl }}/sobre">Conhecer o projeto</a>
            </div>
        </div>
        <aside class="hero-panel">
            <span class="hero-number">20+</span>
            <strong>anos de história do software público brasileiro</strong>
            <p>Um projeto que combina continuidade, autonomia tecnológica e colaboração entre diferentes participantes do ecossistema.</p>
        </aside>
    </div>
</section>

<section class="section">
    <div class="shell">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Por que e-Cidade</p>
                <h2>Infraestrutura digital pública que pode ser estudada, adaptada e compartilhada.</h2>
            </div>
        </div>
        <div class="feature-grid">
            <article class="card"><span class="card-index">01</span><h3>Gestão integrada</h3><p>Centraliza processos de diferentes áreas e entes municipais, reduzindo fragmentação e retrabalho.</p></article>
            <article class="card"><span class="card-index">02</span><h3>Liberdade de escolha</h3><p>O código aberto reduz dependência de um único fornecedor e permite diferentes linhas de desenvolvimento.</p></article>
            <article class="card"><span class="card-index">03</span><h3>Desenvolvimento colaborativo</h3><p>Municípios, empresas, comunidade e academia podem contribuir com evolução, documentação e conhecimento.</p></article>
        </div>
    </div>
</section>

<section class="section section--muted">
    <div class="shell">
        <div class="section-heading section-heading--inline">
            <div>
                <p class="eyebrow">Atualizações</p>
                <h2>Notícias recentes</h2>
            </div>
            <a class="text-link" href="{{ $page->baseUrl }}/noticias">Ver todas →</a>
        </div>
        <div class="post-grid">
            @foreach ($posts->take(3) as $post)
                @include('_components.post-card', ['post' => $post])
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="shell community-grid">
        <div>
            <p class="eyebrow">Participe</p>
            <h2>O e-Cidade é maior que um único repositório.</h2>
            <p>O desenvolvimento acontece em diferentes linhas mantidas por participantes do ecossistema. A organização comunitária no GitHub funciona como ponto de entrada para localizar projetos, documentação e espaços de colaboração.</p>
        </div>
        <div class="community-links">
            <a class="community-link" href="{{ $page->communityUrl }}"><strong>GitHub</strong><span>Código, projetos e governança →</span></a>
            <a class="community-link" href="{{ $page->forumUrl }}"><strong>Fórum</strong><span>Dúvidas e conhecimento compartilhado →</span></a>
            <a class="community-link" href="{{ $page->telegramUrl }}"><strong>Telegram</strong><span>Conversa em tempo real com a comunidade →</span></a>
        </div>
    </div>
</section>
@endsection
