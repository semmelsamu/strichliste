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
