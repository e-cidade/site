{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title ? $page->title . ' | ' : '' }}{{ $page->siteName }}</title>
    @include('_partials.seo')

    <link rel="icon" type="image/png" href="{{ $page->logoUrl }}">
    <link rel="apple-touch-icon" href="{{ $page->logoUrl }}">
    @viteRefresh()
    <link rel="stylesheet" href="{{ $page->baseUrl }}{{ vite('source/_assets/scss/main.scss') }}">
</head>
<body>
    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

    <header class="site-header">
        <div class="shell header-inner">
            <a class="brand" href="{{ $page->baseUrl }}/" aria-label="e-Cidade — início">
                <img src="{{ $page->logoUrl }}" alt="e-Cidade">
            </a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav">
                Menu
            </button>

            <nav id="main-nav" class="main-nav" aria-label="Navegação principal">
                <a href="{{ $page->baseUrl }}/sobre" class="{{ $page->isActive('/sobre') ? 'active' : '' }}">Sobre</a>
                <a href="{{ $page->baseUrl }}/noticias" class="{{ $page->isActive('/noticias') ? 'active' : '' }}">Notícias</a>
                <a href="{{ $page->baseUrl }}/codigo-fonte" class="{{ $page->isActive('/codigo-fonte') ? 'active' : '' }}">Código-fonte</a>
                <a href="{{ $page->baseUrl }}/manuais-e-documentacoes" class="{{ $page->isActive('/manuais-e-documentacoes') ? 'active' : '' }}">Documentação</a>
                <a href="{{ $page->baseUrl }}/prestadores-de-servico" class="{{ $page->isActive('/prestadores-de-servico') ? 'active' : '' }}">Prestadores</a>
            </nav>
        </div>
    </header>

    <main id="conteudo">
        @yield('body')
    </main>

    <footer class="site-footer">
        <div class="shell footer-grid">
            <div>
                <img class="footer-logo" src="{{ $page->logoUrl }}" alt="e-Cidade">
                <p>Software livre para gestão pública municipal integrada.</p>
            </div>
            <nav aria-label="Links da comunidade">
                <strong>Comunidade</strong>
                <a href="{{ $page->communityUrl }}">GitHub</a>
                <a href="{{ $page->telegramUrl }}">Telegram</a>
                <a href="{{ $page->forumUrl }}">Fórum</a>
            </nav>
            <nav aria-label="Links do site">
                <strong>Projeto</strong>
                <a href="{{ $page->baseUrl }}/sobre">Sobre</a>
                <a href="{{ $page->baseUrl }}/noticias">Notícias</a>
                <a href="{{ $page->baseUrl }}/codigo-fonte">Código-fonte</a>
                <a href="{{ $page->siteRepositoryUrl }}" target="_blank" rel="noopener noreferrer">Repositório do site</a>
            </nav>
        </div>
        <div class="shell footer-meta">
            <span>Conteúdo mantido pela comunidade e-Cidade.</span>
            <span>Software livre, colaboração e governo digital.</span>
        </div>
    </footer>

    <script type="module" src="{{ $page->baseUrl }}{{ vite('source/_assets/js/main.js') }}"></script>
</body>
</html>
