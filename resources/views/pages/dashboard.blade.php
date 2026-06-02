<x-layouts.main title="Preisliste">
    <x-wrapper class="space-y-content">
        <header class="space-y-inline">
            <h1 class="text-center">Willkommen</h1>
            <p class="flex items-center justify-center gap-2 text-center text-text-secondary">
                Angemeldet als <em>{{ Auth::user()->name }}</em>
            </p>
        </header>

        <nav class="mx-auto max-w-sm space-y-inline">
            <p class="flex items-center gap-2 text-lg font-medium">
                <x-lucide-tally-5 />
                Hauptfunktionen
            </p>

            <a
                class="card p-inline"
                href="{{ route('tally-sheet.auth.list-users') }}"
            >
                Strichliste
            </a>
            <a class="card p-inline" href="{{ route('article-list') }}">
                Preisliste
            </a>

            <p class="mt-content flex items-center gap-2 text-lg font-medium">
                <x-lucide-pencil-ruler />
                Administration
            </p>

            <a class="card p-inline" href="{{ route("articles.index") }}">
                Artikel bearbeiten
            </a>
            <a class="card p-inline">Kategorien bearbeiten</a>
            <a class="card p-inline">Nutzer bearbeiten</a>

            <p class="mt-content flex items-center gap-2 text-lg font-medium">
                <x-lucide-boxes />
                Modi
            </p>

            <a class="card p-inline">Kassen-Modus</a>
            <a class="card p-inline">Helfer-Modus</a>

            <p class="mt-content flex items-center gap-2 text-lg font-medium">
                <x-lucide-circle-user-round />
                Account
            </p>

            <a class="card p-inline">Einstellungen</a>
            <a class="card p-inline" href="{{ route('logout') }}">Abmelden</a>
        </nav>
    </x-wrapper>
</x-layouts.main>
