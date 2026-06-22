<?php

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Barcode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

pest()->use(RefreshDatabase::class);

/**
 * Build a real JPEG upload using Imagick (the GD-based UploadedFile::fake()->image()
 * is unavailable because GD is not loaded in this environment).
 */
function imageUpload(int $width = 1200, int $height = 1000): UploadedFile
{
    $imagick = new Imagick;
    $imagick->newImage($width, $height, new ImagickPixel('red'));
    $imagick->setImageFormat('jpeg');

    $path = tempnam(sys_get_temp_dir(), 'article-image').'.jpg';
    file_put_contents($path, $imagick->getImageBlob());

    return new UploadedFile($path, 'photo.jpg', 'image/jpeg', test: true);
}

test('admins can list active and archived articles', function () {
    $admin = testUser([], UserRole::Admin);
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
    $admin = testUser([], UserRole::Admin);
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
    $admin = testUser([], UserRole::Admin);
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
    'missing price' => [['price' => null], ['price']],
    'negative price' => [['price' => '-0.10'], ['price']],
    'too many price decimals' => [['price' => '1.234'], ['price']],
]);

test('article creation form shows inline validation errors', function () {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory();

    $this->actingAs($admin)
        ->from(route('articles.create'))
        ->post(route('articles.store'), [
            'name' => null,
            'category' => $category->id,
            'price' => '1.20',
        ])
        ->assertRedirect(route('articles.create'));

    $this->get(route('articles.create'))
        ->assertSee('Name muss ausgefüllt werden.');
});

test('article update validates name and category', function (array $payload, array $errors) {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory();
    $article = testArticle(['category_id' => $category->id]);
    $payload = array_replace([
        'name' => 'Valid Article',
        'category' => $category->id,
    ], $payload);

    $this->actingAs($admin)->patch(route('articles.update', $article), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing name' => [['name' => null], ['name']],
    'missing category' => [['category' => null], ['category']],
    'unknown category' => [['category' => 9999], ['category']],
]);

test('admins can update article details without changing its price history', function () {
    $admin = testUser([], UserRole::Admin);
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
    $admin = testUser([], UserRole::Admin);
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

test('article price update validates non-negative decimal price', function (array $payload, array $errors) {
    $admin = testUser([], UserRole::Admin);
    $article = testArticle();
    $payload = array_replace(['price' => '1.00'], $payload);

    $this->actingAs($admin)->post(route('articles.update-price', $article), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing price' => [['price' => null], ['price']],
    'negative price' => [['price' => '-0.10'], ['price']],
    'too many price decimals' => [['price' => '1.234'], ['price']],
]);

test('admins can archive and restore articles', function () {
    $admin = testUser([], UserRole::Admin);
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
    $admin = testUser([], UserRole::Admin);
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

test('adding a barcode already linked to another article reassigns it', function () {
    $admin = testUser([], UserRole::Admin);
    $firstArticle = testArticle();
    $secondArticle = testArticle();

    $barcode = new Barcode;
    $barcode->barcode = 'duplicate-code';
    $barcode->article_id = $firstArticle->id;
    $barcode->save();

    $this->actingAs($admin)->post(route('articles.add-barcode', $secondArticle), [
        'barcode' => 'duplicate-code',
    ])
        ->assertRedirect(route('articles.edit', $secondArticle->id))
        ->assertSessionHas('toast.type', 'success');

    expect($barcode->fresh()->article_id)->toBe($secondArticle->id);
});

test('adding a barcode already linked to a user reassigns it to the article', function () {
    $admin = testUser([], UserRole::Admin);
    $user = testUser([], UserRole::Customer);
    $article = testArticle();

    $barcode = new Barcode;
    $barcode->barcode = 'user-owned-code';
    $barcode->user_id = $user->id;
    $barcode->save();

    $this->actingAs($admin)->post(route('articles.add-barcode', $article), [
        'barcode' => 'user-owned-code',
    ])
        ->assertRedirect(route('articles.edit', $article->id))
        ->assertSessionHas('toast.type', 'success');

    expect($barcode->fresh())
        ->article_id->toBe($article->id)
        ->user_id->toBeNull();
});

test('article barcodes are required', function () {
    $admin = testUser([], UserRole::Admin);
    $article = testArticle();

    $this->actingAs($admin)->post(route('articles.add-barcode', $article), [
        'barcode' => null,
    ])->assertSessionHasErrors('barcode');
});

test('admins can update the sounds assigned to an article', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);
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

test('admins can upload an image for an article, scaled down to 800px jpeg', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);
    $article = testArticle();

    $this->actingAs($admin)->post(route('articles.update-image', $article), [
        'image' => imageUpload(1200, 1000),
    ])
        ->assertRedirect(route('articles.edit', $article->id))
        ->assertSessionHas('toast.type', 'success');

    expect($article->hasImage())->toBeTrue();

    $contents = Storage::disk('public')->get("article-images/{$article->id}.jpg");
    $stored = ImageManager::usingDriver(Driver::class)->decodeBinary($contents);

    expect(bin2hex(substr($contents, 0, 3)))->toBe('ffd8ff')
        ->and(max($stored->width(), $stored->height()))->toBe(800)
        ->and($stored->width())->toBe(800)
        ->and($stored->height())->toBe(667);
});

test('uploading a new image overwrites the existing one', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);
    $article = testArticle();

    $this->actingAs($admin)->post(route('articles.update-image', $article), [
        'image' => imageUpload(),
    ]);
    $this->actingAs($admin)->post(route('articles.update-image', $article), [
        'image' => imageUpload(),
    ]);

    expect(Storage::disk('public')->files('article-images'))
        ->toBe(["article-images/{$article->id}.jpg"]);
});

test('admins can delete an article image', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);
    $article = testArticle();
    Storage::disk('public')->put("article-images/{$article->id}.jpg", 'image');

    $this->actingAs($admin)->delete(route('articles.delete-image', $article))
        ->assertRedirect(route('articles.edit', $article->id))
        ->assertSessionHas('toast.type', 'success');

    expect($article->hasImage())->toBeFalse();
});

test('article image upload requires an image file', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);
    $article = testArticle();

    $this->actingAs($admin)->post(route('articles.update-image', $article), [
        'image' => UploadedFile::fake()->create('notes.txt', 10),
    ])->assertSessionHasErrors('image');

    expect($article->hasImage())->toBeFalse();
});
