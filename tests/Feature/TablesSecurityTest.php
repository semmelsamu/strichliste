<?php

use App\Enums\UserRole;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

test('invalid roleFilter from the URL does not crash the users table', function () {
    $admin = testUser(['name' => 'The Admin'], UserRole::Admin);
    testUser(['name' => 'Someone']);

    $this->actingAs($admin);

    Livewire::test('livewire.users-table')
        ->set('roleFilter', 'not-a-real-role')
        ->assertOk()
        // An unknown role filter is ignored, so all users are still listed.
        ->assertSee('Someone');
});

test('client cannot inflate perPage to load everything at once', function () {
    $world = testUser([], UserRole::World);
    $user = testUser([], UserRole::Customer);

    collect(range(1, 120))->each(fn () => Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 1,
    ]));

    session(tallySheetSession($user, $world));

    expect(fn () => Livewire::test('livewire.transaction-history')->set('perPage', 1_000_000))
        ->toThrow(Exception::class, 'Cannot update locked property: [perPage]');
});

test('search escapes LIKE wildcards', function () {
    $admin = testUser(['name' => 'The Admin'], UserRole::Admin);
    testUser(['name' => 'Alice']);
    testUser(['name' => 'Bob']);
    testUser(['name' => '50% Off']);

    $this->actingAs($admin);

    // "%" is treated literally: it matches the name containing a percent sign
    // but not arbitrary other names.
    Livewire::test('livewire.users-table')
        ->set('search', '%')
        ->assertSee('50% Off')
        ->assertDontSee('Alice')
        ->assertDontSee('Bob');
});

test('sortDirection tampering is sanitized', function () {
    $admin = testUser(['name' => 'The Admin'], UserRole::Admin);
    testUser(['name' => 'Anna']);

    $this->actingAs($admin);

    Livewire::test('livewire.users-table')
        ->set('sortField', 'balance')
        ->set('sortDirection', 'asc); drop table users; --')
        ->assertOk();
});

test('non-admins are forbidden from the users table component', function () {
    $customer = testUser([], UserRole::Customer);

    $this->actingAs($customer);

    Livewire::test('livewire.users-table')->assertForbidden();
});

test('guests are forbidden from the users table component', function () {
    Livewire::test('livewire.users-table')->assertForbidden();
});
