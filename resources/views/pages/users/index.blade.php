@use (App\Enums\UserRole)

<x-layout.main title="Nutzer">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
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
    </header>
    <x-wrapper>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Rollen</th>
                    <th class="text-right">Guthaben</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <th
                            @class (["w-auto", "text-text-secondary font-normal" => $user->trashed()])
                        >
                            {{ $user->name }}
                        </th>
                        <td>
                            @foreach ($user->roles->map(fn ($role) => UserRole::tryFrom($role->name))->filter() as $role)
                                <div class="badge">
                                    @if ($role->icon())
                                        @svg ($role->icon())
                                    @endif
                                    {{ $role->displayName() }}
                                </div>
                            @endforeach
                        </td>
                        <td class="text-right">
                            <x-currency :amount="$user->balance" />
                        </td>
                        <td class="flex items-center justify-end">
                            <a href="{{ route("users.edit", $user->id) }}">
                                <x-lucide-square-pen />
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="4" class="text-center">
                            Keine Nutzer gefunden.
                        </th>
                    </tr>
                @endforelse
            </tbody>
            <caption>
                {{ sizeof($users) }} Nutzer gesamt.
            </caption>
        </table>
    </x-wrapper>
</x-layout.main>
