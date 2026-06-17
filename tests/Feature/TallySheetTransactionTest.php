<?php

use App\Enums\UserRole;
use App\Models\BuyArticleTransaction;
use App\Models\Transaction;
use App\Models\UndoTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

pest()->use(RefreshDatabase::class);

function transactionUsers(): array
{
    return [
        'admin' => testUser([], UserRole::TallyHost),
        'world' => testUser([], UserRole::World),
        'vendor' => testUser([], UserRole::Vendor),
        'user' => testUser([], UserRole::Customer),
    ];
}

test('users can deposit money from a world account', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.deposit'), [
        'action' => 'deposit',
        'world' => $world->id,
        'amount' => '10.50',
    ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionHas('sound', 'spongebob-moneten');

    $transaction = Transaction::firstOrFail();

    expect($transaction->from_user_id)->toBe($world->id)
        ->and($transaction->to_user_id)->toBe($user->id)
        ->and((float) $transaction->amount)->toBe(10.50)
        ->and((float) $user->fresh()->balance)->toBe(10.50);
});

test('users can withdraw money without going below zero', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.deposit'), [
        'action' => 'withdraw',
        'world' => $world->id,
        'amount' => '3.50',
    ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionHas('sound', 'wobble');

    $withdrawal = Transaction::latest('id')->firstOrFail();

    expect((float) $withdrawal->amount)->toBe(-3.50)
        ->and((float) $user->fresh()->balance)->toBe(1.50);
});

test('users can withdraw their exact balance to zero', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 0.20,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.deposit'), [
        'action' => 'withdraw',
        'world' => $world->id,
        'amount' => '0.20',
    ])->assertRedirect();

    expect((float) $user->fresh()->balance)->toBe(0.0);
});

test('withdrawals cannot exceed the users balance', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 2,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.deposit'), [
        'action' => 'withdraw',
        'world' => $world->id,
        'amount' => '2.01',
    ])->assertSessionHasErrors('amount');

    expect(Transaction::count())->toBe(1);
});

test('users already in debt cannot withdraw more money', function () {
    ['admin' => $admin, 'world' => $world, 'vendor' => $vendor, 'user' => $user] = transactionUsers();

    Transaction::factory()->create([
        'from_user_id' => $user->id,
        'to_user_id' => $vendor->id,
        'amount' => 1,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.deposit'), [
        'action' => 'withdraw',
        'world' => $world->id,
        'amount' => '0.01',
    ])->assertSessionHasErrors('amount');

    expect(Transaction::count())->toBe(1);
});

test('deposit requests validate action and amount', function (array $payload, array $errors) {
    ['admin' => $admin, 'world' => $world, 'vendor' => $vendor, 'user' => $user] = transactionUsers();

    $payload = array_replace([
        'action' => 'deposit',
        'world' => $world->id,
        'amount' => '1.00',
    ], $payload);

    $payload = collect($payload)
        ->map(fn ($value) => $value instanceof Closure ? $value() : $value)
        ->all();

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.deposit'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing action' => [['action' => null], ['action']],
    'invalid action' => [['action' => 'refund'], ['action']],
    'missing amount' => [['amount' => null], ['amount']],
    'too many decimals' => [['amount' => '1.234'], ['amount']],
]);

test('users can buy articles when they have enough balance', function () {
    ['admin' => $admin, 'world' => $world, 'vendor' => $vendor, 'user' => $user] = transactionUsers();
    $article = testArticle(['name' => 'Club Mate', 'sounds' => ['kaching']], 1.20);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.buy'), [
        'vendor' => $vendor->id,
        'article' => $article->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionHas('sound', 'kaching');

    $purchase = Transaction::latest('id')->firstOrFail();

    expect($purchase->from_user_id)->toBe($user->id)
        ->and($purchase->to_user_id)->toBe($vendor->id)
        ->and((float) $purchase->amount)->toBe(1.20)
        ->and((float) $user->fresh()->balance)->toBe(3.80)
        ->and(BuyArticleTransaction::where('transaction_id', $purchase->id)->where('article_id', $article->id)->exists())->toBeTrue();
});

test('users cannot buy articles without enough balance', function () {
    ['admin' => $admin, 'vendor' => $vendor, 'user' => $user] = transactionUsers();
    $article = testArticle(price: 1.20);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.buy'), [
        'vendor' => $vendor->id,
        'article' => $article->id,
    ])->assertSessionHasErrors('article');

    expect(BuyArticleTransaction::count())->toBe(0)
        ->and(Transaction::count())->toBe(0);
});

test('article purchases validate article ids', function (array $payload, array $errors) {
    ['admin' => $admin, 'world' => $world, 'vendor' => $vendor, 'user' => $user] = transactionUsers();
    $article = testArticle(price: 1.20);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $payload = array_replace([
        'vendor' => $vendor->id,
        'article' => $article->id,
    ], $payload);

    $payload = collect($payload)
        ->map(fn ($value) => $value instanceof Closure ? $value() : $value)
        ->all();

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.buy'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing article' => [['article' => null], ['article']],
    'unknown article' => [['article' => 9999], ['article']],
]);

test('users can undo one of their recent transactions', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    $deposit = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.undo'), [
        'transaction' => $deposit->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success')
        ->assertSessionHas('sound', 'wobble');

    $undoingTransaction = Transaction::latest('id')->firstOrFail();

    expect((float) $undoingTransaction->amount)->toBe(-5.0)
        ->and($undoingTransaction->from_user_id)->toBe($world->id)
        ->and($undoingTransaction->to_user_id)->toBe($user->id)
        ->and((float) $user->fresh()->balance)->toBe(0.0)
        ->and(UndoTransaction::where('transaction_id', $undoingTransaction->id)->where('undone_transaction_id', $deposit->id)->exists())->toBeTrue();
});

test('transactions cannot be undone twice', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    $deposit = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.undo'), [
        'transaction' => $deposit->id,
    ])->assertRedirect();

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.undo'), [
        'transaction' => $deposit->id,
    ])->assertSessionHasErrors('transaction');
});

test('users cannot undo transactions that do not belong to them', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();
    $otherUser = testUser();

    $deposit = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $otherUser->id,
        'amount' => 5,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.undo'), [
        'transaction' => $deposit->id,
    ])->assertSessionHasErrors('transaction');
});

test('undo requests validate the transaction id', function (array $payload, array $errors) {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    $deposit = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $payload = array_replace(['transaction' => $deposit->id], $payload);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.undo'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing transaction' => [['transaction' => null], ['transaction']],
    'unknown transaction' => [['transaction' => 9999], ['transaction']],
]);

test('old transactions cannot be undone', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    $deposit = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
        'created_at' => Carbon::now()->subMinutes(6),
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.undo'), [
        'transaction' => $deposit->id,
    ])->assertSessionHasErrors('transaction');
});

test('history view returns normalized transactions newest first', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    $olderNegative = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => -2,
        'created_at' => Carbon::now()->subMinute(),
    ]);

    $newerPositive = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 3,
        'created_at' => Carbon::now(),
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->get(route('tally-sheet.history'))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.history')
        ->assertViewHas('normalizedTransactions', function ($transactions) use ($newerPositive, $olderNegative, $world, $user) {
            return $transactions->pluck('id')->all() === [$newerPositive->id, $olderNegative->id]
                && (float) $transactions->last()->amount === 2.0
                && $transactions->last()->fromUser->is($user)
                && $transactions->last()->toUser->is($world);
        });
});
