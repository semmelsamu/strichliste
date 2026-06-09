<?php

use App\Models\Article;
use App\Models\Barcode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

test('admins can list active and archived articles', function () {
    $admin = testUser();
    $activeArticle = testArticle(['name' => 'Club Mate']);
    $archivedArticle = testArticle(['name' => 'Archived Mate']);
    $archivedArticle->delete();

    $this->actingAs($admin)->get(route('articles.index'))
        ->assertSuccessful()
        ->assertViewIs('pages.articles.index')
        ->assertViewHas('articles', fn ($articles) => $articles->contains($activeArticle) && ! $articles->contains($archivedArticle))
        ->assertViewHas('archivedArticles', fn ($articles) => $articles->contains($archivedArticle));
});

test('admins can create articles with an initial price', function () {
    $admin = testUser();
    $category = testCategory(['name' => 'Softdrinks']);

    $this->actingAs($admin)->post(route('articles.store'), [
        'name' => 'Paulaner Spezi',
        'category' => $category->id,
        'price' => '1.20',
    ])
        ->assertRedirect(route('articles.index'))
        ->assertSessionHas('toast.type', 'success');

    $article = Article::where('name', 'Paulaner Spezi')->firstOrFail();

    expect($article->category_id)->toBe($category->id)
        ->and((float) $article->currentPrice)->toBe(1.20);
});

test('article creation validates name category and non-negative decimal price', function (array $payload, array $errors) {
    $admin = testUser();
    $category = testCategory();
    $payload = array_replace([
        'name' => 'Valid Article',
        'category' => $category->id,
        'price' => '1.20',
    ], $payload);

    $this->actingAs($admin)->post(route('articles.store'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing name' => [['name' => null], ['name']],
    'missing category' => [['category' => null], ['category']],
    'unknown category' => [['category' => 9999], ['category']],
    'negative price' => [['price' => '-0.10'], ['price']],
    'too many price decimals' => [['price' => '1.234'], ['price']],
]);

test('admins can update article details without changing its price history', function () {
    $admin = testUser();
    $oldCategory = testCategory(['name' => 'Snacks']);
    $newCategory = testCategory(['name' => 'Coffee']);
    $article = testArticle(['category_id' => $oldCategory->id], 0.80);

    $this->actingAs($admin)->patch(route('articles.update', $article), [
        'name' => 'Filter Coffee',
        'category' => $newCategory->id,
    ])
        ->assertRedirect(route('articles.index'))
        ->assertSessionHas('toast.type', 'success');

    expect($article->fresh()->name)->toBe('Filter Coffee')
        ->and($article->fresh()->category_id)->toBe($newCategory->id)
        ->and($article->prices()->count())->toBe(1);
});

test('admins can append a new article price', function () {
    $admin = testUser();
    $article = testArticle(price: 0.80);
    $article->prices()->update([
        'effective_since' => now()->subMinute(),
    ]);

    $this->actingAs($admin)->post(route('articles.update-price', $article), [
        'price' => '1.00',
    ])
        ->assertRedirect(route('articles.edit', $article->id))
        ->assertSessionHas('toast.type', 'success');

    expect($article->prices()->count())->toBe(2)
        ->and((float) $article->fresh()->currentPrice)->toBe(1.00);
});

test('admins can archive and restore articles', function () {
    $admin = testUser();
    $article = testArticle();

    $this->actingAs($admin)->delete(route('articles.destroy', $article))
        ->assertRedirect(route('articles.index', $article->id))
        ->assertSessionHas('toast.type', 'success');

    expect($article->fresh()->trashed())->toBeTrue();

    $this->actingAs($admin)->post(route('articles.restore', $article))
        ->assertRedirect(route('articles.index', $article->id))
        ->assertSessionHas('toast.type', 'success');

    expect($article->fresh()->trashed())->toBeFalse();
});

test('admins can add and remove article barcodes', function () {
    $admin = testUser();
    $article = testArticle();

    $this->actingAs($admin)->post(route('articles.add-barcode', $article), [
        'barcode' => '4012345678901',
    ])
        ->assertRedirect(route('articles.edit', $article->id))
        ->assertSessionHas('toast.type', 'success');

    $barcode = Barcode::where('barcode', '4012345678901')->firstOrFail();

    expect($barcode->article_id)->toBe($article->id);

    $this->actingAs($admin)->delete(route('articles.remove-barcode', [$article, $barcode]))
        ->assertRedirect(route('articles.edit', $article->id))
        ->assertSessionHas('toast.type', 'success');

    expect(Barcode::whereKey($barcode->id)->exists())->toBeFalse();
});

test('article barcodes must be unique', function () {
    $admin = testUser();
    $firstArticle = testArticle();
    $secondArticle = testArticle();

    $barcode = new Barcode;
    $barcode->barcode = 'duplicate-code';
    $barcode->article_id = $firstArticle->id;
    $barcode->save();

    $this->actingAs($admin)->post(route('articles.add-barcode', $secondArticle), [
        'barcode' => 'duplicate-code',
    ])->assertSessionHasErrors('barcode');
});

test('admins can update the sounds assigned to an article', function () {
    Storage::fake('public');

    $admin = testUser();
    $article = testArticle();

    Storage::disk('public')->put('sounds/kaching.mp3', 'sound');
    Storage::disk('public')->put('sounds/wobble.mp3', 'sound');

    $this->actingAs($admin)->post(route('articles.update-sounds', $article), [
        'kaching' => 'on',
    ])
        ->assertRedirect(route('articles.edit', $article->id))
        ->assertSessionHas('toast.type', 'success');

    expect($article->fresh()->sounds)->toBe(['kaching']);
});
