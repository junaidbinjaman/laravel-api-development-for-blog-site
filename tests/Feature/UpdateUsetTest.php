<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('user successfully updated his own data', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson('/api/user/' . $user->id, [
        'email' => 'new@email.com'
    ]);

    $response->assertStatus(200);
    $this->assertTrue(count($response->json('data')) >= 1);
    $response->assertJson(fn($json) => $json->where('status', 'success')->etc());
});

test('admin successfully updated a user\'s data', function () {
    $users = User::factory()
        ->count(2)
        ->sequence(
            ['role' => 'admin'],
            ['role' => 'user']
        )
        ->create();

    Sanctum::actingAs($users[0], ['*']);

    $response = $this->putJson('/api/user/' . $users[1]->id, ['email' => 'changedby@admin.com']);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'email' => 'changedby@admin.com'
        ]
    ]);
});

test('unauthorized user failed to update user data', function () {
    $users = User::factory()
        ->count(2)
        ->sequence(
            ['role' => 'admin'],
            ['role' => 'user']
        )
        ->create();

    Sanctum::actingAs($users[1], ['*']);

    $response = $this->putJson('/api/user/' . $users[0]->id, ['email' => 'changedby@admin.com']);

    $response->assertStatus(403);
    $response->assertJson(fn($json) => $json->where('message', 'This action is unauthorized.')->etc());
});

test('user passed invalid data', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson('/api/user/' . $user->id, [
        'email' => 'newemail.com'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('unauthenticated user failed to update user data', function () {
    $user = User::factory()->create();

    $response = $this->putJson('/api/user/' . $user->id, [
        'email' => 'new@email.com'
    ]);

    $response->assertStatus(401);
    $response->json(['message' => 'Unauthenticated.']);
});

test('invalid user id. unable to find the user', function () {
    $user = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson('/api/user/' . 2342, [
        'email' => 'new@email.com'
    ]);

    $response->assertStatus(404);
});

test('profile picture updates successfully', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson('/api/user/' . $user->id, ['profile_picture' => UploadedFile::fake()->image('profile.png')]);

    $response->assertStatus(200);

    $user = User::query()->find($user->id)->first();
    Storage::disk('public')->assertExists($user->profile_picture);
});
