<x-layouts.main :title="$title">
    {{ $slot }}

    <nav
        class="mt-auto grid w-full grid-cols-3 bg-fsim-medium px-wrapper *:flex *:h-full *:w-full *:justify-center *:py-inline"
    >
        <button onclick="history.back()">
            <x-lucide-chevron-left />
        </button>
        <a href="/">
            <x-lucide-home />
        </a>
    </nav>
</x-layouts.main>
