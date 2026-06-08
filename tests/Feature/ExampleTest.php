<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('validation errors flash the error sound', function () {
    $response = $this->post(route('authenticate'));

    $response
        ->assertRedirect()
        ->assertSessionHas('sound', 'windows-error')
        ->assertSessionHasErrors(['name', 'password']);
});
