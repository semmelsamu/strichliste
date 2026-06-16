<?php

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\ArticlePrice;
use App\Models\Category;
use App\Models\User;
use App\Services\TallySheetSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function testUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'name' => fake()->unique()->userName(),
    ], $attributes));
}

function testCategory(array $attributes = []): Category
{
    $category = new Category;
    $category->name = $attributes['name'] ?? fake()->unique()->word();
    $category->icon = $attributes['icon'] ?? 'lucide-circle';
    $category->save();

    return $category;
}

/**
 * @return array<string, int>
 */
function tallySheetRunningSession(?User $world = null, ?User $vendor = null): array
{
    $world ??= testUser(['type' => UserRole::World]);
    $vendor ??= testUser(['type' => UserRole::Vendor]);

    return [
        TallySheetSessionService::WORLD_SESSION_KEY => $world->id,
        TallySheetSessionService::VENDOR_SESSION_KEY => $vendor->id,
    ];
}

/**
 * @return array<string, int>
 */
function tallySheetSession(User $user, ?User $world = null, ?User $vendor = null): array
{
    return array_merge(tallySheetRunningSession($world, $vendor), [
        TallySheetSessionService::USER_SESSION_KEY => $user->id,
    ]);
}

function testArticle(array $attributes = [], ?float $price = 1.20): Article
{
    $article = new Article;
    $article->name = $attributes['name'] ?? fake()->unique()->word();
    $article->category_id = $attributes['category_id'] ?? testCategory()->id;
    $article->sounds = $attributes['sounds'] ?? null;
    $article->save();

    if ($price !== null) {
        $articlePrice = new ArticlePrice;
        $articlePrice->article_id = $article->id;
        $articlePrice->price = $price;
        $articlePrice->save();
    }

    return $article;
}
