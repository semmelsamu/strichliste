<x-layout.main title="Pin eingeben">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('tally-sheet.auth.list-users') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Pin eingeben</h1>
    </x-header>

    <main class="wrapper px-wrapper py-section">
        <x-form
            post="{{ route('tally-sheet.auth.validate-pin', $user) }}"
            class="mx-auto max-w-sm"
        >
            <div class="mx-auto flex flex-col items-center gap-2 text-center">
                <div
                    class="grid aspect-square w-16 place-items-center rounded-full bg-fsim-medium"
                >
                    <x-lucide-user />
                </div>
                {{ $user->name }}
            </div>

            <x-input.text
                name="pin"
                type="password"
                label="PIN"
                required
                autofocus
            />

            <x-input.submit class="ml-auto">Anmelden</x-input.submit>
        </x-form>
    </main>
</x-layout.main>
