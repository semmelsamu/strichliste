<?php

use App\Enums\SystemSound;
use App\Enums\UserRole;
use App\Models\BuyArticleTransaction;
use App\Models\SystemSoundSetting;
use App\Models\Transaction;
use App\Models\UndoTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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

function assignSystemSound(SystemSound $systemSound, string $name): void
{
    Storage::disk('public')->put("sounds/{$name}.mp3", 'sound');
    SystemSoundSetting::create([
        'system_sound' => $systemSound,
        'sound' => $name,
    ]);
}

test('no sound is played when no system sound is configured for an action', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.deposit'), [
        'action' => 'deposit',
        'world' => $world->id,
        'amount' => '10.50',
    ])
        ->assertRedirect()
        ->assertSessionMissing('sound');
});

test('users can deposit money from a world account', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    Storage::fake('public');
    assignSystemSound(SystemSound::Deposit, 'spongebob-moneten');

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

    Storage::fake('public');
    assignSystemSound(SystemSound::Withdraw, 'wobble');

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

test('users can send money to another user', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();
    $recipient = testUser([], UserRole::Customer);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.transfer'), [
        'recipient' => $recipient->id,
        'amount' => '3.50',
    ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success');

    $transfer = Transaction::latest('id')->firstOrFail();

    expect($transfer->from_user_id)->toBe($user->id)
        ->and($transfer->to_user_id)->toBe($recipient->id)
        ->and((float) $transfer->amount)->toBe(3.50)
        ->and((float) $user->fresh()->balance)->toBe(1.50)
        ->and((float) $recipient->fresh()->balance)->toBe(3.50);
});

test('transfers cannot exceed the senders balance', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();
    $recipient = testUser([], UserRole::Customer);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 2,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.transfer'), [
        'recipient' => $recipient->id,
        'amount' => '2.01',
    ])->assertSessionHasErrors('amount');

    expect(Transaction::count())->toBe(1);
});

test('users cannot send money to themselves', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.transfer'), [
        'recipient' => $user->id,
        'amount' => '1.00',
    ])->assertSessionHasErrors('recipient');

    expect(Transaction::count())->toBe(1);
});

test('transfer requests validate recipient and amount', function (array $payload, array $errors) {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();
    $recipient = testUser([], UserRole::Customer);
    $vendor = $vendor ?? testUser([], UserRole::Vendor);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $payload = array_replace([
        'recipient' => $recipient->id,
        'amount' => '1.00',
    ], $payload);

    $payload = collect($payload)
        ->map(fn ($value) => $value instanceof Closure ? $value() : $value)
        ->all();

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))->post(route('tally-sheet.transfer'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing recipient' => [['recipient' => null], ['recipient']],
    'unknown recipient' => [['recipient' => 9999], ['recipient']],
    'recipient is not a customer' => [['recipient' => fn () => testUser([], UserRole::Vendor)->id], ['recipient']],
    'missing amount' => [['amount' => null], ['amount']],
    'zero amount' => [['amount' => '0'], ['amount']],
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

    Storage::fake('public');
    assignSystemSound(SystemSound::UndoTransaction, 'wobble');

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

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world ?? null, $vendor ?? null))
        ->withHeader('HX-Request', 'true')
        ->get(route('tally-sheet.history'))
        ->assertSuccessful()
        ->assertSeeInOrder([
            "transaction-{$newerPositive->id}",
            "transaction-{$olderNegative->id}",
        ], false)
        // The negative transaction is normalized: from/to users are swapped,
        // so it reads as money paid out to the world user.
        ->assertSee("Geld bei {$world->name} ausgezahlt");
});

test('history view lazily loads transactions and only queries them for htmx requests', function () {
    ['admin' => $admin, 'world' => $world, 'user' => $user] = transactionUsers();

    $transaction = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
        'created_at' => Carbon::now(),
    ]);

    $session = tallySheetSession($user, $world, null);

    // Initial (non-HTMX) page load renders only the shell: a spinner placeholder
    // with the lazy-loading trigger, and none of the transaction data.
    $this->actingAs($admin)->withSession($session)->get(route('tally-sheet.history'))
        ->assertSuccessful()
        ->assertSee('hx-get', false)
        ->assertSee('animate-spin', false)
        ->assertDontSee('Transaktionen gesamt.')
        ->assertDontSee("transaction-{$transaction->id}", false);

    // The HTMX fragment request renders the transactions, without the shell wrapper.
    $this->actingAs($admin)->withSession($session)
        ->withHeader('HX-Request', 'true')
        ->get(route('tally-sheet.history'))
        ->assertSuccessful()
        ->assertSee('1 Transaktionen gesamt.')
        ->assertSee("transaction-{$transaction->id}", false)
        ->assertDontSee('hx-get', false);
});
