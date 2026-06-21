<?php

use App\Enums\UserRole;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

test('lists both active and deactivated users', function () {
    testUser(['name' => 'Active Person']);
    testUser(['name' => 'Deactivated Person'])->delete();

    Livewire::test('users.users-table')
        ->assertOk()
        ->assertSee('Active Person')
        ->assertSee('Deactivated Person');
});

test('search filters users by name', function () {
    testUser(['name' => 'Alice']);
    testUser(['name' => 'Bob']);

    Livewire::test('users.users-table')
        ->set('search', 'Ali')
        ->assertSee('Alice')
        ->assertDontSee('Bob');
});

test('searching resets the page back to one', function () {
    User::factory()->count(30)->create();

    Livewire::test('users.users-table')
        ->call('setPage', 2)
        ->set('search', 'a')
        ->assertSet('paginators.page', 1);
});

test('role filter narrows users by role', function () {
    testUser(['name' => 'The Admin'], UserRole::Admin);
    testUser(['name' => 'The Customer'], UserRole::Customer);

    Livewire::test('users.users-table')
        ->set('roleFilter', UserRole::Admin->value)
        ->assertSee('The Admin')
        ->assertDontSee('The Customer');
});

test('show trashed toggle hides deactivated users', function () {
    testUser(['name' => 'Gone User'])->delete();

    Livewire::test('users.users-table')
        ->assertSee('Gone User')
        ->set('showTrashed', false)
        ->assertDontSee('Gone User');
});

test('sorting by name toggles direction', function () {
    testUser(['name' => 'Anna']);
    testUser(['name' => 'Zoe']);

    Livewire::test('users.users-table')
        ->assertSet('sortField', 'name')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['Anna', 'Zoe'])
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Zoe', 'Anna']);
});

test('sorting by balance orders users independently of their name', function () {
    // Names are deliberately the reverse of balance order, so a passing
    // assertion can only come from sorting by balance, not by name.
    $rich = testUser(['name' => 'Aaron Rich']);
    $poor = testUser(['name' => 'Zoe Poor']);

    Transaction::factory()->create([
        'from_user_id' => $poor->id,
        'to_user_id' => $rich->id,
        'amount' => 100,
    ]);

    Livewire::test('users.users-table')
        ->call('sortBy', 'balance')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['Zoe Poor', 'Aaron Rich'])
        ->call('sortBy', 'balance')
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Aaron Rich', 'Zoe Poor']);
});

test('paginates users beyond the page size', function () {
    foreach (range(1, 30) as $number) {
        testUser(['name' => sprintf('User %02d', $number)]);
    }

    Livewire::test('users.users-table')
        ->assertSee('User 01')
        ->assertDontSee('User 30')
        ->call('setPage', 2)
        ->assertSee('User 30')
        ->assertDontSee('User 01');
});
