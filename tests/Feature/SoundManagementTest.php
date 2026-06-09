<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

test('admins can list stored sounds', function () {
    Storage::fake('public');

    $admin = testUser();
    Storage::disk('public')->put('sounds/kaching.mp3', 'sound');

    $this->actingAs($admin)->get(route('sounds.index'))
        ->assertSuccessful()
        ->assertViewIs('pages.sounds.index')
        ->assertViewHas('sounds', fn ($sounds) => $sounds->pluck('filename')->contains('kaching.mp3'));
});

test('admins can upload mp3 sounds with slugged filenames', function () {
    Storage::fake('public');

    $admin = testUser();
    $file = UploadedFile::fake()->create('Cash Register.mp3', 100, 'audio/mpeg');

    $this->actingAs($admin)->post(route('sounds.store'), [
        'sound' => $file,
    ])
        ->assertRedirect(route('sounds.index'))
        ->assertSessionHas('toast.type', 'success');

    Storage::disk('public')->assertExists('sounds/cash-register.mp3');
});

test('sound uploads require an mp3 file under five megabytes', function (UploadedFile|string|null $sound, array $errors) {
    Storage::fake('public');

    $admin = testUser();

    $this->actingAs($admin)->post(route('sounds.store'), [
        'sound' => $sound,
    ])->assertSessionHasErrors($errors);
})->with([
    'missing file' => [null, ['sound']],
    'wrong mime type' => [fn () => UploadedFile::fake()->create('sound.wav', 100, 'audio/wav'), ['sound']],
    'too large' => [fn () => UploadedFile::fake()->create('sound.mp3', 5121, 'audio/mpeg'), ['sound']],
]);

test('admins can delete existing sounds', function () {
    Storage::fake('public');

    $admin = testUser();
    Storage::disk('public')->put('sounds/wobble.mp3', 'sound');

    $this->actingAs($admin)->delete(route('sounds.destroy', 'wobble'))
        ->assertSessionHas('toast.type', 'success');

    Storage::disk('public')->assertMissing('sounds/wobble.mp3');
});
