@use (Illuminate\Support\Facades\Auth)

<x-layouts.main title="Anmelden">
    <header class="bg-fsim-medium p-wrapper">
        <h1>Anmelden</h1>
    </header>
    <x-wrapper>
        <form
            class="mx-auto flex max-w-sm flex-col gap-4"
            method="POST"
            action="{{ route('authenticate') }}"
        >
            @csrf

            <div class="flex flex-col gap-2">
                <label for="name">Nutzername</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="text-input"
                    required
                    autofocus
                    value="{{old('name')}}"
                />
            </div>

            <div class="flex flex-col gap-2">
                <label for="password">Passwort</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="text-input"
                />
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="remember"
                    id="remember"
                    class="checkbox"
                />
                <label for="remember">Angemeldet bleiben</label>
            </div>

            <br />

            <button type="submit" class="button ml-auto bg-fsim-light">
                Anmelden
            </button>
        </form>
    </x-wrapper>
</x-layouts.main>
