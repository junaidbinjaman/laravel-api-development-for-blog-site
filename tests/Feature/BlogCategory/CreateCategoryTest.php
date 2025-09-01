<?php
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
   $this->endPoint = '/api/category';
   $this->categoryData = [
       'name' => 'Books',
   ];
});

test('admin can create a category successfully', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson($this->endPoint, $this->categoryData);

    $response->assertStatus(201);
    $this->assertDatabaseHas('categories', ['name' => 'Books']);
});

test('author fails to create a new category', function () {
    $user = User::factory()->create(['role' => 'author']);

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson($this->endPoint, $this->categoryData);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('categories', ['name' => 'Bpooks']);
});

test('unauthenticated user fails to create a new category', function () {
    $response = $this->postJson($this->endPoint, $this->categoryData);

    $response->assertStatus(401);
    $response->assertJson(fn ($json) => $json->where('message', 'Unauthenticated.'));
});

test('category name already esits', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Sanctum::actingAs($user, ['*']);

    $response1 = $this->postJson($this->endPoint, $this->categoryData);
    $response2 = $this->postJson($this->endPoint, $this->categoryData);

    $response1->assertStatus(201);
    $response2->assertStatus(422);
    $response2->assertJsonValidationErrors('name');
});
