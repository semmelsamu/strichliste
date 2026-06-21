<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortField = 'name';

    #[Url]
    public string $sortDirection = 'asc';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public bool $showTrashed = true;

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedShowTrashed(): void
    {
        $this->resetPage();
    }

    /**
     * The filtered, sorted and paginated users.
     *
     * Balance is a cached computed attribute on the model (see App\Models\User::balance),
     * not a database column, so when sorting by it we recreate the same sum in SQL via
     * subquery columns (received - sent) instead of sorting in PHP, which would only ever
     * sort the current page.
     *
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        $direction = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $query = User::query()
            ->with('roles')
            ->when($this->showTrashed, fn ($query) => $query->withTrashed())
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->roleFilter !== '', fn ($query) => $query->role($this->roleFilter));

        if ($this->sortField === 'balance') {
            $query
                ->withSum('receivedTransactions as received_sum', 'amount')
                ->withSum('sentTransactions as sent_sum', 'amount')
                ->orderByRaw('(COALESCE(received_sum, 0) - COALESCE(sent_sum, 0)) '.$direction);
        } else {
            $query->orderBy('name', $direction);
        }

        return $query->paginate(25);
    }

    /**
     * Role filter options keyed by role value, with an "all roles" entry first.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function roleOptions(): array
    {
        return collect(UserRole::cases())
            ->mapWithKeys(fn (UserRole $role) => [$role->value => $role->displayName()])
            ->prepend('Alle Rollen', '')
            ->all();
    }
};
?>

@placeholder
    <div class="grid place-items-center p-wrapper">
        <x-spinner />
    </div>
@endplaceholder

<div class="space-y-content">
    <div class="flex flex-wrap items-end gap-content">
        <div class="w-64">
            <x-input.text
                name="search"
                label="Suche"
                placeholder="Nach Name suchen …"
                wire:model.live.debounce.250ms="search"
            />
        </div>
        <div class="w-56">
            <x-input.select
                name="roleFilter"
                label="Rolle"
                :options="$this->roleOptions"
                wire:model.live="roleFilter"
            />
        </div>
        <div class="w-56">
            <x-input.checkbox
                name="showTrashed"
                label="Deaktivierte anzeigen"
                wire:model.live="showTrashed"
            />
        </div>
        <div wire:loading.flex class="ml-auto items-center">
            <x-spinner />
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>
                    <button
                        type="button"
                        wire:click="sortBy('name')"
                        class="inline-flex items-center gap-1"
                    >
                        Name
                        @if ($sortField === 'name')
                            @svg ('lucide-chevron-' . ($sortDirection === 'asc' ? 'up' : 'down'), 'size-4')
                        @else
                            @svg ('lucide-chevrons-up-down', 'size-4 text-text-secondary')
                        @endif
                    </button>
                </th>
                <th>Rollen</th>
                <th class="text-right">
                    <button
                        type="button"
                        wire:click="sortBy('balance')"
                        class="inline-flex items-center gap-1"
                    >
                        Guthaben
                        @if ($sortField === 'balance')
                            @svg ('lucide-chevron-' . ($sortDirection === 'asc' ? 'up' : 'down'), 'size-4')
                        @else
                            @svg ('lucide-chevrons-up-down', 'size-4 text-text-secondary')
                        @endif
                    </button>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->users as $user)
                <tr wire:key="user-{{ $user->id }}">
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
                        <a href="{{ route('users.edit', $user->id) }}">
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
            {{ $this->users->total() }} Nutzer gesamt.
        </caption>
    </table>

    {{ $this->users->links() }}
</div>
