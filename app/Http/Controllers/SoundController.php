<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.sounds.index', [
            'sounds' => collect(Storage::disk('public')->files('sounds'))
                ->map(fn ($filename) => [
                    'name' => pathinfo($filename, PATHINFO_FILENAME),
                    'path' => $filename,
                ]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.sounds.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sound' => [
                'required', 'file',
                'mimes:mp3',
                'max:5120', // 5MB
            ],
        ]);

        $sound = $request->file('sound');

        $originalName = $sound->getClientOriginalName();

        $storedName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME))
            .'.'
            .pathinfo($originalName, PATHINFO_EXTENSION);

        $path = $sound->storeAs('sounds', $storedName, 'public');

        return redirect()->route('sounds.index')->with('toast', [
            'type' => 'success',
            'message' => 'Sound wurde hochgeladen.',
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // TODO: Validation as id is passed via URL params any may be harmful
        $safeName = basename($id);

        Storage::disk('public')->delete('sounds/'.$safeName.'.mp3');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Sound wurde gelöscht.',
        ]);
    }
}
