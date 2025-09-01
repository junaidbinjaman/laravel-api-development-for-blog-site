<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loginUrl = '/api/login';
    $this->userLoginData = [
        'email' => 'ersome65859@gmail.com',
        'password' => 'jwolt65859j'
    ];
});

test('user can log in successfully', function () {
    $user = User::factory()->create([
        'email' => $this->userLoginData['email'],
        'password' => bcrypt($this->userLoginData['password'])
    ]);

    $response = $this->postJson($this->loginUrl, $this->userLoginData);

    $response->assertStatus(200);
    $this->assertAuthenticatedAs($user);
});

test('authenticated user has correct role', function () {
    $user = User::factory()->create([
        'password' => bcrypt('jwolt65859j'),
        'role' => 'user'
    ]);

    $response = $this->postJson($this->loginUrl, [
        'email' => $user->email,
        'password' => 'jwolt65859j'
    ]);
    $this->actingAs($user);

    $response->assertStatus(200);
    $this->assertEquals('user', auth()->user()->role);
});

test('user enters wrong password', function () {
    User::factory()->create([
        'email' => $this->userLoginData['email'],
        'password' => bcrypt($this->userLoginData['password'])
    ]);

    $response = $this->postJson($this->loginUrl, [
        'email' => $this->userLoginData['email'],
        'password' => 'abc'
    ]);

    $response->assertStatus(401);
    $this->assertGuest();
});
