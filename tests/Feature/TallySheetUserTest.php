<?php

use App\Enums\UserType;
use App\Models\Transaction;
use App\Models\User;
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

    $this->actingAs($admin)->post(route('tally-sheet.users.store'), [
        'username' => 'new-member',
        'pin' => '1234',
    ])
        ->assertRedirectToRoute('tally-sheet.deposit', ['user' => User::where('name', 'new-member')->value('id')])
        ->assertSessionHas('toast.type', 'success');

    $user = User::where('name', 'new-member')->firstOrFail();

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
        ->assertRedirect(route('tally-sheet.deposit', $userWithNoBalance));

    $this->actingAs($admin)->get(route('tally-sheet.auth.login', $userWithBalance))
        ->assertRedirect(route('tally-sheet.buy-overview', $userWithBalance));
});

test('users with a pin must enter it before reaching their start page', function () {
    $admin = testUser();
    $user = testUser(['pin' => '1234']);

    $this->actingAs($admin)->get(route('tally-sheet.auth.login', $user))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.auth.enter-pin')
        ->assertViewHas('user', $user);

    $this->actingAs($admin)->post(route('tally-sheet.auth.validate-pin', $user), [
        'user' => $user->id,
        'pin' => '1234',
    ])->assertRedirect(route('tally-sheet.deposit', $user));
});

test('an incorrect tally sheet pin is rejected', function () {
    $admin = testUser();
    $user = testUser(['pin' => '1234']);

    $this->actingAs($admin)->post(route('tally-sheet.auth.validate-pin', $user), [
        'user' => $user->id,
        'pin' => '0000',
    ])->assertSessionHasErrors('pin');
});

test('tally sheet users can update their username', function () {
    $admin = testUser();
    $user = testUser(['name' => 'old-name']);

    $this->actingAs($admin)->patch(route('tally-sheet.users.update', $user), [
        'username' => 'new-name',
    ])
        ->assertRedirect(route('tally-sheet.users.edit', $user))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->name)->toBe('new-name');
});

test('tally sheet users can save and remove their pin', function () {
    $admin = testUser();
    $user = testUser(['pin' => null]);

    $this->actingAs($admin)->post(route('tally-sheet.users.update-pin', $user), [
        'pin' => '9876',
    ])
        ->assertRedirect(route('tally-sheet.users.edit', $user))
        ->assertSessionHas('toast.type', 'success');

    expect(Hash::check('9876', $user->fresh()->pin))->toBeTrue();

    $this->actingAs($admin)->delete(route('tally-sheet.users.remove-pin', $user))
        ->assertRedirect(route('tally-sheet.users.edit', $user))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->pin)->toBeNull();
});

test('tally sheet users can deactivate their account', function () {
    $admin = testUser();
    $user = testUser();

    $this->actingAs($admin)->delete(route('tally-sheet.users.destroy', $user))
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->trashed())->toBeTrue();
});
