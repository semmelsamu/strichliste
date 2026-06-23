<x-layout.main title="Nutzer">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('dashboard') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Nutzer</h1>
        <a
            class="button ml-auto bg-fsim-light"
            href="{{ route('users.create') }}"
        >
            <x-lucide-plus />
            Neuen Nutzer erstellen
        </a>
    </x-header>

    <main class="wrapper px-wrapper py-section">
        <livewire:livewire.users-table defer />
    </main>
</x-layout.main>
