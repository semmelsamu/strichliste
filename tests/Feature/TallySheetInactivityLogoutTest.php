<?php

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('logged-in customer views render the inactivity logout component', function (string $route) {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser([], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->get(route($route))
        ->assertSuccessful()
        ->assertSee(
            "inactivityTimeout('".route('tally-sheet.auth.logout')."')",
            false
        );
})->with([
    'buy overview' => ['tally-sheet.buy-overview'],
    'deposit' => ['tally-sheet.show-deposit'],
    'history' => ['tally-sheet.history'],
    'user settings' => ['tally-sheet.users.edit'],
]);

test('the login screen does not render the inactivity logout component', function () {
    $admin = testUser([], UserRole::TallyHost);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->get(route('tally-sheet.auth.list-users'))
        ->assertSuccessful()
        ->assertDontSee('inactivityTimeout');
});
