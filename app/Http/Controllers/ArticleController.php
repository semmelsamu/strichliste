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

    public function edit(Request $request, Article $article)
    {
        return view('pages.articles.edit', [
            'article' => $article,
            'categories' => Category::all(),
            'prices' => ArticlePrice::where('article_id', $article->id)->get(),
        ]);
    }
}
