<?php

test('new users can register', function () {
    $response = $this->post('/register', [
        'nom' => 'Dupont',
        'prenom' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertNoContent();
});
