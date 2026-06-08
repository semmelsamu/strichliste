<?php

test('guests are redirected from the dashboard to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirectToRoute('login');
});

test('validation errors flash the error sound', function () {
    $response = $this->post(route('authenticate'));

    $response
        ->assertRedirect()
        ->assertSessionHas('sound', 'windows-error')
        ->assertSessionHasErrors(['name', 'password']);
});
