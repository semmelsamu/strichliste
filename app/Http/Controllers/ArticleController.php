<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticlePrice;
use App\Models\Category;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.articles.list', ['articles' => Article::all()]);
    }

    public function create(Request $request)
    {
        return view('pages.articles.create', [
            'categories' => Category::all(),
        ]);
    }

    public function edit(Request $request, Article $article)
    {
        return view('pages.articles.edit', [
            'article' => $article,
            'categories' => Category::all(),
            'prices' => ArticlePrice::where('article_id', $article->id)->orderByDesc('effective_since')->get(),
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'category' => ['required', 'integer'],
        ]);

        $article->name = $validated['name'];
        $article->category_id = $validated['category'];
        $article->save();

        return redirect()->route('articles.index')->with('toast', [
            'type' => 'success',
            'message' => 'Änderungen gespeichert.',
        ]);
    }

    public function updatePrice(Request $request, Article $article)
    {
        $validated = $request->validate([
            'price' => ['required', 'decimal:0,2'],
        ]);

        $newPrice = new ArticlePrice;

        $newPrice->article_id = $article->id;
        $newPrice->price = $validated['price'];
        $newPrice->save();

        return redirect()->route('articles.edit', $article->id)->with('toast', [
            'type' => 'success',
            'message' => 'Preis geändert.',
        ]);
    }
}
