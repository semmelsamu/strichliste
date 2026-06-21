<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

pest()->use(RefreshDatabase::class);

test('admin user index renders the users table component', function () {
    $admin = testUser([], UserRole::Admin);

    $this->actingAs($admin)->get(route('users.index'))
        ->assertSuccessful()
        ->assertViewIs('pages.users.index')
        ->assertSeeLivewire('livewire.users-table');
});

test('admins can create users with a hashed password', function () {
    $admin = testUser([], UserRole::Admin);

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'username' => 'vendor-user',
        'password' => 'vendor-password',
    ]);

    $createdUser = User::where('name', 'vendor-user')->firstOrFail();

    $response
        ->assertRedirect(route('users.edit', $createdUser))
        ->assertSessionHas('toast.type', 'success');

    expect(Hash::check('vendor-password', $createdUser->password))->toBeTrue();
});

test('admin user creation validates required unique fields', function (array $payload, array $errors) {
    $admin = testUser([], UserRole::Admin);
    testUser(['name' => 'existing-user']);

    $this->actingAs($admin)->post(route('users.store'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing username' => [['password' => 'secret'], ['username']],
    'duplicate username' => [['username' => 'existing-user', 'password' => 'secret'], ['username']],
    'missing password' => [['username' => 'new-user'], ['password']],
]);

test('admins can update a users roles', function () {
    $admin = testUser([], UserRole::Admin);
    $user = testUser([], UserRole::Customer);

    $this->actingAs($admin)->put(route('users.update-roles', $user), [
        UserRole::Vendor->value => 'on',
    ])
        ->assertRedirect(route('users.edit', $user))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->hasRole(UserRole::Vendor))->toBeTrue()
        ->and($user->fresh()->hasRole(UserRole::Customer))->toBeFalse();
});

test('admins can update user names', function () {
    $admin = testUser([], UserRole::Admin);
    $user = testUser([], UserRole::Customer);

    $this->actingAs($admin)->patch(route('users.update', $user), [
        'username' => 'renamed-vendor',
    ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->name)->toBe('renamed-vendor');
});

test('admin user update requires a username', function () {
    $admin = testUser([], UserRole::Admin);
    $user = testUser([], UserRole::Customer);

    $this->actingAs($admin)->patch(route('users.update', $user), [
        'username' => null,
    ])->assertSessionHasErrors('username');
});

test('admins cannot rename a user to another users existing name', function () {
    $admin = testUser([], UserRole::Admin);
    $firstUser = testUser(['name' => 'first-user']);
    $secondUser = testUser(['name' => 'second-user']);

    $this->actingAs($admin)->patch(route('users.update', $secondUser), [
        'username' => $firstUser->name,
    ])
        ->assertSessionHas('toast.type', 'error');

    expect($secondUser->fresh()->name)->toBe('second-user');
});

test('admins can deactivate and restore users', function () {
    $admin = testUser([], UserRole::Admin);
    $user = testUser();

    $this->actingAs($admin)->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.edit', $user->id))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->trashed())->toBeTrue();

    $this->actingAs($admin)->post(route('users.restore', $user))
        ->assertRedirect(route('users.edit', $user->id))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->trashed())->toBeFalse();
});

test('admins can update a users password', function () {
    $admin = testUser([], UserRole::Admin);
    $user = testUser(['password' => Hash::make('old-password')]);

    $this->actingAs($admin)->put(route('users.update-password', $user), [
        'password' => 'new-password',
    ])
        ->assertRedirect(route('users.edit', $user))
        ->assertSessionHas('toast.type', 'success');

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('admin password update requires a password', function (array $payload) {
    $admin = testUser([], UserRole::Admin);
    $user = testUser(['password' => Hash::make('old-password')]);

    $this->actingAs($admin)->put(route('users.update-password', $user), $payload)
        ->assertSessionHasErrors('password');

    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
})->with([
    'missing password' => [[]],
    'non-string password' => [['password' => ['not-a-string']]],
]);

test('admins can remove a users pin', function () {
    $admin = testUser([], UserRole::Admin);
    $user = testUser(['pin' => '1234']);

    $this->actingAs($admin)->delete(route('users.remove-pin', $user))
        ->assertRedirect(route('users.edit', $user))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->pin)->toBeNull();
});
