---
title: Manuais e documentações
description: Manuais, referências e documentação disponível sobre o e-Cidade.
---
{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}
@extends('_layouts.main')

@section('body')
<section class="page-hero">
    <div class="shell shell--narrow">
        <p class="eyebrow">Conhecimento</p>
        <h1>Manuais e documentações</h1>
        <p>Conteúdo técnico e operacional publicado pela comunidade e por participantes do ecossistema.</p>
    </div>
</section>

<section class="section">
    <div class="shell">
        <div class="resource-list">
            <article class="resource-item">
                <div>
                    <p class="eyebrow">30/12/2024</p>
                    <h2>Manual do Financeiro</h2>
                    <p>Registro de documentação publicado no site anterior.</p>
                </div>
                <a class="text-link" href="{{ $page->baseUrl }}/manuais/manual-do-financeiro">Abrir →</a>
            </article>
        </div>
    </div>
</section>
@endsection
