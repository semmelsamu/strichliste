<x-layouts.main :title="$title">

    {{ $slot }}

    <nav class="w-full px-wrapper bg-fsim-medium mt-auto grid grid-cols-3 *:w-full *:h-full *:flex *:justify-center *:py-inline">
        <button onclick="history.back();">
            <x-lucide-chevron-left />
        </button>
        <a href="/">
            <x-lucide-home />
        </a>
    </nav>

</x-layouts.main>
