<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;
use App\Models\Post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $category = Category::factory()->create(); // create in DB

    $this->endPoint = '/api/post';
    $this->postData = [
        'title' => 'Test title',
        'slug' => Str::slug('Test title'),
        'category_id' => $category->id, // now has a valid ID
        'content' => 'There are many variations of passages of Lorem Ipsum available',
        'excerpt' => 'There are many variations of..',
    ];
});

test('admin can create a post successfully', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Sanctum::actingAs($user);


    $response = $this->postJson($this->endPoint, $this->postData);

    $response->assertStatus(201);
    $this->assertDatabaseHas('posts', [
        'id' => 1,
        'title' => $this->postData['title']
    ]);
});

test('author can create a post successfully', function () {
    $user = User::factory()->create(['role' => 'author']);

    Sanctum::actingAs($user);

    $response = $this->postJson($this->endPoint, $this->postData);

    $response->assertStatus(201);
    $this->assertDatabaseHas('posts', [
        'id' => 1,
        'title' => $this->postData['title']
    ]);
});

test('unauthorized user failed to create a new post', function () {
    $user = User::factory()->create(['role' => 'user']);

    Sanctum::actingAs($user);

    $response = $this->postJson($this->endPoint, $this->postData);

    $response->assertStatus(403);
    $this->assertDatabaseEmpty('posts');
});

test('invalid data passes and required field empty failed to create the post', function () {
    $user = User::factory()->create(['role' => 'author']);

    Sanctum::actingAs($user);

    $this->postData['title'] = null;

    $response = $this->postJson($this->endPoint, $this->postData);

    $response->assertStatus(422);
    $this->assertDatabaseEmpty('posts');
});

test('post slug already exits', function () {
    $user = User::factory()->create(['role' => 'author']);

    Sanctum::actingAs($user);

    $response1 = $this->postJson($this->endPoint, $this->postData);
    $response2 = $this->postJson($this->endPoint, $this->postData);

    $response1->assertStatus(201);
    $this->assertDatabaseCount('posts', 1);
    $response2->assertStatus(422);
    $response2->assertJsonValidationErrors('slug');
});

test('successfully retrieved all the post', function () {
    $posts = Post::factory()->count(10)->create();

    $response = $this->getJson($this->endPoint);

    $response->assertStatus(200);
    $this->assertEquals(10, count($posts));
    $response->assertJson(fn($json) => $json
        ->where('status', 'success')
        ->etc()
    );
});

test('successfully retrieved a single post', function () {
    $post = Post::factory()->create();

    $response = $this->getJson('/api/posts' . '/' . $post->slug);

    $response->assertStatus(200);
    $response->assertJson(fn($json) => $json
        ->where('status', 'success')
        ->etc()
    );
});

// -------------

test('invalid post slug passed to retrieve a single post', function () {
    $response = $this->getJson('/api/posts' . '/dummy-slug');

    $response->assertStatus(404);
});

test('the admin can edit an author\'s post', function () {
    $author = User::factory()->create(['role' => 'author']);

    Sanctum::actingAs($author);

    $blog = Post::factory()->create(['author_id' => $author->id]);

    $admin = User::factory()->create(['role' => 'admin']);

    Sanctum::actingAs($admin);

    $response = $this->putJson($this->endPoint . '/' . $blog->id, [
        'title' => 'The title has been edited'
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'title' => 'The title has been edited'
        ]
    ]);
    $this->assertDatabaseHas('posts', [
        'id' => $blog->id,
        'title' => 'The title has been edited',
    ]);
});

test('author can edit his own post', function () {
    $author = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author->id]);

    Sanctum::actingAs($author);

    $response = $this->putJson($this->endPoint . '/' . $blog->id, [
        'title' => 'The title has been edited'
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'title' => 'The title has been edited'
        ]
    ]);
    $this->assertDatabaseHas('posts', [
        'id' => $blog->id,
        'title' => 'The title has been edited',
    ]);
});

test('author failed to edit a different author\'s post', function () {
    $author1 = User::factory()->create(['role' => 'author']);
    $author2 = User::factory()->create(['role' => 'author']);

    $blog = Post::factory()->create(['author_id' => $author1->id]);

    Sanctum::actingAs($author2);

    $response = $this->putJson($this->endPoint . '/' . $blog->id, [
        'title' => 'The title has been edited'
    ]);

    $response->assertStatus(403);
});

test('admin can delete an author\'s  post', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $author = User::factory()->create(['role' => 'author']);

    $blog = Post::factory()->create(['author_id' => $author->id]);

    Sanctum::actingAs($admin);

    $response = $this->deleteJson($this->endPoint . '/' . $blog->id);

    $response->assertStatus(200);
    $this->assertDatabaseEmpty('posts');
});

test('author can delete his won post', function () {
    $author = User::factory()->create(['role' => 'author']);

    $blog = Post::factory()->create(['author_id' => $author->id]);

    Sanctum::actingAs($author);

    $response = $this->deleteJson($this->endPoint . '/' . $blog->id);

    $response->assertStatus(200);
    $this->assertDatabaseEmpty('posts');
});

test('author failed to delete a different author\'s post', function () {
    $author1 = User::factory()->create(['role' => 'author']);
    $author2 = User::factory()->create(['role' => 'author']);

    $blog = Post::factory()->create(['author_id' => $author1->id]);

    Sanctum::actingAs($author2);

    $response = $this->deleteJson($this->endPoint . '/' . $blog->id);

    $response->assertStatus(403);
    $this->assertDatabaseCount('posts', 1);
});

test('unauthenticated user failed to delete a post', function () {
    $blog = Post::factory()->create();

    $response = $this->deleteJson($this->endPoint . '/' . $blog->id);

    $response->assertStatus(401);
});
