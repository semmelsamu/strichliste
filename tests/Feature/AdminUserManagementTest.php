<?php

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

pest()->use(RefreshDatabase::class);

test('admin user index includes active and deactivated users', function () {
    $admin = testUser();
    $activeUser = testUser(['type' => UserType::NormalUser]);
    $deactivatedUser = testUser(['type' => UserType::Vendor]);
    $deactivatedUser->delete();

    $response = $this->actingAs($admin)->get(route('users.index'));

    $response
        ->assertSuccessful()
        ->assertViewIs('pages.users.index')
        ->assertViewHas('users', fn ($users) => $users->contains($activeUser) && $users->contains($deactivatedUser));
});

test('admins can create users with a hashed password and explicit type', function () {
    $admin = testUser();

    $this->actingAs($admin)->post(route('users.store'), [
        'username' => 'vendor-user',
        'password' => 'vendor-password',
        'type' => UserType::Vendor->value,
    ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('toast.type', 'success');

    $createdUser = User::where('name', 'vendor-user')->firstOrFail();

    expect($createdUser->type)->toBe(UserType::Vendor)
        ->and(Hash::check('vendor-password', $createdUser->password))->toBeTrue();
});

test('admin user creation validates required unique fields and enum type', function (array $payload, array $errors) {
    $admin = testUser();
    testUser(['name' => 'existing-user']);

    $this->actingAs($admin)->post(route('users.store'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing username' => [['password' => 'secret', 'type' => UserType::NormalUser->value], ['username']],
    'duplicate username' => [['username' => 'existing-user', 'password' => 'secret', 'type' => UserType::NormalUser->value], ['username']],
    'missing password' => [['username' => 'new-user', 'type' => UserType::NormalUser->value], ['password']],
    'invalid type' => [['username' => 'new-user', 'password' => 'secret', 'type' => 'admin'], ['type']],
]);

test('admins can update user names and types', function () {
    $admin = testUser();
    $user = testUser(['type' => UserType::NormalUser]);

    $this->actingAs($admin)->patch(route('users.update', $user), [
        'username' => 'renamed-vendor',
        'type' => UserType::Vendor->value,
    ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->name)->toBe('renamed-vendor')
        ->and($user->fresh()->type)->toBe(UserType::Vendor);
});

test('admins cannot rename a user to another users existing name', function () {
    $admin = testUser();
    $firstUser = testUser(['name' => 'first-user']);
    $secondUser = testUser(['name' => 'second-user']);

    $this->actingAs($admin)->patch(route('users.update', $secondUser), [
        'username' => $firstUser->name,
        'type' => UserType::NormalUser->value,
    ])
        ->assertSessionHas('toast.type', 'error');

    expect($secondUser->fresh()->name)->toBe('second-user');
});

test('admins can deactivate and restore users', function () {
    $admin = testUser();
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
    $admin = testUser();
    $user = testUser(['password' => Hash::make('old-password')]);

    $this->actingAs($admin)->put(route('users.update-password', $user), [
        'password' => 'new-password',
    ])
        ->assertRedirect(route('users.edit', $user))
        ->assertSessionHas('toast.type', 'success');

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('admins can remove a users pin', function () {
    $admin = testUser();
    $user = testUser(['pin' => '1234']);

    $this->actingAs($admin)->delete(route('users.remove-pin', $user))
        ->assertRedirect(route('users.edit', $user))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->pin)->toBeNull();
});
