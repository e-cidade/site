---
title: Prestadores de serviços
description: Empresas credenciadas e outras empresas que atuam com o e-Cidade.
---
{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}
@extends('_layouts.main')

@section('body')
<section class="page-hero">
    <div class="shell shell--narrow">
        <p class="eyebrow">Ecossistema</p>
        <h1>Prestadores de serviços</h1>
        <p>Empresas que colaboram com desenvolvimento, implantação, suporte e evolução do e-Cidade.</p>
    </div>
</section>

<section class="section">
    <div class="shell">
        <div class="section-heading">
            <div><p class="eyebrow">Comitê Gestor</p><h2>Empresas credenciadas</h2></div>
        </div>
        <p class="section-intro">As empresas credenciadas participam do desenvolvimento colaborativo da solução, aportando novos códigos e conteúdos e atuando na comunidade.</p>
        <div class="provider-grid">
            @foreach ($providers->where('group', 'credenciada') as $provider)
                @include('_components.provider-card', ['provider' => $provider])
            @endforeach
        </div>

        <div class="section-heading section-heading--spaced">
            <div><p class="eyebrow">Ecossistema ampliado</p><h2>Outras empresas</h2></div>
        </div>
        <div class="provider-grid">
            @foreach ($providers->where('group', 'outra') as $provider)
                @include('_components.provider-card', ['provider' => $provider])
            @endforeach
        </div>
    </div>
</section>
@endsection
