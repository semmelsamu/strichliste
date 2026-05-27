<x-layouts.main title="Registrieren">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('tally-sheet.login') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Registrieren</h1>
    </header>
    <x-wrapper>
        <form
            class="mx-auto flex max-w-sm flex-col gap-4"
            method="POST"
            action="{{ route('tally-sheet.register-action') }}"
        >
            @csrf

            <div class="flex flex-col gap-2">
                <label for="username">Nutzername</label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="text-input"
                    required
                    value="{{old('username')}}"
                />
            </div>

            <div class="flex flex-col gap-2">
                <label for="pin">PIN (Optional)</label>
                <input type="password" name="pin" id="pin" class="text-input" />
            </div>

            <br />

            <button type="submit" class="button ml-auto bg-fsim-light">
                Registrieren
            </button>
        </form>
    </x-wrapper>
</x-layouts.main>
