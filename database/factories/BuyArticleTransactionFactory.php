<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\BuyArticleTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BuyArticleTransaction>
 */
class BuyArticleTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $article = Article::inRandomOrder()->first();

        $user = User::role(UserRole::World->value)->where([
            ['balance', '>=', $article->currentPrice],
        ])->inRandomOrder()->value('id');

        $vendor = User::role(UserRole::Vendor->value)->inRandomOrder()->value('id');

        return [
            'transaction_id' => Transaction::factory()->create([
                'amount' => $article->currentPrice,
                'from_user_id' => $user,
                'to_user_id' => $vendor,
            ]),
            'article_id' => $article->id,
        ];
    }
}
