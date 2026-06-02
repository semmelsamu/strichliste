<x-layouts.main title="Preisliste">
    <x-wrapper class="space-y-content">
        <header class="space-y-inline">
            <h1 class="text-center">Willkommen</h1>
            <p class="flex items-center justify-center gap-2 text-center text-text-secondary">
                Angemeldet als <em>{{ Auth::user()->name }}</em>
            </p>
        </header>

        <nav class="mx-auto max-w-sm space-y-inline">
            <a
                href="{{ route('tally-sheet.auth.list-users') }}"
                class="card flex items-center gap-4 p-inline"
            >
                <x-lucide-tally-5 />
                Strichliste
            </a>
            <a
                href="{{ route('article-list') }}"
                class="card flex items-center gap-4 p-inline"
            >
                <x-lucide-scroll-text />
                Preisliste
            </a>

            <hr />

            <a
                href="{{ route("articles.index") }}"
                class="card flex items-center gap-4 p-inline"
            >
                <x-lucide-square-pen />
                Artikel bearbeiten
            </a>

            <hr />

            <a class="card flex items-center gap-4 p-inline">
                <x-lucide-banknote />
                Kassen-Modus
            </a>

            <a class="card flex items-center gap-4 p-inline">
                <x-lucide-hand />
                Helfer-Modus
            </a>

            <hr />

            <a
                href="{{ route('logout') }}"
                class="card flex items-center gap-4 p-inline"
            >
                <x-lucide-log-out />
                Abmelden
            </a>
        </nav>
    </x-wrapper>
</x-layouts.main>
