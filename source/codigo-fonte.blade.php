---
title: Código-fonte
description: Onde encontrar o código e as diferentes linhas de desenvolvimento do e-Cidade.
---
{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}
@extends('_layouts.main')

@section('body')
<section class="page-hero">
    <div class="shell shell--narrow">
        <p class="eyebrow">Código aberto</p>
        <h1>O código do e-Cidade faz parte de um ecossistema.</h1>
        <p>O software pode ser acessado, estudado, adaptado e evoluído. Hoje existem diferentes linhas de desenvolvimento mantidas por participantes do ecossistema.</p>
    </div>
</section>

<section class="section">
    <div class="shell content-grid">
        <div class="prose">
            <h2>Onde está o código?</h2>
            <p>A organização <strong>e-cidade</strong> no GitHub funciona como ponto de entrada comunitário. Ela documenta projetos, distribuições, mirrors, origens e mantenedores sem atribuir a um único ator a propriedade de todo o ecossistema.</p>
            <p>Alguns repositórios são mantidos diretamente por seus respectivos mantenedores; outros aparecem na organização como mirrors sincronizados com suas origens.</p>
            <div class="actions">
                <a class="button button--primary" href="https://github.com/e-cidade/e-cidade">Mapa do ecossistema no GitHub</a>
                <a class="button button--secondary" href="{{ $page->spbUrl }}">Portal do Software Público</a>
            </div>
        </div>
        <aside class="aside-card">
            <p class="eyebrow">Como contribuir</p>
            <p>Escolha a linha de desenvolvimento relacionada à sua necessidade. Se não souber onde uma contribuição deve ser feita, consulte o repositório comunitário do e-Cidade.</p>
            <a class="text-link" href="{{ $page->communityUrl }}">Abrir repositório comunitário →</a>
        </aside>
    </div>
</section>
@endsection
