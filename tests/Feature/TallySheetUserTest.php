<?php

use App\Enums\UserRole;
use App\Models\Barcode;
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

test('new tally sheet users can register without setting a pin', function () {
    $admin = testUser([], UserRole::TallyHost);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->post(route('tally-sheet.users.store'), [
        'username' => 'no-pin-member',
    ])
        ->assertRedirectToRoute('tally-sheet.show-deposit')
        ->assertSessionHas('toast.type', 'success');

    $user = User::where('name', 'no-pin-member')->firstOrFail();

    expect($user->pin)->toBeNull();
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

test('tally sheet pin validation requires a pin', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => '1234'], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->post(route('tally-sheet.auth.validate-pin', $user), [
        'pin' => null,
    ])->assertSessionHasErrors('pin');
});

test('scanning a barcode linked to a user logs them in without a pin', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $user = testUser(['pin' => '1234'], UserRole::Customer);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $barcode = new Barcode;
    $barcode->barcode = 'user-login-barcode';
    $barcode->user_id = $user->id;
    $barcode->save();

    $this->actingAs($admin)->withSession(tallySheetRunningSession($world))->post(route('tally-sheet.auth.scan-barcode'), [
        'barcode' => 'user-login-barcode',
    ])
        ->assertRedirect(route('tally-sheet.buy-overview'))
        ->assertSessionHas('tally_sheet.user_id', $user->id);
});

test('scanning an unknown barcode keeps the user on the login screen', function () {
    $admin = testUser([], UserRole::TallyHost);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->post(route('tally-sheet.auth.scan-barcode'), [
        'barcode' => 'unknown-barcode',
    ])
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionHas('toast.type', 'error')
        ->assertSessionMissing('tally_sheet.user_id');
});

test('scanning a barcode linked to an article shows the article details page', function () {
    $admin = testUser([], UserRole::TallyHost);
    $article = testArticle();

    $barcode = new Barcode;
    $barcode->barcode = 'article-login-barcode';
    $barcode->article_id = $article->id;
    $barcode->save();

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->post(route('tally-sheet.auth.scan-barcode'), [
        'barcode' => 'article-login-barcode',
    ])
        ->assertRedirect(route('tally-sheet.article-details', $article))
        ->assertSessionMissing('tally_sheet.user_id');
});

test('the article details page shows the scanned article', function () {
    $admin = testUser([], UserRole::TallyHost);
    $article = testArticle();

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->get(route('tally-sheet.article-details', $article))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.article-details')
        ->assertViewHas('article', $article);
});

test('scanning a user barcode on the article details page buys the article without logging in', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $user = testUser([], UserRole::Customer);
    $article = testArticle(price: 1.20);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $barcode = new Barcode;
    $barcode->barcode = 'user-purchase-barcode';
    $barcode->user_id = $user->id;
    $barcode->save();

    $this->actingAs($admin)->withSession(tallySheetRunningSession($world, $vendor))->post(route('tally-sheet.article-details.buy-by-barcode', $article), [
        'barcode' => 'user-purchase-barcode',
    ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionMissing('tally_sheet.user_id');

    $purchase = Transaction::latest('id')->firstOrFail();

    expect($purchase->from_user_id)->toBe($user->id)
        ->and($purchase->to_user_id)->toBe($vendor->id)
        ->and((float) $purchase->amount)->toBe(1.20)
        ->and((float) $user->fresh()->balance)->toBe(3.80);
});

test('scanning a user barcode without enough balance logs the user in and sends them to deposit', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $user = testUser([], UserRole::Customer);
    $article = testArticle(price: 1.20);

    $barcode = new Barcode;
    $barcode->barcode = 'poor-user-barcode';
    $barcode->user_id = $user->id;
    $barcode->save();

    $this->actingAs($admin)->withSession(tallySheetRunningSession($world, $vendor))->post(route('tally-sheet.article-details.buy-by-barcode', $article), [
        'barcode' => 'poor-user-barcode',
    ])
        ->assertRedirect(route('tally-sheet.show-deposit'))
        ->assertSessionHas('toast.type', 'error')
        ->assertSessionHas('tally_sheet.user_id', $user->id);

    expect(Transaction::count())->toBe(0);
});

