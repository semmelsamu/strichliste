<?php

namespace App\Services;

use App\Models\Article;
use App\Models\BuyArticleTransaction;
use App\Models\Transaction;
use App\Models\UndoTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function transferMoney(User $user, User $world, float $amount): void
    {
        $transaction = new Transaction;
        $transaction->from_user_id = $world->id;
        $transaction->to_user_id = $user->id;
        $transaction->amount = $amount;
        $transaction->save();
    }

    public function buyArticle(User $user, User $vendor, Article $article): void
    {
        DB::transaction(function () use ($article, $user, $vendor) {
            $transaction = new Transaction;
            $transaction->from_user_id = $user->id;
            $transaction->to_user_id = $vendor->id;
            $transaction->amount = $article->currentPrice;
            $transaction->save();

            $buyArticleTransaction = new BuyArticleTransaction;
            $buyArticleTransaction->transaction_id = $transaction->id;
            $buyArticleTransaction->article_id = $article->id;
            $buyArticleTransaction->save();
        });
    }

    public function undoTransaction(Transaction $transactionToUndo): void
    {
        DB::transaction(function () use ($transactionToUndo) {
            $transaction = new Transaction;
            $transaction->from_user_id = $transactionToUndo->from_user_id;
            $transaction->to_user_id = $transactionToUndo->to_user_id;
            $transaction->amount = -$transactionToUndo->amount;
            $transaction->save();

            $undoTransaction = new UndoTransaction;
            $undoTransaction->transaction_id = $transaction->id;
            $undoTransaction->undone_transaction_id = $transactionToUndo->id;
            $undoTransaction->save();

        });
    }
}
