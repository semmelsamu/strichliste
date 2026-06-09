<?php

use App\Enums\UserType;
use App\Models\Article;
use App\Models\ArticlePrice;
use App\Models\Barcode;
use App\Models\BuyArticleTransaction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('a user can buy an article by scanning its barcode', function () {
    [$user, $vendor, $article] = createBarcodePurchaseFixtures(userBalance: 5.00, articlePrice: 1.50);

    $response = $this->actingAs($user)->post(route('tally-sheet.buy-by-barcode', $user), [
        'vendor' => $vendor->id,
        'barcode' => '1234567890',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $transaction = Transaction::query()
        ->where('from_user_id', $user->id)
        ->where('to_user_id', $vendor->id)
        ->where('amount', 1.50)
        ->first();

    expect($transaction)->not->toBeNull();
    expect($user->fresh()->balance)->toBe(3.5);

    $this->assertDatabaseHas(BuyArticleTransaction::class, [
        'transaction_id' => $transaction->id,
        'article_id' => $article->id,
    ]);
});

test('a user cannot buy an article by barcode without enough balance', function () {
    [$user, $vendor] = createBarcodePurchaseFixtures(userBalance: 1.00, articlePrice: 1.50);

    $response = $this->actingAs($user)->post(route('tally-sheet.buy-by-barcode', $user), [
        'vendor' => $vendor->id,
        'barcode' => '1234567890',
    ]);

    $response->assertSessionHasErrors(['barcode' => 'Nicht genügend Guthaben.']);

    $this->assertDatabaseMissing(Transaction::class, [
        'from_user_id' => $user->id,
        'to_user_id' => $vendor->id,
        'amount' => 1.50,
    ]);
});

/**
 * @return array{0: User, 1: User, 2: Article}
 */
function createBarcodePurchaseFixtures(float $userBalance, float $articlePrice): array
{
    $world = User::factory()->create(['type' => UserType::World]);
    $vendor = User::factory()->create(['type' => UserType::Vendor]);
    $user = User::factory()->create(['type' => UserType::NormalUser]);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => $userBalance,
    ]);

    $category = new Category;
    $category->name = 'Drinks';
    $category->icon = 'cup-soda';
    $category->save();

    $article = new Article;
    $article->name = 'Mate';
    $article->category_id = $category->id;
    $article->save();

    $price = new ArticlePrice;
    $price->article_id = $article->id;
    $price->price = $articlePrice;
    $price->save();

    $barcode = new Barcode;
    $barcode->article_id = $article->id;
    $barcode->barcode = '1234567890';
    $barcode->save();

    return [$user, $vendor, $article];
}
