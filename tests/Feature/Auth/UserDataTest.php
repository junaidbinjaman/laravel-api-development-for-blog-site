<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->endPoint = '/api/user';
});

test('user gets his own data successfully', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson($this->endPoint . '/' . $user->id);

    $response->assertStatus(200);
    $this->assertTrue(count($response->json('data')) >= 1);
    $response->assertJson(fn($json) => $json->where('status', 'success')->etc());
});

test('admin can retrieve other user\'s data', function () {
    $users = User::factory()
        ->count(2)
        ->sequence(
            ['role' => 'admin'],
            ['role' => 'user']
        )
        ->create();

    Sanctum::actingAs($users[0], ['*']);

    $response = $this->getJson($this->endPoint . '/' . $users[1]->id); // Trying to get user one's data wile logged in as user 0

    $response->assertStatus(200);
    $this->assertTrue(count($response->json('data')) >= 1);
    $response->assertJson(fn($json) => $json->where('status', 'success')->etc());
});

test('user fail to gets a different user data', function () {
    $users = User::factory()
        ->count(2)
        ->sequence(
            ['role' => 'admin'],
            ['role' => 'user']
        )
        ->create();

    Sanctum::actingAs($users[1], ['*']);

    $response = $this->getJson($this->endPoint . '/' . $users[0]->id);

    $response->assertStatus(403);
    $response->assertJson(fn($json) => $json->where('status', 'fail')
        ->where('data', [])
        ->etc()
    );
});

test('fail to get user data because of being unauthenticated', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->getJson($this->endPoint . '/' . $user->id);

    $response->assertStatus(401);
    $response->assertJson(['message' => 'Unauthenticated.']);
});

test('non existent user id passed ', function () {
    $user = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson($this->endPoint . '/' . 17366);

    $response->assertStatus(404);
});
