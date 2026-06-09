<?php

use App\Enums\UserType;
use App\Models\Transaction;
use App\Models\User;
use App\TallySheetSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

pest()->use(RefreshDatabase::class);

test('tally sheet user list groups only normal users by first letter', function () {
    $admin = testUser(['type' => UserType::Vendor]);
    $alice = testUser(['name' => 'Alice', 'type' => UserType::NormalUser]);
    $bob = testUser(['name' => 'Bob', 'type' => UserType::NormalUser]);
    testUser(['name' => 'Vendor', 'type' => UserType::Vendor]);
    testUser(['name' => 'World', 'type' => UserType::World]);

    $this->actingAs($admin)->get(route('tally-sheet.auth.list-users'))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.auth.login')
        ->assertViewHas('usersByLetter', function ($usersByLetter) use ($alice, $bob) {
            return $usersByLetter->keys()->all() === ['A', 'B']
                && $usersByLetter->get('A')->contains($alice)
                && $usersByLetter->get('B')->contains($bob);
        });
});

test('new tally sheet users can register and are redirected to deposit', function () {
    $admin = testUser();

    $response = $this->actingAs($admin)->post(route('tally-sheet.users.store'), [
        'username' => 'new-member',
        'pin' => '1234',
    ]);

    $user = User::where('name', 'new-member')->firstOrFail();

    $response
        ->assertRedirectToRoute('tally-sheet.deposit')
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionHas('tally_sheet.user_id', $user->id);

    expect($user->type)->toBe(UserType::NormalUser)
        ->and(Hash::check('1234', $user->pin))->toBeTrue();
});

test('tally sheet registration validates username and unique names', function (array $payload, array $errors) {
    $admin = testUser();
    testUser(['name' => 'taken-name']);

    $this->actingAs($admin)->post(route('tally-sheet.users.store'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing username' => [['pin' => '1234'], ['username']],
    'too short username' => [['username' => 'ab', 'pin' => '1234'], ['username']],
    'duplicate username' => [['username' => 'taken-name', 'pin' => '1234'], ['username']],
]);

test('users without a pin go directly to their start page', function () {
    $admin = testUser();
    $world = testUser(['type' => UserType::World]);
    $userWithNoBalance = testUser(['pin' => null]);
    $userWithBalance = testUser(['pin' => null]);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $userWithBalance->id,
        'amount' => 5,
    ]);

    $this->actingAs($admin)->get(route('tally-sheet.auth.login', $userWithNoBalance))
        ->assertRedirect(route('tally-sheet.deposit'))
        ->assertSessionHas('tally_sheet.user_id', $userWithNoBalance->id);

    $this->actingAs($admin)->get(route('tally-sheet.auth.login', $userWithBalance))
        ->assertRedirect(route('tally-sheet.buy-overview'))
        ->assertSessionHas('tally_sheet.user_id', $userWithBalance->id);
});

test('users with a pin must enter it before reaching their start page', function () {
    $admin = testUser();
    $user = testUser(['pin' => '1234']);

    $this->actingAs($admin)->get(route('tally-sheet.auth.login', $user))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.auth.enter-pin')
        ->assertViewHas('user', $user);

    $this->actingAs($admin)->post(route('tally-sheet.auth.validate-pin', $user), [
        'pin' => '1234',
    ])
        ->assertRedirect(route('tally-sheet.deposit'))
        ->assertSessionHas('tally_sheet.user_id', $user->id);
});

test('an incorrect tally sheet pin is rejected', function () {
    $admin = testUser();
    $user = testUser(['pin' => '1234']);

    $this->actingAs($admin)->post(route('tally-sheet.auth.validate-pin', $user), [
        'pin' => '0000',
    ])->assertSessionHasErrors('pin');
});

test('tally sheet users can update their username', function () {
    $admin = testUser();
    $user = testUser(['name' => 'old-name']);

    $this->actingAs($admin)->withSession(['tally_sheet.user_id' => $user->id])->put(route('tally-sheet.users.update'), [
        'username' => 'new-name',
    ])
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->name)->toBe('new-name');
});

test('tally sheet users can save and remove their pin', function () {
    $admin = testUser();
    $user = testUser(['pin' => null]);

    $this->actingAs($admin)->withSession(['tally_sheet.user_id' => $user->id])->post(route('tally-sheet.users.update-pin'), [
        'pin' => '9876',
    ])
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    expect(Hash::check('9876', $user->fresh()->pin))->toBeTrue();

    $this->actingAs($admin)->withSession(['tally_sheet.user_id' => $user->id])->delete(route('tally-sheet.users.remove-pin'))
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->pin)->toBeNull();
});

test('tally sheet users can deactivate their account', function () {
    $admin = testUser();
    $user = testUser();

    $this->actingAs($admin)->withSession(['tally_sheet.user_id' => $user->id])->delete(route('tally-sheet.users.destroy'))
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionMissing('tally_sheet.user_id');

    expect($user->fresh()->trashed())->toBeTrue();
});

test('guarded tally sheet routes redirect to user list without a selected session user', function () {
    $admin = testUser();

    $this->actingAs($admin)->get(route('tally-sheet.deposit'))
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionMissing('toast');
});

test('guarded tally sheet routes reject deleted or non normal selected users', function (array $attributes) {
    $admin = testUser();
    $selectedUser = testUser($attributes);

    if ($selectedUser->type === UserType::NormalUser) {
        $selectedUser->delete();
    }

    $this->actingAs($admin)->withSession(['tally_sheet.user_id' => $selectedUser->id])->get(route('tally-sheet.deposit'))
        ->assertRedirect(route('tally-sheet.auth.list-users'));
})->with([
    'soft deleted normal user' => [['type' => UserType::NormalUser]],
    'vendor user' => [['type' => UserType::Vendor]],
    'world user' => [['type' => UserType::World]],
]);

test('tally sheet logout clears only selected tally user session', function () {
    $admin = testUser();
    $user = testUser();

    $this->actingAs($admin)
        ->withSession(['tally_sheet.user_id' => $user->id, 'kept' => 'value'])
        ->get(route('tally-sheet.auth.logout'))
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionMissing('tally_sheet.user_id')
        ->assertSessionHas('kept', 'value');

    $this->assertAuthenticatedAs($admin);
});

test('tally sheet session refuses to select non normal users', function () {
    $vendor = testUser(['type' => UserType::Vendor]);

    app(TallySheetSession::class)->selectUser($vendor);
})->throws(InvalidArgumentException::class);
