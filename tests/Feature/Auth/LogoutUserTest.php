<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loginUrl = '/api/login';
    $this->logoutUrl = '/api/logout';
    $this->userData = ['email' => 'ersome65859@gmail.com', 'password' => 'jwolt65859j', 'role' => 'user'];
});

test('Logout user successfully', function () {
    $user = User::factory()->create($this->userData);

    Sanctum::actingAs($user, ['*']);

    $response = $this->post($this->logoutUrl);

    $response->assertStatus(200);
    $response->assertJson(['status' => 'success']);
});

test('Unauthenticated user trying to logout', function () {
    $response = $this->postJson($this->logoutUrl);

    $response->assertStatus(401);
    $response->assertJson(['message' => 'Unauthenticated.']);
});
