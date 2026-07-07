<?php

use App\Enums\UserRole;
use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('feedback page is accessible publicly', function () {
    $this->get(route('feedback.create'))
        ->assertStatus(200);
});

test('feedback can be submitted', function () {
    $this->post(route('feedback.store'), ['message' => 'This is a test feedback.'])
        ->assertRedirect(route('feedback.success'));

    $this->assertDatabaseHas('feedbacks', ['message' => 'This is a test feedback.']);
});

test('feedback success page is accessible publicly', function () {
    $this->get(route('feedback.success'))
        ->assertStatus(200);
});

test('feedback message is required', function () {
    $this->post(route('feedback.store'), ['message' => ''])
        ->assertSessionHasErrors('message');
});

test('honeypot rejects bot submissions silently', function () {
    $this->post(route('feedback.store'), ['message' => 'Bot spam', 'website' => 'http://spam.com'])
        ->assertRedirect(route('feedback.success'));

    $this->assertDatabaseMissing('feedbacks', ['message' => 'Bot spam']);
});

test('feedback submit is rate limited', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('feedback.store'), ['message' => "Message $i"]);
    }

    $this->post(route('feedback.store'), ['message' => 'Over the limit'])
        ->assertStatus(429);
});

test('admin can view feedbacks index', function () {
    Feedback::create(['message' => 'Hello from a user']);

    $admin = testUser(role: UserRole::Admin);

    $this->actingAs($admin)
        ->get(route('feedbacks.index'))
        ->assertStatus(200)
        ->assertSee('Hello from a user');
});

test('non-admin cannot view feedbacks index', function () {
    $user = testUser();

    $this->actingAs($user)
        ->get(route('feedbacks.index'))
        ->assertStatus(403);
});
