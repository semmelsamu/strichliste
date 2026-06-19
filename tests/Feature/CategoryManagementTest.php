<?php

use App\Enums\UserRole;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('admins can list categories', function () {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory(['name' => 'Softdrinks']);

    $this->actingAs($admin)->get(route('categories.index'))
        ->assertSuccessful()
        ->assertViewIs('pages.categories.index')
        ->assertViewHas('categories', fn ($categories) => $categories->contains($category));
});

test('admins can create categories with lucide icons', function () {
    $admin = testUser([], UserRole::Admin);

    $this->actingAs($admin)->post(route('categories.store'), [
        'name' => 'Coffee',
        'icon' => 'coffee',
    ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('toast.type', 'success');

    $category = Category::where('name', 'Coffee')->firstOrFail();

    expect($category->icon)->toBe('lucide-coffee');
});

test('category creation validates name and icon existence', function (array $payload, array $errors) {
    $admin = testUser([], UserRole::Admin);

    $this->actingAs($admin)->post(route('categories.store'), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing name' => [['icon' => 'coffee'], ['name']],
    'missing icon' => [['name' => 'Coffee'], ['icon']],
    'unknown icon' => [['name' => 'Coffee', 'icon' => 'not-a-real-lucide-icon'], ['icon']],
]);

test('category update validates name and icon existence', function (array $payload, array $errors) {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory();
    $payload = array_replace([
        'name' => 'Valid Name',
        'icon' => 'coffee',
    ], $payload);

    $this->actingAs($admin)->patch(route('categories.update', $category), $payload)
        ->assertSessionHasErrors($errors);
})->with([
    'missing name' => [['name' => null], ['name']],
    'missing icon' => [['icon' => null], ['icon']],
    'unknown icon' => [['icon' => 'not-a-real-lucide-icon'], ['icon']],
]);

test('admins can edit and update categories', function () {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory(['name' => 'Old Name', 'icon' => 'lucide-circle']);

    $this->actingAs($admin)->get(route('categories.edit', $category))
        ->assertSuccessful()
        ->assertViewIs('pages.categories.edit')
        ->assertViewHas('category', fn ($viewCategory) => $viewCategory->icon === 'circle');

    $this->actingAs($admin)->patch(route('categories.update', $category), [
        'name' => 'New Name',
        'icon' => 'coffee',
    ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('toast.type', 'success');

    expect($category->fresh()->name)->toBe('New Name')
        ->and($category->fresh()->icon)->toBe('lucide-coffee');
});

test('admins can hide a category', function () {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory(['name' => 'Softdrinks']);

    $this->actingAs($admin)->patch(route('categories.update', $category), [
        'name' => 'Softdrinks',
        'icon' => 'coffee',
        'hidden' => '1',
    ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('toast.type', 'success');

    expect($category->fresh()->hidden)->toBeTrue();
});

test('admins can unhide a category', function () {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory(['name' => 'Softdrinks']);
    $category->hidden = true;
    $category->save();

    $this->actingAs($admin)->patch(route('categories.update', $category), [
        'name' => 'Softdrinks',
        'icon' => 'coffee',
    ])
        ->assertRedirect(route('categories.index'));

    expect($category->fresh()->hidden)->toBeFalse();
});

test('hidden categories are not displayed in the tally sheet', function () {
    $admin = testUser([], UserRole::Admin);
    $user = testUser([], UserRole::Customer);
    $visible = testCategory(['name' => 'Softdrinks']);
    $hidden = testCategory(['name' => 'Secret']);
    $hidden->hidden = true;
    $hidden->save();

    $this->actingAs($admin)->withSession(tallySheetSession($user))->get(route('tally-sheet.buy-overview'))
        ->assertSuccessful()
        ->assertViewHas('categories', fn ($categories) => $categories->contains($visible) && ! $categories->contains($hidden));
});

test('admins can delete unused categories', function () {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory();

    $this->actingAs($admin)->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('toast.type', 'success');

    expect(Category::whereKey($category->id)->exists())->toBeFalse();
});

test('admins cannot delete categories that are still used by articles', function () {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory();
    testArticle(['category_id' => $category->id]);

    $this->actingAs($admin)->delete(route('categories.destroy', $category))
        ->assertSessionHas('toast.type', 'error');

    expect(Category::whereKey($category->id)->exists())->toBeTrue();
});

test('admins can reorder categories', function () {
    $admin = testUser([], UserRole::Admin);
    $first = testCategory(['name' => 'Softdrinks']);
    $second = testCategory(['name' => 'Kaffee']);

    $this->actingAs($admin)->patch(route('categories.reorder'), [
        'order' => [
            $first->id => 2,
            $second->id => 1,
        ],
    ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('toast.type', 'success');

    expect($first->fresh()->order)->toBe(2)
        ->and($second->fresh()->order)->toBe(1);
});

test('the admin index lists categories sorted by order', function () {
    $admin = testUser([], UserRole::Admin);
    $second = testCategory(['name' => 'Softdrinks']);
    $second->order = 2;
    $second->save();
    $first = testCategory(['name' => 'Kaffee']);
    $first->order = 1;
    $first->save();

    $this->actingAs($admin)->get(route('categories.index'))
        ->assertSuccessful()
        ->assertViewHas('categories', fn ($categories) => $categories->pluck('id')->all() === [$first->id, $second->id]);
});

test('the tally sheet lists categories sorted by order', function () {
    $admin = testUser([], UserRole::Admin);
    $user = testUser([], UserRole::Customer);
    $second = testCategory(['name' => 'Softdrinks']);
    $second->order = 2;
    $second->save();
    $first = testCategory(['name' => 'Kaffee']);
    $first->order = 1;
    $first->save();

    $this->actingAs($admin)->withSession(tallySheetSession($user))->get(route('tally-sheet.buy-overview'))
        ->assertSuccessful()
        ->assertViewHas('categories', fn ($categories) => $categories->pluck('id')->all() === [$first->id, $second->id]);
});

test('categories with the same order are accepted and tiebroken by name', function () {
    $admin = testUser([], UserRole::Admin);
    $banana = testCategory(['name' => 'Banane']);
    $apple = testCategory(['name' => 'Apfel']);

    $this->actingAs($admin)->patch(route('categories.reorder'), [
        'order' => [
            $banana->id => 1,
            $apple->id => 1,
        ],
    ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('toast.type', 'success');

    expect($banana->fresh()->order)->toBe(1)
        ->and($apple->fresh()->order)->toBe(1);

    $this->actingAs($admin)->get(route('categories.index'))
        ->assertViewHas('categories', fn ($categories) => $categories->pluck('id')->all() === [$apple->id, $banana->id]);
});

test('reorder validates that positions are non-negative integers', function (array $payload) {
    $admin = testUser([], UserRole::Admin);
    $category = testCategory();

    $this->actingAs($admin)->patch(route('categories.reorder'), [
        'order' => [$category->id => $payload['value']],
    ])
        ->assertSessionHasErrors('order.'.$category->id);
})->with([
    'negative' => [['value' => -1]],
    'non-integer' => [['value' => 'abc']],
]);
