<?php

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\ArticlePrice;
use App\Models\Barcode;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('that when a user has 0,20 € and withdraws 0,20 €, it succeds', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $user = testUser([], UserRole::Customer);

    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 0.2,
    ]);

    $this->actingAs($admin)->withSession(tallySheetSession($user, $world, $vendor))->post(route('tally-sheet.deposit'), [
        'action' => 'withdraw',
        'world' => $world->id,
        'amount' => 0.2,
    ]);

    expect($user->fresh()->balance)->toBe(0.0);
});

test('that a balance arising from subtraction is not corrupted by floating point error', function () {
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $user = testUser([], UserRole::Customer);

    // 3.00 deposited, 2.40 spent → a true balance of exactly 0.60.
    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 3.00,
    ]);
    Transaction::factory()->create([
        'from_user_id' => $user->id,
        'to_user_id' => $vendor->id,
        'amount' => 2.40,
    ]);

    expect($user->fresh()->balance)->toBe(0.6);
});

test('that a user with a balance of exactly the article price can buy it', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $user = testUser([], UserRole::Customer);

    // 5.00 deposited, 4.40 spent → a true balance of exactly 0.60, which naive
    // float subtraction renders as 0.5999999999999996 (just below 0.60).
    Transaction::factory()->create([
        'from_user_id' => $world->id,
        'to_user_id' => $user->id,
        'amount' => 5.00,
    ]);
    Transaction::factory()->create([
        'from_user_id' => $user->id,
        'to_user_id' => $vendor->id,
        'amount' => 4.40,
    ]);

    $category = new Category;
    $category->name = 'Drinks';
    $category->icon = 'cup-soda';
    $category->save();

    $article = new Article;
    $article->name = 'Kaffee';
    $article->category_id = $category->id;
    $article->save();

    $price = new ArticlePrice;
    $price->article_id = $article->id;
    $price->price = 0.60;
    $price->save();

    $barcode = new Barcode;
    $barcode->article_id = $article->id;
    $barcode->barcode = '1234567890';
    $barcode->save();

    $response = $this->actingAs($admin)->withSession(tallySheetSession($user, $world, $vendor))->post(route('tally-sheet.buy-by-barcode'), [
        'vendor' => $vendor->id,
        'barcode' => '1234567890',
    ]);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas(Transaction::class, [
        'from_user_id' => $user->id,
        'to_user_id' => $vendor->id,
        'amount' => 0.60,
    ]);
});
