<?php

namespace App\Http\Controllers;

use App\Enums\SystemSound;
use App\Models\Sound;
use App\Models\SystemSoundSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.sounds.index', [
            'sounds' => Sound::all(),
            'systemSounds' => SystemSoundSetting::all()->keyBy(fn (SystemSoundSetting $systemSoundSetting) => $systemSoundSetting->system_sound->value),
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

        Sound::create($request->file('sound'));

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
        $sound = Sound::get($id);

        if (! isset($sound)) {
            abort(404, 'Sound wurde nicht gefunden');
        }

        $sound->destroy();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Sound wurde gelöscht.',
        ]);
    }

    public function updateSystemSounds(Request $request)
    {
        $validated = $request->validate(
            collect(SystemSound::cases())
                ->mapWithKeys(fn (SystemSound $systemSound) => [
                    $systemSound->value => ['nullable', 'string'],
                ])
                ->all()
        );

        SystemSoundSetting::upsert(
            collect(SystemSound::cases())
                ->map(fn (SystemSound $systemSound) => [
                    'system_sound' => $systemSound->value,
                    'sound' => $validated[$systemSound->value] ?? null,
                ])
                ->all(),
            uniqueBy: ['system_sound'],
            update: ['sound'],
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'System-Sounds wurden gespeichert.',
        ]);
    }
}
