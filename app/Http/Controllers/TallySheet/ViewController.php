<?php

namespace App\Http\Controllers\TallySheet;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

class ViewController extends Controller
{
    public function showBuyOverview(User $user)
    {
        return view('pages.tally-sheet.buy-overview', [
            'categories' => Category::all(),
            'user' => $user,
        ]);
    }

    public function showBuyCategory(User $user, $category_id)
    {
        return view('pages.tally-sheet.buy-category', [
            'category' => Category::with('articles')->firstWhere('id', $category_id),
            'user' => $user,
        ]);
    }

    public function showDeposit(User $user)
    {
        return view('pages.tally-sheet.deposit', [
            'user' => $user,
        ]);
    }

    public function showHistory(User $user)
    {
        return view('pages.tally-sheet.history', [
            'normalizedTransactions' => $user->transactions()
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn ($t) => Transaction::normalize($t)),
            'user' => $user,
        ]);
    }
}
