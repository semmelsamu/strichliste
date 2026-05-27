<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\Article;
use App\Models\Transaction;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
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

        $transaction = new Transaction;

        if ($validated['action'] == 'deposit') {
            $transaction->amount = $validated['amount'];
        } else {
            $transaction->amount = -1 * $validated['amount'];
        }

        $transaction->from_user_id = $validated['world'];
        $transaction->to_user_id = $validated['user'];
        $transaction->save();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Action completed successfully!',
        ]);
    }

    public function buyArticle(Request $request)
    {
        $validated = $request->validate([
            'user' => [
                'required',
                'exists:users,id',
                'bail',
            ],
            'vendor' => [
                'required',
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

        dd($validated);
    }
}
