<?php

use App\Enums\UserRole;
use App\Services\TallySheetSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('the start session form is shown without world and vendor selected', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);

    $this->actingAs($admin)->get(route('tally-sheet.start-session'))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.start-session')
        ->assertViewHas('worlds', fn ($worlds) => $worlds->contains($world))
        ->assertViewHas('vendors', fn ($vendors) => $vendors->contains($vendor));
});

test('a tally sheet session can be started with a valid world and vendor', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);

    $this->actingAs($admin)->get(route('tally-sheet.start-session', [
        'world' => $world->id,
        'vendor' => $vendor->id,
    ]))
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionHas(TallySheetSessionService::WORLD_SESSION_KEY, $world->id)
        ->assertSessionHas(TallySheetSessionService::VENDOR_SESSION_KEY, $vendor->id);
});

test('the start session form is shown again when only one of world or vendor is given', function () {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);

    $this->actingAs($admin)->get(route('tally-sheet.start-session', ['world' => $world->id]))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.start-session');
});

test('a tally host with an assigned world and vendor automatically starts a session', function () {
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);
    $tallyHost = testUser([
        'assigned_world_id' => $world->id,
        'assigned_vendor_id' => $vendor->id,
    ], UserRole::TallyHost);

    $this->actingAs($tallyHost)->get(route('tally-sheet.start-session'))
        ->assertRedirect(route('tally-sheet.auth.list-users'))
        ->assertSessionHas(TallySheetSessionService::WORLD_SESSION_KEY, $world->id)
        ->assertSessionHas(TallySheetSessionService::VENDOR_SESSION_KEY, $vendor->id);
});

test('a tally host falls back to the manual picker when the assigned vendor no longer has the vendor role', function () {
    $world = testUser([], UserRole::World);
    $invalidVendor = testUser();
    $tallyHost = testUser([
        'assigned_world_id' => $world->id,
        'assigned_vendor_id' => $invalidVendor->id,
    ], UserRole::TallyHost);

    $this->actingAs($tallyHost)->get(route('tally-sheet.start-session'))
        ->assertSuccessful()
        ->assertViewIs('pages.tally-sheet.start-session');
});

test('tally hosts cannot stop a session', function () {
    $tallyHost = testUser([], UserRole::TallyHost);

    $this->actingAs($tallyHost)
        ->withSession(tallySheetRunningSession())
        ->get(route('tally-sheet.stop-session'))
        ->assertForbidden();
});

test('admins can stop a session', function () {
    $admin = testUser([], UserRole::Admin);

    $this->actingAs($admin)
        ->withSession(tallySheetRunningSession())
        ->get(route('tally-sheet.stop-session'))
        ->assertRedirect(route('dashboard'));
});

test('starting a session validates world and vendor', function (array $payload, array $errors) {
    $admin = testUser([], UserRole::TallyHost);
    $world = testUser([], UserRole::World);
    $vendor = testUser([], UserRole::Vendor);

    $payload = array_replace([
        'world' => $world->id,
        'vendor' => $vendor->id,
    ], $payload);

    $payload = collect($payload)
        ->map(fn ($value) => $value instanceof Closure ? $value() : $value)
        ->all();

    $this->actingAs($admin)->get(route('tally-sheet.start-session', $payload))
        ->assertSessionHasErrors($errors);
})->with([
    'non-integer world' => [['world' => 'not-a-number'], ['world']],
    'non-integer vendor' => [['vendor' => 'not-a-number'], ['vendor']],
    'world without the world role' => [['world' => fn () => testUser([], UserRole::Customer)->id], ['world']],
    'vendor without the vendor role' => [['vendor' => fn () => testUser([], UserRole::Customer)->id], ['vendor']],
]);
