<?php

use App\Enums\SystemSound;
use App\Enums\UserRole;
use App\Models\SystemSoundSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

test('admins can list stored sounds', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);
    Storage::disk('public')->put('sounds/kaching.mp3', 'sound');

    $this->actingAs($admin)->get(route('sounds.index'))
        ->assertSuccessful()
        ->assertViewIs('pages.sounds.index')
        ->assertViewHas('sounds', fn ($sounds) => $sounds->pluck('filename')->contains('kaching.mp3'));
});

test('the index page preselects sounds already assigned to system sounds', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);
    Storage::disk('public')->put('sounds/kaching.mp3', 'sound');
    SystemSoundSetting::create([
        'system_sound' => SystemSound::Deposit,
        'sound' => 'kaching',
    ]);

    $this->actingAs($admin)->get(route('sounds.index'))
        ->assertSuccessful()
        ->assertSeeInOrder(['value="kaching"', 'selected'], false);
});

test('admins can upload mp3 sounds with slugged filenames', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);
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

    $admin = testUser([], UserRole::Admin);

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

    $admin = testUser([], UserRole::Admin);
    Storage::disk('public')->put('sounds/wobble.mp3', 'sound');

    $this->actingAs($admin)->delete(route('sounds.destroy', 'wobble'))
        ->assertSessionHas('toast.type', 'success');

    Storage::disk('public')->assertMissing('sounds/wobble.mp3');
});

test('admins can assign sounds to system sounds', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);

    $this->actingAs($admin)->put(route('sounds.update-system-sounds'), [
        SystemSound::Deposit->value => 'kaching',
        SystemSound::Withdraw->value => 'wobble',
    ])
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success');

    $this->assertDatabaseHas('system_sounds', [
        'system_sound' => SystemSound::Deposit->value,
        'sound' => 'kaching',
    ]);
    $this->assertDatabaseHas('system_sounds', [
        'system_sound' => SystemSound::Withdraw->value,
        'sound' => 'wobble',
    ]);
});

test('assigning system sounds again updates the existing setting', function () {
    Storage::fake('public');

    $admin = testUser([], UserRole::Admin);
    SystemSoundSetting::create([
        'system_sound' => SystemSound::Deposit,
        'sound' => 'kaching',
    ]);

    $this->actingAs($admin)->put(route('sounds.update-system-sounds'), [
        SystemSound::Deposit->value => 'wobble',
    ])->assertSessionHas('toast.type', 'success');

    expect(SystemSoundSetting::where('system_sound', SystemSound::Deposit)->count())->toBe(1);
    $this->assertDatabaseHas('system_sounds', [
        'system_sound' => SystemSound::Deposit->value,
        'sound' => 'wobble',
    ]);
});
