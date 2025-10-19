<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->endPoint = '/api/registerUser';
    $this->userData = [
        'first_name' => 'Ersome',
        'last_name' => 'rego',
        'email' => 'ersome@gmail.com',
        'password' => 'jwolt65859j',
        'password_confirmation' => 'jwolt65859j',
        'user_role' => 'admin',
        'profile_picture' => '/storage/ersome_profile.png'
    ];
});

test('User register successfully', function () {
    $response = $this->postJson($this->endPoint, $this->userData);

    $response->assertStatus(201);
    $this->assertDatabasehas('users', [
        'email' => $this->userData['email']
    ]);
});

test('User email should be already in used', function () {
    $response = $this->postJson($this->endPoint, $this->userData);

    $response->assertStatus(422);
});

test('Invalid email', function () {
    $userDataWithInvalidEmail = $this->userData;
    $userDataWithInvalidEmail['email'] = 'ersome65859gmail.com';

    $response = $this->postJson($this->endPoint, $userDataWithInvalidEmail);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('Invalid password', function () {
    $userDataWithInvalidEmail = $this->userData;
    $userDataWithInvalidEmail['password'] = 'aaa'; // Password is too short

    $response = $this->postJson($this->endPoint, $userDataWithInvalidEmail);
    $response->assertStatus(422);
});

test('password confirmation must match', function () {
    $data = $this->userData;
    $data['password_confirmation'] = 'wrongpassword';

    $response = $this->postJson($this->endPoint, $data);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

