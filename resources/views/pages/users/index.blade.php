@use (App\Enums\UserType)

<x-layouts.main title="Nutzer">
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
                    <th>Typ</th>
                    <th>Name</th>
                    <th class="text-right">Guthaben</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td title="{{ $user->type }}">
                            @switch ($user->type)
                                @case (UserType::World)
                                    <x-lucide-globe />
                                    @break
                                @case (UserType::Vendor)
                                    <x-lucide-banknote />
                                    @break
                                @default
                                    <x-lucide-user />
                                    @break
                            @endswitch
                        </td>
                        <th
                            @class (["w-auto", "text-text-secondary font-normal" => $user->trashed()])
                        >
                            {{ $user->name }}
                        </th>
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
</x-layouts.main>
