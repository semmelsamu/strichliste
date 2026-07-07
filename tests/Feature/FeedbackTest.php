<?php

use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('feedback page is accessible publicly', function () {
    $this->get(route('feedback.create'))
        ->assertStatus(200);
});

test('feedback can be submitted', function () {
    $this->post(route('feedback.store'), ['message' => 'This is a test feedback.'])
        ->assertRedirect()
        ->assertSessionHas('toast');

    $this->assertDatabaseHas('feedbacks', ['message' => 'This is a test feedback.']);
});

test('feedback message is required', function () {
    $this->post(route('feedback.store'), ['message' => ''])
        ->assertSessionHasErrors('message');
});
