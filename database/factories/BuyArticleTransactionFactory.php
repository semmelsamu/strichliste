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
        return [
            'transaction_id' => Transaction::factory(),
            'article_id' => Article::inRandomOrder()->value('id'),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (BuyArticleTransaction $transaction) {
            $article = Article::find($transaction->article->id);

            $transaction->transaction->update([
                'amount' => $article->currentPrice,
            ]);
        });
    }
}
