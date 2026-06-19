<?php

use App\Enums\UserRole;
use App\Models\ArticlePrice;
use App\Models\Barcode;
use App\Models\BuyArticleTransaction;
use App\Models\Transaction;
use App\Models\UndoTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

pest()->use(RefreshDatabase::class);

test('user balances are incoming transactions minus outgoing transactions', function () {
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $user = testUser([], UserRole::Customer);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 10,
    ]);

    Transaction::factory()->create([
        'from_user_id' => $user->id,
        'to_user_id' => $vendor->id,
        'amount' => 3.25,
    ]);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => -1.50,
    ]);

    expect((float) $user->fresh()->balance)->toBe(5.25)
        ->and((float) $world->fresh()->balance)->toBe(-8.50)
        ->and((float) $vendor->fresh()->balance)->toBe(3.25);
});

test('user balance is memoized on the instance within a request', function () {
    $world = testUser([], UserRole::World);
    $user = testUser([], UserRole::Customer);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 10,
    ]);

    expect((float) $user->balance)->toBe(10.0);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    expect((float) $user->balance)->toBe(10.0)
        ->and((float) $user->fresh()->balance)->toBe(15.0);
});

test('article current price is memoized on the instance within a request', function () {
    $article = testArticle(price: null);

    $price = new ArticlePrice;
    $price->article_id = $article->id;
    $price->price = 0.80;
    $price->effective_since = Carbon::now()->subDay();
    $price->save();

    expect((float) $article->currentPrice)->toBe(0.80);

    $newPrice = new ArticlePrice;
    $newPrice->article_id = $article->id;
    $newPrice->price = 1.20;
    $newPrice->effective_since = Carbon::now();
    $newPrice->save();

    expect((float) $article->currentPrice)->toBe(0.80)
        ->and((float) $article->fresh()->currentPrice)->toBe(1.20);
});

test('a users transactions include sent and received transactions only', function () {
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $user = testUser([], UserRole::Customer);
    $otherUser = testUser([], UserRole::Customer);

    $received = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
    ]);
    $sent = Transaction::factory()->create([
        'from_user_id' => $user->id,
        'to_user_id' => $vendor->id,
    ]);
    $unrelated = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $otherUser->id,
    ]);

    expect($user->transactions()->pluck('id')->all())->toContain($received->id, $sent->id)
        ->and($user->transactions()->pluck('id')->all())->not->toContain($unrelated->id);
});

test('users are grouped alphabetically by first letter with non letters at the end', function () {
    $anna = new User(['name' => 'anna']);
    $bob = new User(['name' => 'Bob']);
    $number = new User(['name' => '1st User']);
    $underscore = new User(['name' => '_robot']);

    $grouped = User::groupByFirstLetter(collect([$number, $bob, $anna, $underscore]));

    expect($grouped->keys()->all())->toBe(['A', 'B', '*'])
        ->and($grouped->get('A')->first())->toBe($anna)
        ->and($grouped->get('B')->first())->toBe($bob)
        ->and($grouped->get('*')->all())->toBe([$number, $underscore]);
});

test('article current price is the latest effective price', function () {
    $article = testArticle(price: null);

    $oldPrice = new ArticlePrice;
    $oldPrice->article_id = $article->id;
    $oldPrice->price = 0.80;
    $oldPrice->effective_since = Carbon::now()->subDay();
    $oldPrice->save();

    $newPrice = new ArticlePrice;
    $newPrice->article_id = $article->id;
    $newPrice->price = 1.20;
    $newPrice->effective_since = Carbon::now();
    $newPrice->save();

    expect((float) $article->fresh()->currentPrice)->toBe(1.20);
});

test('negative transactions are normalized by swapping users and making the amount positive', function () {
    $world = testUser([], UserRole::World);
    $user = testUser([], UserRole::Customer);

    $transaction = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => -2.50,
    ]);

    $normalized = Transaction::normalize($transaction);

    expect((float) $normalized->amount)->toBe(2.50)
        ->and($normalized->fromUser->is($user))->toBeTrue()
        ->and($normalized->toUser->is($world))->toBeTrue()
        ->and((float) $transaction->amount)->toBe(-2.50);
});

test('positive transactions normalize to the same transaction instance', function () {
    $world = testUser([], UserRole::World);
    $user = testUser([], UserRole::Customer);

    $transaction = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 2.50,
    ]);

    expect(Transaction::normalize($transaction))->toBe($transaction);
});

test('buy article transaction keeps referencing archived articles', function () {
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $user = testUser([], UserRole::Customer);
    $article = testArticle();

    $transaction = Transaction::factory()->create([
        'from_user_id' => $user->id,
        'to_user_id' => $vendor->id,
        'amount' => $article->currentPrice,
    ]);

    $buyArticleTransaction = new BuyArticleTransaction;
    $buyArticleTransaction->transaction_id = $transaction->id;
    $buyArticleTransaction->article_id = $article->id;
    $buyArticleTransaction->save();

    $article->delete();

    expect($buyArticleTransaction->fresh()->article->is($article))->toBeTrue();
});

test('undo transaction relations identify both the undoing and undone transactions', function () {
    $world = testUser([], UserRole::World);
    $user = testUser([], UserRole::Customer);

    $original = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5,
    ]);

    $undoing = Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => -5,
    ]);

    $undoTransaction = new UndoTransaction;
    $undoTransaction->transaction_id = $undoing->id;
    $undoTransaction->undone_transaction_id = $original->id;
    $undoTransaction->save();

    expect($undoing->fresh()->undoTransaction->is($undoTransaction))->toBeTrue()
        ->and($original->fresh()->undone->is($undoTransaction))->toBeTrue()
        ->and($undoTransaction->fresh()->undoneTransaction->is($original))->toBeTrue();
});

test('a barcode is deleted once it is detached from both its article and its user', function () {
    $article = testArticle();

    $barcode = new Barcode;
    $barcode->barcode = 'detach-me';
    $barcode->article_id = $article->id;
    $barcode->save();

    $barcode->article()->dissociate();
    $barcode->save();

    expect(Barcode::whereKey($barcode->id)->exists())->toBeFalse();
});

test('a barcode linked to a user survives while the user link remains', function () {
    $user = testUser();

    $barcode = new Barcode;
    $barcode->barcode = 'user-barcode';
    $barcode->user_id = $user->id;
    $barcode->save();

    expect($barcode->fresh()->user->is($user))->toBeTrue();

    $barcode->touch();

    expect(Barcode::whereKey($barcode->id)->exists())->toBeTrue();
});

test('a barcode cannot be linked to both an article and a user', function () {
    $article = testArticle();
    $user = testUser();

    $barcode = new Barcode;
    $barcode->barcode = 'conflicting-barcode';
    $barcode->article_id = $article->id;
    $barcode->user_id = $user->id;

    expect(fn () => $barcode->save())->toThrow(LogicException::class);
});
