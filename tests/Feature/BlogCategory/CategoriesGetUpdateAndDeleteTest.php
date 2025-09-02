<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('get all categories successfully', function () {
    $response = $this->getJson('/api/categories');

    $response->assertStatus(200);
});

test('user can get a single post successfully', function () {
    $category = Category::factory()->create();

    $response = $this->getJson('/api/category/' . $category->id);

    $response->assertStatus(200);
    $response->assertJson(fn($json) => $json->where('status', 'success')->etc());
});

test('invalid category id is passed', function () {
    $category = Category::factory()->create();

    $response = $this->getJson('/api/category/' . $category->id + 1);

    $response->assertStatus(404);
});

test('admin the edit category name successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->putJson('/api/category/' . $category->id, ['name' => 'The category is edited']);

    $response->status(200);
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'The category is edited'
    ]);
});

test('invalid category name', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->putJson('/api/category/' . $category->id, ['name' => '']);

    $response->status(422);
    $response->assertJsonValidationErrors('name');
});

test('author trying to edit the category name', function () {
    $author = User::factory()->create(['role' => 'author']);
    $category = Category::factory()->create();

    Sanctum::actingAs($author);

    $response = $this->putJson('/api/category/' . $category->id, ['name' => 'New category']);

    $response->AssertStatus(403);
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => $category->name, // original name
    ]);
});

test('admin can delete a category successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->deleteJson('/api/category/' . $category->id);

    $response->status(200);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('admin trying to delete an invalid calegory', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Sanctum::actingAs($admin);

    $response = $this->deleteJson('/api/category/' . 100);

    $response->AssertStatus(404);
});

test('author failed to delete a category', function () {
    $author = User::factory()->create(['role' => 'author']);
    $category = Category::factory()->create();

    Sanctum::actingAs($author);

    $response = $this->deleteJson('/api/category/' . $category->id);

    $response->AssertStatus(403);
    $response->assertJson(fn($json) => $json
        ->where('status', 'fail')
        ->etc()
    );
});

