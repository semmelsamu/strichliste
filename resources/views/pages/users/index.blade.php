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
            <tr>
                <th>Typ</th>
                <th>Name</th>
                <th class="text-right">Guthaben</th>
            </tr>
            @foreach ($users as $user)
                <tr class="border-t border-text-secondary/40 *:py-4">
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
                    <td class="w-auto">{{ $user->name }}</td>
                    <td class="text-right">
                        <x-currency :amount="$user->balance" />
                    </td>
                    <td class="flex items-center justify-end">
                        <a href="{{ route("users.edit", $user->id) }}">
                            <x-lucide-square-pen />
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </x-wrapper>
</x-layouts.main>
