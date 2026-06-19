<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticlePrice;
use App\Models\Barcode;
use App\Models\Category;
use App\Models\Sound;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index(Request $request)
    {
        return view('pages.articles.index', [
            'articles' => Article::all(),
            'archivedArticles' => Article::onlyTrashed()->orderBy('deleted_at', 'desc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('pages.articles.create', [
            'categories' => Category::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'category' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'decimal:0,2', 'gte:0'],
        ]);

        $article = new Article;
        $article->name = $validated['name'];
        $article->category_id = $validated['category'];
        $article->save();

        $price = new ArticlePrice;
        $price->price = $validated['price'];
        $price->article_id = $article->id;
        $price->save();

        return redirect()->route('articles.index')->with('toast', [
            'type' => 'success',
            'message' => 'Artikel wurde erstellt.',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Article $article)
    {
        return view('pages.articles.edit', [
            'article' => $article,
            'categories' => Category::all(),
            'prices' => ArticlePrice::where('article_id', $article->id)->orderByDesc('effective_since')->get(),
            'sounds' => Sound::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'category' => ['required', 'integer', 'exists:categories,id'],
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
            'price' => ['required', 'decimal:0,2', 'gte:0'],
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

    public function updateSounds(Request $request, Article $article)
    {
        $sounds = [];

        foreach (Sound::all() as $sound) {
            if ($request->input($sound->name())) {
                array_push($sounds, $sound->name());
            }
        }

        $article->sounds = $sounds;
        $article->save();

        return redirect()->route('articles.edit', $article->id)->with('toast', [
            'type' => 'success',
            'message' => 'Sound-Auswahl gespeichert.',
        ]);
    }

    public function updateImage(Request $request, Article $article)
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'max:8192'],
        ]);

        $article->setImageFromUpload($validated['image']);

        return redirect()->route('articles.edit', $article->id)->with('toast', [
            'type' => 'success',
            'message' => 'Bild wurde gespeichert.',
        ]);
    }

    public function deleteImage(Article $article)
    {
        $article->deleteImage();

        return redirect()->route('articles.edit', $article->id)->with('toast', [
            'type' => 'success',
            'message' => 'Bild wurde entfernt.',
        ]);
    }

    public function addBarcode(Request $request, Article $article)
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string'],
        ]);

        $barcode = Barcode::where('barcode', $validated['barcode'])->first() ?? new Barcode;
        $barcode->barcode = $validated['barcode'];
        $barcode->user_id = null;
        $barcode->article_id = $article->id;
        $barcode->save();

        return redirect()->route('articles.edit', $article->id)->with('toast', [
            'type' => 'success',
            'message' => 'Barcode wurde verknüpft.',
        ]);
    }

    public function removeBarcode(Request $request, Article $article, Barcode $barcode)
    {
        $barcode->article()->dissociate();
        $barcode->save();

        return redirect()->route('articles.edit', $article->id)->with('toast', [
            'type' => 'success',
            'message' => 'Barcode wurde entfernt.',
        ]);
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->route('articles.index', $article->id)->with('toast', [
            'type' => 'success',
            'message' => 'Artikel wurde archiviert.',
        ]);
    }

    public function restore(Article $article)
    {
        $article->restore();

        return redirect()->route('articles.index', $article->id)->with('toast', [
            'type' => 'success',
            'message' => 'Artikel wurde wiederhergestellt.',
        ]);
    }
}
