<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\BuyArticleTransaction;
use App\Models\Transaction;
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

        return [
            'transaction_id' => Transaction::factory()->create([
                'amount' => $article->currentPrice,
            ]),
            'article_id' => $article->id,
        ];
    }
}
