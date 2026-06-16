@use (App\Enums\UserRole)

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

            <div class="flex gap-inline">
                <a
                    class="card flex-1 p-inline"
                    href="{{ route('tally-sheet.auth.list-users') }}"
                >
                    Strichliste
                </a>
                @if (tally_session()->isRunning())
                    <a
                        class="card bg-red-800 p-inline"
                        href="{{ route('tally-sheet.stop-session') }}"
                    >
                        <x-lucide-square />
                    </a>
                @endif
            </div>
            <a class="card p-inline" href="{{ route('article-list') }}">
                Preisliste
            </a>

            @if (auth()->user()->hasRole(UserRole::Admin->value))
                <p class="mt-content flex items-center gap-2 text-lg font-medium">
                    <x-lucide-pencil-ruler />
                    Administration
                </p>
                <a class="card p-inline" href="{{ route("articles.index") }}">
                    Artikel bearbeiten
                </a>
                <a class="card p-inline" href="{{ route("categories.index") }}">
                    Kategorien bearbeiten
                </a>
                <a class="card p-inline" href="{{ route("users.index") }}">
                    Nutzer bearbeiten
                </a>
                <a class="card p-inline" href="{{ route("sounds.index") }}">
                    Sounds bearbeiten
                </a>
            @endif

            @if (auth()->user()->hasRole(UserRole::Admin->value))
                <p class="mt-content flex items-center gap-2 text-lg font-medium">
                    <x-lucide-boxes />
                    Modi
                </p>
                <a class="card p-inline">Kassen-Modus</a>
                <a class="card p-inline">Helfer-Modus</a>
            @endif

            <p class="mt-content flex items-center gap-2 text-lg font-medium">
                <x-lucide-circle-user-round />
                Account
            </p>

            <a class="card p-inline">Einstellungen</a>
            <a class="card p-inline" href="{{ route('logout') }}">Abmelden</a>
        </nav>
    </x-wrapper>
</x-layouts.main>
