<?php

use App\Enums\UserRole;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('that when a user has 0,20 € and withdraws 0,20 €, it succeds', function () {
    $world = User::factory()->create(['type' => UserRole::World]);
    $vendor = User::factory()->create(['type' => UserRole::Vendor]);
    $user = User::factory()->create(['type' => UserRole::Customer]);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 0.2,
    ]);

    $this->actingAs($user)->withSession(tallySheetSession($user, $world, $vendor))->post(route('tally-sheet.deposit'), [
        'action' => 'withdraw',
        'world' => $world->id,
        'amount' => 0.2,
    ]);

    expect($user->fresh()->balance)->toBe(0);
});
