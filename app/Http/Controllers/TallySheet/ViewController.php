<?php

namespace App\Http\Controllers\TallySheet;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Services\TallySheetSessionService;

class ViewController extends Controller
{
    public function __construct(private readonly TallySheetSessionService $tallySheetSessionService) {}

    public function showBuyOverview()
    {
        $user = $this->tallySheetSessionService->get('user');

        return view('pages.tally-sheet.buy-overview', [
            'categories' => Category::where('hidden', false)->orderBy('order')->orderBy('name')->get(),
            'mostFrequentArticles' => Article::query()
                ->select('articles.*')
                ->selectRaw('COUNT(buy_article_transactions.transaction_id) as purchases_count')
                ->join('buy_article_transactions', 'articles.id', '=', 'buy_article_transactions.article_id')
                ->join('transactions', 'buy_article_transactions.transaction_id', '=', 'transactions.id')
                ->where('transactions.from_user_id', $user->id)
                ->where('transactions.created_at', '>=', now()->subMonths(3))
                ->groupBy('articles.id')
                ->orderByDesc('purchases_count')
                ->limit(4)
                ->get(),
        ]);
    }

    public function showBuyCategory($category_id)
    {
        return view('pages.tally-sheet.buy-category', [
            'category' => Category::with(['articles' => fn ($q) => $q->orderBy('name')])->firstWhere('id', $category_id),
        ]);
    }

    public function showDeposit()
    {
        return view('pages.tally-sheet.deposit');
    }

    public function showTransfer()
    {
        $user = $this->tallySheetSessionService->get('user');

        return view('pages.tally-sheet.transfer', [
            'recipients' => User::role(UserRole::Customer)->whereKeyNot($user->id)->orderBy('name')->get(),
        ]);
    }

    public function showArticleDetails(Article $article)
    {
        return view('pages.tally-sheet.article-details', [
            'article' => $article,
        ]);
    }

    public function showHistory()
    {
        return view('pages.tally-sheet.history');
    }
}
