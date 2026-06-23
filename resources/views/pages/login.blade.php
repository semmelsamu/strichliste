@use (Illuminate\Support\Facades\Auth)

<x-layout.main title="Anmelden">
    <x-header class="bg-fsim-medium p-wrapper">
        <h1>Anmelden</h1>
    </x-header>

    <x-wrapper>
        <x-form post="{{ route('authenticate') }}" class="mx-auto max-w-sm">
            <x-input.text
                name="name"
                label="Nutzername"
                value="{{ old('name') }}"
                required
                autofocus
            />

            <x-input.text name="password" type="password" label="Passwort" />

            <x-input.checkbox name="remember" label="Angemeldet bleiben" />

            <x-input.submit class="ml-auto">Anmelden</x-input.submit>
        </x-form>
    </x-wrapper>
</x-layout.main>