test('scanning an unknown barcode on the article details page shows an error and does not buy', function () {
    $admin = testUser([], UserRole::TallyHost);
    $article = testArticle(price: 1.20);

    $this->actingAs($admin)->withSession(tallySheetRunningSession())->post(route('tally-sheet.article-details.buy-by-barcode', $article), [
        'barcode' => 'unknown-barcode',
    ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'error')
        ->assertSessionMissing('tally_sheet.user_id');

    expect(Transaction::count())->toBe(0);
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

test('tally sheet username update validates username and uniqueness', function (array $payload, array $errors) {
    $admin = testUser([], UserRole::TallyHost);
    testUser(['name' => 'taken-name']);
    $user = testUser(['name' => 'old-name'], UserRole::Customer);
    $payload = array_replace(['username' => 'new-name'], $payload);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->put(route('tally-sheet.users.update'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing username' => [['username' => null], ['username']],
    'too short username' => [['username' => 'ab'], ['username']],
    'duplicate username' => [['username' => 'taken-name'], ['username']],
]);

test('tally sheet users can save and remove their pin', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => null], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->post(route('tally-sheet.users.update-pin'), [
        'pin' => '9876',
    ])
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    expect(Hash::check('9876', $user->fresh()->pin))->toBeTrue();

    $this->actingAs($admin)->withSession(tallySheetSession($user))->delete(route('tally-sheet.users.remove-pin'), [
        'pin' => '9876',
    ])
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    expect($user->fresh()->pin)->toBeNull();
});

test('tally sheet pin removal is rejected with an incorrect pin', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => '1234'], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->delete(route('tally-sheet.users.remove-pin'), [
        'pin' => '0000',
    ])
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'error');

    expect($user->fresh()->pin)->not->toBeNull();
});

test('tally sheet pin removal requires the pin', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => '1234'], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->delete(route('tally-sheet.users.remove-pin'))
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'error');

    expect($user->fresh()->pin)->not->toBeNull();
});

test('tally sheet pin update requires a pin', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => null], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->post(route('tally-sheet.users.update-pin'), [
        'pin' => null,
    ])->assertSessionHasErrors('pin');
});

test('tally sheet users can add and remove their barcodes', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser([], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->post(route('tally-sheet.users.add-barcode'), [
        'barcode' => '4012345678901',
    ])
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    $barcode = Barcode::where('barcode', '4012345678901')->firstOrFail();

    expect($barcode->user_id)->toBe($user->id);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->delete(route('tally-sheet.users.remove-barcode', $barcode))
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'success');

    expect(Barcode::whereKey($barcode->id)->exists())->toBeFalse();
});

test('tally sheet user barcodes must be unique', function () {
    $admin = testUser([], UserRole::TallyHost);
    $firstUser = testUser([], UserRole::Customer);
    $secondUser = testUser([], UserRole::Customer);

    $barcode = new Barcode;
    $barcode->barcode = 'duplicate-code';
    $barcode->user_id = $firstUser->id;
    $barcode->save();

    $this->actingAs($admin)->withSession(tallySheetSession($secondUser))->post(route('tally-sheet.users.add-barcode'), [
        'barcode' => 'duplicate-code',
    ])->assertSessionHasErrors('barcode');
});

test('tally sheet user barcodes are required', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser([], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->post(route('tally-sheet.users.add-barcode'), [
        'barcode' => null,
    ])->assertSessionHasErrors('barcode');
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

test('tally sheet users with a pin must confirm deactivation with their pin', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => '1234'], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->delete(route('tally-sheet.users.destroy'), [
        'pin' => '1234',
    ])
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionMissing('tally_sheet.user_id');

    expect($user->fresh()->trashed())->toBeTrue();
});

test('tally sheet account deactivation is rejected with an incorrect pin', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => '1234'], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->delete(route('tally-sheet.users.destroy'), [
        'pin' => '0000',
    ])
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'error');

    expect($user->fresh()->trashed())->toBeFalse();
});

test('tally sheet account deactivation requires a pin when one is set', function () {
    $admin = testUser([], UserRole::TallyHost);
    $user = testUser(['pin' => '1234'], UserRole::Customer);

    $this->actingAs($admin)->withSession(tallySheetSession($user))->delete(route('tally-sheet.users.destroy'))
        ->assertRedirect(route('tally-sheet.users.edit'))
        ->assertSessionHas('toast.type', 'error');

    expect($user->fresh()->trashed())->toBeFalse();
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
