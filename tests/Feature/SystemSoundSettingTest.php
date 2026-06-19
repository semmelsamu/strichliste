<?php

use App\Enums\SystemSound;
use App\Models\SystemSoundSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('it resolves the sound name assigned to a system sound', function () {
    SystemSoundSetting::create([
        'system_sound' => SystemSound::Deposit,
        'sound' => 'kaching',
    ]);

    expect(SystemSoundSetting::get(SystemSound::Deposit))->toBe('kaching');
});

test('it returns null when no sound is assigned to a system sound', function () {
    expect(SystemSoundSetting::get(SystemSound::Deposit))->toBeNull();
});
