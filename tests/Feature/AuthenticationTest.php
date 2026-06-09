<?php

use App\Enums\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

pest()->use(RefreshDatabase::class);

test('guests are redirected to the login page from protected pages', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('articles.index'))->assertRedirect(route('login'));
    $this->get(route('tally-sheet.auth.list-users'))->assertRedirect(route('login'));
});

test('guests can view the login form', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertViewIs('pages.login');
});

test('users can authenticate with their name and password', function () {
    $user = testUser([
        'password' => Hash::make('secret-password'),
        'type' => UserType::NormalUser,
    ]);

    $this->post(route('authenticate'), [
        'name' => $user->name,
        'password' => 'secret-password',
    ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('toast.type', 'success');

    $this->assertAuthenticatedAs($user);
});

test('users cannot authenticate with an invalid password', function () {
    $user = testUser([
        'password' => Hash::make('secret-password'),
    ]);

    $this->post(route('authenticate'), [
        'name' => $user->name,
        'password' => 'wrong-password',
    ])
        ->assertSessionHasErrors('name')
        ->assertSessionHasInput('name', $user->name);

    $this->assertGuest();
});

test('users can log out', function () {
    $user = testUser([
        'password' => Hash::make('secret-password'),
    ]);

    $this->actingAs($user)
        ->get(route('logout'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('toast.type', 'success');

    $this->assertGuest();
});
