<?php

use App\Enums\UserRole;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

pest()->use(RefreshDatabase::class);

test('tally sheet user list groups only normal users by first letter', function () {
    $admin = testUser([], UserRole::TallyHost);
    $alice = testUser(['name' => 'Alice'], UserRole::Customer);
    $bob = testUser(['name' => 'Bob'], UserRole::Customer);
    $vendor = testUser(['name' => 'Vendor'], UserRole::Vendor);
    $world = testUser(['name' => 'World'], UserRole::World);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->get(route('tally-sheet.auth.list-users'))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.auth.login')
        ->assertViewHas('usersByLetter', function ($usersByLetter) use ($alice, $bob, $vendor, $world) {
            $allUsers = $usersByLetter->flatten();

            return $usersByLetter->get('A')->contains($alice)
                && $usersByLetter->get('B')->contains($bob)
                && ! $allUsers->contains($vendor)
                && ! $allUsers->contains($world);
        });
});

test('new tally sheet users can register and are redirected to deposit', function () {
    $admin = testUser([], UserRole::TallyHost);

    $response = $this->actingAs($admin)->withSession(tallySheetRunningSession())->post(route('tally-sheet.users.store'), [
        'username' => 'new-member',
        'pin' => '1234',
    ]);

    $user = User::where('name', 'new-member')->firstOrFail();

    $response
        ->assertRedirectToRoute('tally-sheet.show-deposit')
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionHas('tally_sheet.user_id', $user->id);

    expect($user->hasRole(UserRole::Customer))->toBeTrue()
        ->and(Hash::check('1234', $user->pin))->toBeTrue();
});

test('tally sheet registration validates username and unique names', function (array $payload, array $errors) {
    $admin = testUser([], UserRole::TallyHost);
    testUser(['name' => 'taken-name']);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->post(route('tally-sheet.users.store'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing username' => [['pin' => '1234'], ['username']],
    'too short username' => [['username' => 'ab', 'pin' => '1234'], ['username']],
    'duplicate username' => [['username' => 'taken-name', 'pin' => '1234'], ['username']],
]);

test('users without a pin go directly to their start page', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $userWithNoBalance = testUser(['pin' => null], UserRole::Customer);
    $userWithBalance = testUser(['pin' => null], UserRole::Customer);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $userWithBalance->id,
        'amount' => 5,
    ]);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->get(route('tally-sheet.auth.login', $userWithNoBalance))
        ->assertRedirect(route('tally-sheet.show-deposit'))
        ->assertSessionHas('tally_sheet.user_id', $userWithNoBalance->id);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->get(route('tally-sheet.auth.login', $userWithBalance))
        ->assertRedirect(route('tally-sheet.buy-overview'))
        ->assertSessionHas('tally_sheet.user_id', $userWithBalance->id);
});

test('users with a pin must enter it before reaching their start page', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => '1234'], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->get(route('tally-sheet.auth.login', $user))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.auth.enter-pin')
        ->assertViewHas('user', $user);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->post(route('tally-sheet.auth.validate-pin', $user), [
        'pin' => '1234',
    ])
        ->assertRedirect(route('tally-sheet.show-deposit'))
        ->assertSessionHas('tally_sheet.user_id', $user->id);
});

test('an incorrect tally sheet pin is rejected', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => '1234'], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->post(route('tally-sheet.auth.validate-pin', $user), [
        'pin' => '0000',
    ])->assertSessionHasErrors('pin');
});

test('tally sheet users can update their username', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['name' => 'old-name'], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->put(route('tally-sheet.users.update'), [
        'username' => 'new-name',
    ])
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->name)->toBe('new-name');
});

test('tally sheet users can save and remove their pin', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => null], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->post(route('tally-sheet.users.update-pin'), [
        'pin' => '9876',
    ])
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    expect(Hash::check('9876', $user->fresh()->pin))->toBeTrue();

    $this->actingAs($admin)->withSession(tallySheetSession($user))->delete(route('tally-sheet.users.remove-pin'))
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->pin)->toBeNull();
});

test('tally sheet users can deactivate their account', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser([], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->delete(route('tally-sheet.users.destroy'))
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionMissing('tally_sheet.user_id');

    expect($user->fresh()->trashed())->toBeTrue();
});

test('guarded tally sheet routes redirect to user list without a selected session user', function () {
    $admin = testUser([], UserRole::TallyHost);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->get(route('tally-sheet.deposit'))
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionMissing('toast');
});

test('guarded tally sheet routes reject deleted or non normal selected users', function (UserRole $role) {
    $admin = testUser([], UserRole::TallyHost);
    $selectedUser = testUser([], $role);

    if ($role === UserRole::Customer) {
        $selectedUser->delete();
    }

    $this->actingAs($admin)->withSession(tallySheetSession($selectedUser))->get(route('tally-sheet.deposit'))
        ->assertRedirect(route('tally-sheet.auth.list-users'));
})->with([
    'soft deleted normal user' => [UserRole::Customer],
    'vendor user' => [UserRole::Vendor],
    'world user' => [UserRole::World],
]);

test('tally sheet logout clears only selected tally user session', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser();

    $this->actingAs($admin)
        ->withSession(array_merge(tallySheetSession($user), ['kept' => 'value']))
        ->get(route('tally-sheet.auth.logout'))
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionMissing('tally_sheet.user_id')
        ->assertSessionHas('kept', 'value');

    $this->assertAuthenticatedAs($admin);
});
