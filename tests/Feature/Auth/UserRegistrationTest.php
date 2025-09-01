<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->endPoint = '/api/registerUser';
    $this->userData = [
        'name' => 'junaidbinjaman',
        'email' => 'ersome@gmail.com',
        'password' => 'jwolt65859j',
        'password_confirmation' => 'jwolt65859j',
        'user_role' => 'admin',
        'profile_picture' => UploadedFile::fake()->image('profile_pic.png')
    ];
});

test('profile picture is saved after registration', function () {
    Storage::fake('public');
    $response = $this->json('POST', $this->endPoint, $this->userData);

    $response->assertStatus(201);
    $this->assertDatabaseHas('users', ['email' => $this->userData['email']]);

    $user = User::query()->where('email', $this->userData['email'])->first();
    Storage::disk('public')->assertExists($user->profile_picture);
});

test('User email should be already in used', function () {
    \App\Models\User::factory()->create([
        'email' => $this->userData['email']
    ]);
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
