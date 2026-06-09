<?php

namespace App\Http\Controllers\TallySheet;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TallySheetSessionService;

class ViewController extends Controller
{
    public function __construct(private readonly TallySheetSessionService $tallySheetSessionService) {}

    public function showBuyOverview()
    {
        return view('pages.tally-sheet.buy-overview', [
            'categories' => Category::all(),
        ]);
    }

    public function showBuyCategory($category_id)
    {
        return view('pages.tally-sheet.buy-category', [
            'category' => Category::with('articles')->firstWhere('id', $category_id),
        ]);
    }

    public function showDeposit()
    {
        return view('pages.tally-sheet.deposit');
    }

    public function showHistory()
    {
        $user = $this->tallySheetSessionService->get('user');

        return view('pages.tally-sheet.history', [
            'normalizedTransactions' => $user->transactions()
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn ($t) => Transaction::normalize($t)),
        ]);
    }
}
