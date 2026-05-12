<x-layouts.main :title="$title">
    {{ $slot }}

    <nav
        class="px-wrapper bg-fsim-medium *:py-inline mt-auto grid w-full grid-cols-3 *:flex *:h-full *:w-full *:justify-center"
    >
        <button onclick="history.back()">
            <x-lucide-chevron-left />
        </button>
        <a href="/">
            <x-lucide-home />
        </a>
    </nav>
</x-layouts.main>
