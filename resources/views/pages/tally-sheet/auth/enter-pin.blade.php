<x-layouts.main title="Pin eingeben">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('tally-sheet.auth.list-users') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Pin eingeben</h1>
    </header>
    <x-wrapper>
        <form
            class="mx-auto flex max-w-sm flex-col gap-8"
            method="POST"
            action="{{ route('tally-sheet.auth.validate-pin', $user) }}"
        >
            @csrf

            <div class="flex flex-col items-center gap-2 text-center">
                <div
                    class="grid aspect-square w-16 place-items-center rounded-full bg-fsim-medium"
                >
                    <x-lucide-user />
                </div>
                {{ $user->name }}
            </div>

            <div class="flex flex-col gap-2">
                <label for="pin">PIN</label>
                <input
                    type="password"
                    name="pin"
                    id="pin"
                    class="text-input"
                    required
                    autofocus
                />
            </div>

            <button type="submit" class="button ml-auto bg-fsim-light">
                Anmelden
            </button>
        </form>
    </x-wrapper>
</x-layouts.main>
