<?php

namespace App\Http\Controllers;

use App\Models\Category;
use BladeUI\Icons\Exceptions\SvgNotFound;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.categories.index', [
            'categories' => Category::orderBy('order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'icon' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) {
                    try {
                        svg('lucide-'.$value);
                    } catch (SvgNotFound) {
                        $fail('The specified icon could not be found.');
                    }
                },
            ],
        ]);

        $category = new Category;

        $category->name = $validated['name'];
        $category->icon = 'lucide-'.$validated['icon'];
        $category->save();

        return redirect()->route('categories.index')->with('toast', [
            'type' => 'success',
            'message' => 'Kategorie wurde erstellt.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $category->icon = Str::after($category->icon, 'lucide-');

        return view('pages.categories.edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'icon' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) {
                    try {
                        svg('lucide-'.$value);
                    } catch (SvgNotFound) {
                        $fail('The specified icon could not be found.');
                    }
                },
            ],
            'hidden' => ['boolean'],
        ]);

        $category->name = $validated['name'];
        $category->icon = 'lucide-'.$validated['icon'];
        $category->hidden = $request->boolean('hidden');
        $category->save();

        return redirect()->route('categories.index')->with('toast', [
            'type' => 'success',
            'message' => 'Änderungen wurden gespeichert.',
        ]);
    }

    /**
     * Update the ordering position of the categories.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['order'] as $categoryId => $order) {
            Category::whereKey($categoryId)->update(['order' => $order]);
        }

        return redirect()->route('categories.index')->with('toast', [
            'type' => 'success',
            'message' => 'Reihenfolge wurde gespeichert.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();

            return redirect()->route('categories.index')->with('toast', [
                'type' => 'success',
                'message' => 'Kategorie wurde gelöscht.',
            ]);
        } catch (QueryException) {

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Kategorie konnte nicht gelöscht werden. Stelle sicher, dass keine Artikel die Kategorie verwenden und versuche es nochmal.',
            ]);
        }
    }
}
