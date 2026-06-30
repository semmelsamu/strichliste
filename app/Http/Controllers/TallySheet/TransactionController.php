<?php

namespace App\Http\Controllers\TallySheet;

use App\Enums\SystemSound;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Barcode;
use App\Models\SystemSoundSetting;
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
        $world = $this->tallySheetSessionService->get('world');

        $this->transactionService->transferMoney(
            from: $world,
            to: $user,
            amount: $action == 'deposit' ? $amount : -1 * $amount,
        );

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' => ($action == 'deposit' ? 'Aufgeladen: ' : 'Abgehoben: ').Number::currency($amount),
            ])
            ->with('sound', SystemSoundSetting::get(
                $action == 'deposit' ? SystemSound::Deposit : SystemSound::Withdraw
            ));
    }

    public function transferMoney(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
            'recipient' => [
                'required',
                'integer',
                'bail',
                Rule::exists('users', 'id'),
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    if ((int) $value === $user->id) {
                        $fail('Du kannst dir nicht selbst Geld senden.');

                        return;
                    }

                    if (! User::find($value)?->hasRole(UserRole::Customer)) {
                        $fail('Der ausgewählte Nutzer ist ungültig.');
                    }
                },
            ],
            'amount' => [
                'required',
                'decimal:0,2',
                'gt:0',
                function (string $attribute, mixed $amount, Closure $fail) use ($user) {
                    if ($amount > $user->balance) {
                        $fail('Du kannst nicht mehr senden als du auf dem Konto hast.');
                    }
                },
            ],
        ]);

        $recipient = User::findOrFail($validated['recipient']);
        $amount = $validated['amount'];

        $this->transactionService->transferMoney(
            from: $user,
            to: $recipient,
            amount: $amount,
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Gesendet: '.Number::currency($amount).' an '.$recipient->name,
        ]);
    }

    public function buyArticle(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
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

        $vendor = $this->tallySheetSessionService->get('vendor');
        $article = Article::findOrFail($validated['article']);

        $this->transactionService->buyArticle($user, $vendor, $article);

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'Gekauft: '.$article->name.' für '.Number::currency($article->currentPrice),
            ])
            ->with('sound', collect($article->sounds ?? [SystemSoundSetting::get(SystemSound::BuyFallback)])->random());
    }

    public function buyArticleByBarcode(Request $request): RedirectResponse
    {
        $user = $this->tallySheetSessionService->get('user');

        $validated = $request->validate([
            'barcode' => [
                'required',
                'string',
                'bail',
                'exists:barcodes,barcode',
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    $article = Barcode::where('barcode', $value)->first()?->article;

                    if (! $article) {
                        $fail('Artikel nicht gefunden');

                        return;
                    }

                    if ($user->balance < $article->currentPrice) {
                        $fail('Nicht genügend Guthaben.');
                    }
                },
            ],
        ]);

        $vendor = $this->tallySheetSessionService->get('vendor');
        $article = Barcode::where('barcode', $validated['barcode'])->firstOrFail()->article;

        $this->transactionService->buyArticle($user, $vendor, $article);

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'Gekauft: '.$article->name.' für '.Number::currency($article->currentPrice),
            ])
            ->with('sound', collect($article->sounds ?? [SystemSoundSetting::get(SystemSound::BuyFallback)])->random());
    }

    public function buyArticleByScannedUser(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string'],
        ]);

        $user = Barcode::where('barcode', $validated['barcode'])->first()?->user;

        if (! $user || ! $user->hasRole(UserRole::Customer)) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Barcode wurde keinem Nutzer zugeordnet.',
            ]);
        }

        if ($user->balance < $article->currentPrice) {
            $this->tallySheetSessionService->login($user);

            return redirect()->route('tally-sheet.show-deposit')->with('toast', [
                'type' => 'error',
                'message' => 'Nicht genügend Guthaben. Bitte aufladen.',
            ]);
        }

        $vendor = $this->tallySheetSessionService->get('vendor');

        $this->transactionService->buyArticle($user, $vendor, $article);

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'Gekauft: '.$article->name.' für '.Number::currency($article->currentPrice),
            ])
            ->with('sound', collect($article->sounds ?? [SystemSoundSetting::get(SystemSound::BuyFallback)])->random());
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
            ->with('sound', SystemSoundSetting::get(SystemSound::UndoTransaction));
    }
}
