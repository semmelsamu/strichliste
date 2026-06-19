<?php

use App\Enums\SystemSound;
use App\Models\SystemSoundSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

test('it resolves the sound assigned to a system sound', function () {
    Storage::fake('public');
    Storage::disk('public')->put('sounds/kaching.mp3', 'sound');
    SystemSoundSetting::create([
        'system_sound' => SystemSound::Deposit,
        'sound' => 'kaching',
    ]);

    $sound = SystemSoundSetting::sound(SystemSound::Deposit);

    expect($sound->name())->toBe('kaching');
});

test('it returns null when no sound is assigned to a system sound', function () {
    expect(SystemSoundSetting::sound(SystemSound::Deposit))->toBeNull();
});
