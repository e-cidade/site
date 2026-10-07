{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}
@php
    $seo = $page->seoMetadata();
@endphp
<meta name="description" content="{{ $seo['description'] }}">
<meta name="author" content="{{ $seo['authorName'] }}">
@if ($seo['indexable'])
    <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
@else
    <meta name="robots" content="noindex,nofollow,noarchive">
@endif
<link rel="canonical" href="{{ $seo['canonicalUrl'] }}">
<meta property="og:type" content="{{ $seo['ogType'] }}">
<meta property="og:title" content="{{ $seo['documentTitle'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonicalUrl'] }}">
<meta property="og:site_name" content="{{ $seo['siteName'] }}">
<meta property="og:locale" content="{{ $seo['ogLocale'] }}">
<meta property="og:image" content="{{ $seo['socialImage']['url'] }}">
<meta property="og:image:secure_url" content="{{ $seo['socialImage']['url'] }}">
@if ($seo['socialImage']['width'] > 0 && $seo['socialImage']['height'] > 0)
    <meta property="og:image:width" content="{{ $seo['socialImage']['width'] }}">
    <meta property="og:image:height" content="{{ $seo['socialImage']['height'] }}">
@endif
<meta property="og:image:alt" content="{{ $seo['socialImage']['alt'] }}">
@if ($seo['publishedTime'])
    <meta property="article:published_time" content="{{ $seo['publishedTime'] }}">
@endif
<meta name="twitter:card" content="{{ $seo['socialImage']['twitterCard'] }}">
<meta name="twitter:title" content="{{ $seo['documentTitle'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['socialImage']['url'] }}">
<meta name="twitter:image:alt" content="{{ $seo['socialImage']['alt'] }}">
<script type="application/ld+json">{!! json_encode($seo['structuredData'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
