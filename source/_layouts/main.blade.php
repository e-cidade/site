{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}

@php
    $documentTitle = $page->title ? $page->title . ' | ' . $page->siteName : $page->siteName;
    $description = $page->description ?? $page->siteDescription;
    $canonicalUrl = $page->getUrl();
    $isArticle = ($page->type ?? null) === 'article';
    $indexable = ($page->indexable ?? true) && !($page->noindex ?? false);

    $resolveAbsoluteUrl = static function (?string $url) use ($page): ?string {
        if ($url === null || $url === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }
        return rtrim((string) $page->baseUrl, '/') . '/' . ltrim($url, '/');
    };

    $coverImage = $page->cover_image ?? null;
    $socialImage = $resolveAbsoluteUrl($coverImage ?: $page->logoUrl);
    $socialImageAlt = $coverImage ? ($page->cover_alt ?? $page->title ?? $page->siteName) : $page->siteName;
    $twitterCard = $coverImage ? 'summary_large_image' : 'summary';
    $siteUrl = rtrim((string) $page->baseUrl, '/');

    $structuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl . '/#website',
                'url' => $siteUrl . '/',
                'name' => $page->siteName,
                'description' => $page->siteDescription,
            ],
            [
                '@type' => 'SoftwareApplication',
                '@id' => $siteUrl . '/#software',
                'name' => $page->siteName,
                'url' => $siteUrl . '/',
                'description' => $page->siteDescription,
                'applicationCategory' => 'GovernmentApplication',
                'operatingSystem' => 'Web',
                'isAccessibleForFree' => true,
                'sameAs' => [$page->communityUrl],
            ],
        ],
    ];

    if ($isArticle) {
        $article = [
            '@type' => 'Article',
            '@id' => $canonicalUrl . '#article',
            'headline' => $page->title,
            'description' => $description,
            'mainEntityOfPage' => $canonicalUrl,
            'author' => [
                '@type' => 'Organization',
                'name' => $page->author ?? $page->siteAuthor,
            ],
        ];

        if ($socialImage) {
            $article['image'] = [$socialImage];
        }
        if ($page->date ?? null) {
            $article['datePublished'] = $page->getDate()->format(DATE_ATOM);
        }

        $structuredData['@graph'][] = $article;
    }
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentTitle }}</title>

    <meta name="description" content="{{ $description }}">
    <meta name="author" content="{{ $page->siteAuthor }}">
    @if ($indexable)
        <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
    @else
        <meta name="robots" content="noindex,nofollow,noarchive">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="{{ $isArticle ? 'article' : 'website' }}">
    <meta property="og:site_name" content="{{ $page->siteName }}">
    <meta property="og:title" content="{{ $documentTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:locale" content="pt_BR">
    @if ($socialImage)
        <meta property="og:image" content="{{ $socialImage }}">
        <meta property="og:image:secure_url" content="{{ $socialImage }}">
        <meta property="og:image:alt" content="{{ $socialImageAlt }}">
    @endif
    @if ($isArticle && ($page->date ?? null))
        <meta property="article:published_time" content="{{ $page->getDate()->format(DATE_ATOM) }}">
    @endif
    @if ($isArticle)
        <meta property="article:author" content="{{ $page->author ?? $page->siteAuthor }}">
    @endif

    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $documentTitle }}">
    <meta name="twitter:description" content="{{ $description }}">
    @if ($socialImage)
        <meta name="twitter:image" content="{{ $socialImage }}">
        <meta name="twitter:image:alt" content="{{ $socialImageAlt }}">
    @endif

    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <link rel="icon" type="image/png" href="{{ $page->logoUrl }}">
    <link rel="apple-touch-icon" href="{{ $page->logoUrl }}">
    @viteRefresh()
    <link rel="stylesheet" href="{{ vite('source/_assets/scss/main.scss') }}">
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
            </nav>
        </div>
        <div class="shell footer-meta">
            <span>Conteúdo mantido pela comunidade e-Cidade.</span>
            <span>Software livre, colaboração e governo digital.</span>
        </div>
    </footer>

    <script type="module" src="{{ vite('source/_assets/js/main.js') }}"></script>
</body>
</html>
