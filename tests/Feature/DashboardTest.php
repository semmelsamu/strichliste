<?php

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('tally hosts do not see the stop session button or the account settings link', function () {
    $tallyHost = testUser([], UserRole::TallyHost);

    $this->actingAs($tallyHost)
        ->withSession(tallySheetRunningSession())
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertDontSee(route('tally-sheet.stop-session'))
        ->assertDontSee('Einstellungen');
});

test('admins see the stop session button while a session is running and the account settings link', function () {
    $admin = testUser([], UserRole::Admin);

    $this->actingAs($admin)
        ->withSession(tallySheetRunningSession())
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee(route('tally-sheet.stop-session'))
        ->assertSee('Einstellungen');
});
