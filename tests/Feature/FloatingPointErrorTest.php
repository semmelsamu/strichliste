<?php

use App\Enums\UserRole;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('that when a user has 0,20 € and withdraws 0,20 €, it succeds', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $user = testUser([], UserRole::Customer);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 0.2,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world, $vendor))->post(route('tally-sheet.deposit'), [
        'action' => 'withdraw',
        'world' => $world->id,
        'amount' => 0.2,
    ]);

    expect($user->fresh()->balance)->toBe(0);
});
