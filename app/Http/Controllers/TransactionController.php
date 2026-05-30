<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\Article;
use App\Models\BuyArticleTransaction;
use App\Models\Transaction;
use App\Models\UndoTransaction;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function depositMoney(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['deposit', 'withdraw'])],
            'world' => [
                'required',
                Rule::exists('users', 'id')->where('type', UserType::World),
            ],
            'user' => [
                'required',
                'exists:users,id',
                'bail',
            ],
            'amount' => [
                'required',
                'decimal:0,2',
                function (string $attribute, mixed $amount, Closure $fail) {
                    $user = User::find(request()->user);
                    if (request()->action == 'withdraw') {
                        $amount *= -1;
                    }

                    if ($user->balance < 0) {
                        if ($amount < 0) {
                            $fail('Dein Konto hat schulden, du kannst nicht noch mehr abbuchen.');
                        }
                    } else {
                        if (($user->balance + $amount) < 0) {
                            $fail('Du kannst nicht mehr abbuchen als du auf dem Konto hast.');
                        }
                    }
                },
            ],
        ]);

        $action = $validated['action'];
        $amount = $validated['amount'];
        $world = $validated['world'];
        $user = $validated['user'];

        $transaction = new Transaction;

        if ($action == 'deposit') {
            $transaction->amount = $amount;
        } else {
            $transaction->amount = -1 * $amount;
        }

        $transaction->from_user_id = $world;
        $transaction->to_user_id = $user;
        $transaction->save();

        return back()->with('toast', [
            'type' => 'success',
            'message' => ($action == 'deposit' ? 'Aufgeladen: ' : 'Abgehoben: ').Number::currency($amount),
        ]);
    }

    public function buyArticle(Request $request)
    {
        $validated = $request->validate([
            'user' => [
                'required',
                'integer',
                'exists:users,id',
                'bail',
            ],
            'vendor' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('type', UserType::Vendor->value),
            ],
            'article' => [
                'required',
                'integer',
                'exists:articles,id',
                function (string $attribute, mixed $value, Closure $fail) {
                    $user = User::find(request()->user);
                    $article = Article::find($value);

                    if ($user->balance < $article->currentPrice) {
                        $fail('Nicht genügend Guthaben.');
                    }
                },
            ],
        ]);

        $userId = $validated['user'];
        $vendorId = $validated['vendor'];
        $article = Article::find($validated['article']);

        DB::transaction(function () use ($userId, $vendorId, $article) {
            $transaction = new Transaction;
            $transaction->from_user_id = $userId;
            $transaction->to_user_id = $vendorId;
            $transaction->amount = $article->currentPrice;
            $transaction->save();

            $buyArticleTransaction = new BuyArticleTransaction;
            $buyArticleTransaction->transaction_id = $transaction->id;
            $buyArticleTransaction->article_id = $article->id;
            $buyArticleTransaction->save();
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Gekauft: '.$article->name.' für '.Number::currency($article->currentPrice),
        ]);
    }

    public function undoTransaction(Request $request)
    {
        $validated = $request->validate([
            'user' => ['required',
                'integer',
                'exists:users,id',
                'bail',
            ],
            'transaction' => [
                'required',
                'integer',
                'bail',
                'exists:transactions,id',
                Rule::unique('undo_transactions', 'undone_transaction_id'),
                function (string $attribute, mixed $value, Closure $fail) {
                    $transaction = Transaction::find($value);
                    $user = User::find(request()->user);
                    if (! $transaction) {
                        $fail('Transaktion wurde nicht gefunden');
                    }
                    if ($transaction->from_user_id !== $user->id && $transaction->to_user_id !== $user->id) {
                        $fail('This transaction does not belong to the given user.');
                    }
                    if ($transaction->created_at->lt(now()->subMinutes(5))) {
                        $fail('This transaction is too old to be undone.');
                    }
                },
            ],
        ]);

        $transactionToUndo = Transaction::find($validated['transaction']);

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

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Transaktion rückgängig gemacht',
        ]);
    }
}
