@use (Illuminate\Support\Facades\Auth)

<x-layout.main title="Anmelden">
    <x-header class="wrapper px-wrapper py-6">
        <h1>Anmelden</h1>
    </x-header>

    <main class="wrapper px-wrapper py-section">
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
    </main>
</x-layout.main>
