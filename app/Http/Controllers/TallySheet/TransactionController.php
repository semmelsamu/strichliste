<?php

namespace App\Http\Controllers\TallySheet;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Barcode;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TallySheetSessionService;
use App\Services\TransactionService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly TallySheetSessionService $tallySheetSessionService,
    ) {}

    public function depositMoney(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
            'action' => ['required', Rule::in(['deposit', 'withdraw'])],
            'world' => [
                'required',
                Rule::exists('users', 'id')->where('type', UserType::World),
            ],
            'amount' => [
                'required',
                'decimal:0,2',
                function (string $attribute, mixed $amount, Closure $fail) use ($user) {
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
        $world = User::findOrFail($validated['world']);

        $this->transactionService->transferMoney(
            user: $user,
            world: $world,
            amount: $action == 'deposit' ? $amount : -1 * $amount,
        );

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' => ($action == 'deposit' ? 'Aufgeladen: ' : 'Abgehoben: ').Number::currency($amount),
            ])
            ->with('sound', $action == 'deposit' ? 'spongebob-moneten' : 'wobble');
    }

    public function buyArticle(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
            'vendor' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('type', UserType::Vendor->value),
            ],
            'article' => [
                'required',
                'integer',
                'bail',
                'exists:articles,id',
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    $article = Article::find($value);

                    if ($user->balance < $article->currentPrice) {
                        $fail('Nicht genügend Guthaben.');
                    }
                },
            ],
        ]);

        $vendor = User::findOrFail($validated['vendor']);
        $article = Article::findOrFail($validated['article']);

        $this->transactionService->buyArticle($user, $vendor, $article);

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'Gekauft: '.$article->name.' für '.Number::currency($article->currentPrice),
            ])
            ->with('sound', collect($article->sounds ?? ['kaching'])->random());
    }

    public function buyArticleByBarcode(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
            'vendor' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('type', UserType::Vendor->value),
            ],
            'barcode' => [
                'required',
                'string',
                'bail',
                'exists:barcodes,barcode',
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    $article = Barcode::where('barcode', $value)->first()?->article;

                    if ($user->balance < $article->currentPrice) {
                        $fail('Nicht genügend Guthaben.');
                    }
                },
            ],
        ]);

        $vendor = User::findOrFail($validated['vendor']);
        $article = Barcode::where('barcode', $validated['barcode'])->firstOrFail()->article;

        $this->transactionService->buyArticle($user, $vendor, $article);

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'Gekauft: '.$article->name.' für '.Number::currency($article->currentPrice),
            ])
            ->with('sound', collect($article->sounds ?? ['kaching'])->random());
    }

    public function undoTransaction(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
            'transaction' => [
                'required',
                'integer',
                'bail',
                'exists:transactions,id',
                Rule::unique('undo_transactions', 'undone_transaction_id'),
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    $transaction = Transaction::find($value);
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

        $transactionToUndo = Transaction::findOrFail($validated['transaction']);

        $this->transactionService->undoTransaction($transactionToUndo);

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'Transaktion rückgängig gemacht',
            ])
            ->with('sound', 'wobble');
    }
}
