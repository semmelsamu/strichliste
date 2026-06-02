<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function list(Request $request)
    {
        return view('pages.articles.list', ['articles' => Article::all()]);
    }
}
