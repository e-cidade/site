{{-- SPDX-FileCopyrightText: 2026 e-Cidade community --}}
{{-- SPDX-License-Identifier: AGPL-3.0-or-later --}}
<a class="provider-card" href="{{ $provider->website }}">
    <span class="provider-card__logo">
        <img src="{{ $page->baseUrl }}{{ $provider->logo }}" alt="{{ $provider->logo_alt }}" loading="lazy" decoding="async">
    </span>
    <strong>{{ $provider->name }}</strong>
    <span class="provider-card__link">{{ $provider->website_label }} →</span>
</a>
