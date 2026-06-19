<?php

use App\Enums\SystemSound;
use App\Models\SystemSoundSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('guests are redirected from the dashboard to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirectToRoute('login');
});

test('validation errors flash the error sound', function () {
    SystemSoundSetting::create([
        'system_sound' => SystemSound::Error,
        'sound' => 'windows-error',
    ]);

    $response = $this->post(route('authenticate'));

    $response
        ->assertRedirect()
        ->assertSessionHas('sound', 'windows-error')
        ->assertSessionHasErrors(['name', 'password']);
});
