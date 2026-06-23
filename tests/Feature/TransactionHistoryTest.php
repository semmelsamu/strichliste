<?php

use App\Enums\UserRole;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

test('infinite scroll loads transactions 50 at a time', function () {
    $world = testUser([], UserRole::World);
    $user = testUser([], UserRole::Customer);

    // 60 transactions; the lower the index, the older the transaction.
    $transactions = collect(range(1, 60))->map(fn (int $i) => Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 1,
        'created_at' => Carbon::now()->subMinutes(60 - $i),
    ]));

    $oldest = $transactions->first();

    session(tallySheetSession($user, $world));

    Livewire::test('livewire.transaction-history')
        ->assertSet('perPage', 50)
        ->assertSee('60 Transaktionen gesamt.')
        // The oldest transaction is beyond the first page and the infinite-scroll
        // sentinel is still present. The trailing quote anchors the match so that
        // e.g. "transaction-1" does not match "transaction-15".
        ->assertDontSee('transaction-'.$oldest->id.'"', false)
        ->assertSee('wire:intersect', false)
        ->call('loadMore')
        ->assertSet('perPage', 100)
        ->assertSee('transaction-'.$oldest->id.'"', false)
        // Everything is loaded now, so the sentinel disappears.
        ->assertDontSee('wire:intersect', false);
});

test('shows an empty state when the user has no transactions', function () {
    $user = testUser([], UserRole::Customer);

    session(tallySheetSession($user));

    Livewire::test('livewire.transaction-history')
        ->assertSee('Keine Transaktionen gefunden.')
        ->assertSee('0 Transaktionen gesamt.');
});

test('the undo form is only rendered for transactions within the undo window', function () {
    $world = testUser([], UserRole::World);
    $user = testUser([], UserRole::Customer);

    $recent = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 1,
        'created_at' => Carbon::now()->subMinute(),
    ]);

    $old = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 1,
        'created_at' => Carbon::now()->subMinutes(10),
    ]);

    session(tallySheetSession($user, $world));

    // The hidden input's value attribute (rendered only inside the undo form)
    // marks which transactions are still undoable.
    Livewire::test('livewire.transaction-history')
        ->assertSee('value="'.$recent->id.'"', false)
        ->assertDontSee('value="'.$old->id.'"', false);
});
