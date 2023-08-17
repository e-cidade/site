<nav id="js-nav-menu" class="w-auto px-2 pt-6 pb-2 bg-gray-200 shadow hidden lg:hidden">
    <ul class="my-0">
        <li class="pl-4">
            <a
                title="{{ $page->siteName }} Blog"
                href="/blog"
                class="block mt-0 mb-4 text-sm no-underline {{ $page->isActive('/blog') ? 'active text-blue-500' : 'text-gray-800 hover:text-blue-500' }}"
            >Blog</a>
        </li>
        <li class="pl-4">
            <a
                title="{{ $page->siteName }} Sobre"
                href="/sobre"
                class="block mt-0 mb-4 text-sm no-underline {{ $page->isActive('/sobre') ? 'active text-blue-500' : 'text-gray-800 hover:text-blue-500' }}"
            >Sobre</a>
        </li>
        <li class="pl-4">
            <a
                title="{{ $page->siteName }} Contato"
                href="/contato"
                class="block mt-0 mb-4 text-sm no-underline {{ $page->isActive('/contato') ? 'active text-blue-500' : 'text-gray-800 hover:text-blue-500' }}"
            >Contato</a>
        </li>
    </ul>
</nav>
