<nav class="hidden lg:flex items-center justify-end text-lg">
    <a title="{{ $page->siteName }} Blog" href="/blog"
        class="ml-6 text-gray-700 hover:text-blue-600 {{ $page->isActive('/blog') ? 'active text-blue-600' : '' }}">
        Blog
    </a>

    <a title="{{ $page->siteName }} Sobre" href="/sobre"
        class="ml-6 text-gray-700 hover:text-blue-600 {{ $page->isActive('/sobre') ? 'active text-blue-600' : '' }}">
        Sobre
    </a>

    <a title="{{ $page->siteName }} Contato" href="/contato"
        class="ml-6 text-gray-700 hover:text-blue-600 {{ $page->isActive('/contato') ? 'active text-blue-600' : '' }}">
        Contato
    </a>
</nav>
