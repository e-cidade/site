---
title: Página não encontrada
description: A página solicitada não foi encontrada.
---
{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}
@extends('_layouts.main')

@section('body')
<section class="page-hero">
    <div class="shell shell--narrow">
        <p class="eyebrow">Erro 404</p>
        <h1>Esta página não existe.</h1>
        <p>O endereço pode ter mudado durante a migração do site. Use a navegação principal ou volte à página inicial.</p>
        <a class="button button--primary" href="{{ $page->baseUrl }}/">Voltar ao início</a>
    </div>
</section>
@endsection
