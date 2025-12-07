<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use Laravel\Sanctum\Sanctum;
use App\Models\Comment;
use App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->endpoint = '/api/comment';
});

// Create -- Happy Path
test('logged-in author can post a comment successfully', function () {
    $user = User::factory()->create(['role' => 'author', 'name' => 'junaid']);
    $blog = Post::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson($this->endpoint, [
        'name' => auth()->user()->name,
        'description' => 'The testing description goes in here',
        'post_id' => $blog->id,
        'author_id' => auth()->user()->id,
        'status' => 'pending',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseCount('comments', 1);
});

test('logged-in user can post a comment', function () {
    $user = User::factory()->create(['role' => 'user']);
    $blog = Post::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson($this->endpoint, [
        'name' => auth()->user()->name,
        'description' => 'The testing description goes in here',
        'post_id' => $blog->id,
        'author_id' => auth()->user()->id,
        'status' => 'pending',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseCount('comments', 1);
});

// Create -- Unhappy path
test('non logged-in user fails to post a comment', function () {
    $blog = Post::factory()->create();

    $response = $this->postJson($this->endpoint, [
        'description' => 'The testing description goes in here',
        'post_id' => $blog->id,
        'status' => 'pending',
    ]);

    $response->assertStatus(401);
    $this->assertDatabaseCount('comments', 0);
});

test('fails to post invalid comment data', function () {
    $user = User::factory()->create(['role' => 'user']);
    $blog = Post::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson($this->endpoint, [
        'name' => auth()->user()->name,
        'description' => '',
        'post_id' => $blog->id,
        'author_id' => auth()->user()->id,
        'status' => 'pending',
    ]);

    $response->assertStatus(422);
    $this->assertDatabaseCount('comments', 0);
});

test('fails to post with invalid post id', function () {
    $user = User::factory()->create(['role' => 'user']);
    $blog = Post::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson($this->endpoint, [
        'name' => auth()->user()->name,
        'description' => 'The testing description goes in here',
        'post_id' => $blog->id + 1, // Invalid post id
        'author_id' => auth()->user()->id,
        'status' => 'pending',
    ]);

    $response->assertStatus(422);
    $this->assertDatabaseCount('comments', 0);
});

// Read All -- Happy Path
test('admin can retrieve all comments', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Comment::factory()->count(10)->create();

    Sanctum::actingAs($admin);
    $response = $this->getJson('/api/comment');

    $response->assertStatus(200);
    $response->assertJsonCount(10, 'data');
});

test('author/user can retrieve all approved comments', function () {
    $user = User::factory()->create(['role' => 'user']);

    $posts = Post::factory()->count(2)->for($user, 'author')->create();

    Comment::factory()->count(2)->for($posts[0])->create(['status' => 'approved']);
    Comment::factory()->count(1)->for($posts[0])->create(['status' => 'draft']);
    Comment::factory()->count(3)->for($posts[1])->create(['status' => 'approved']);
    Comment::factory()->count(2)->for($posts[1])->create(['status' => 'archived']);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/comment');

    $response->assertStatus(200);

    // Ensure only approved comments are returned
    $responseData = $response->json('data');
    foreach ($responseData as $comment) {
        expect($comment['status'])->toBe('approved');
    }

    $this->assertCount(5, $responseData); // 2 + 3 approved comments
});


test('user can retrieve comments by post id', function () {
    $post = Post::factory()->create();
    Comment::factory()->count(4)->for($post)->create();

    $response = $this->getJson("/api/comment/post/{$post->id}");

    $response->assertStatus(200);
    $response->assertJsonCount(4, 'post.comments');
});

// Read single data --  Happy Path
test('non logged-in user can retrieve a single comment', function () {
    $comment = Comment::factory()->create();

    $response = $this->getJson("/api/comment/{$comment->id}");

    $response->assertStatus(200);
    $response->assertJson(fn($json) => $json->where('data.id', $comment->id)
        ->where('data.description', $comment->description)
        ->etc()
    );
});

// Read single data -- Unhappy Path
test('non logged-in user fails to retrieve a single comment by invalid comment id', function () {
    $invalidId = 9999;

    $response = $this->getJson("/api/comments/{$invalidId}");

    $response->assertStatus(404);
});


// Update -- Happy Path
test('admin approves a comment successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $comment = Comment::factory()->create([
        'status' => 'draft'
    ]);

    Sanctum::actingAs($admin);
    $response = $this->putJson('/api/comment/' . $comment->id . '/approve');

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', ['status' => 'approved']);
});

test('admin rejects a comment successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $comment = Comment::factory()->create(['status' => 'draft']);

    Sanctum::actingAs($admin);
    $response = $this->putJson('/api/comment/' . $comment->id . '/archive');

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', ['status' => 'archived']);
});

test('admin can edit the comment description successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $comment = Comment::factory()->create();

    Sanctum::actingAs($admin);
    $response = $this->putJson(`comment/{$comment->id}`, [
        'description' => 'New description'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', ['description' => 'New description']);
});

test('user can edit his own comment description', function () {
    $user = User::factory()->create(['role' => 'user']);
    $comment = Comment::factory()->for($user, 'author')->create();

    Sanctum::actingAs($user);
    $response = $this->putJson(`comment/{$comment->id}`, [
        'description' => 'New description'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', ['description' => 'New description']);
});

// Update -- Unhappy Path
test('author fails to edit a user comment description', function () {
    $author = User::factory()->create(['role' => 'author']);
    $comment = Comment::factory()->create();

    Sanctum::actingAs($author);
    $response = $this->putJson(`comment/{$comment->id}`, [
        'description' => 'New description'
    ]);

    $response->assertStatus(403);
});

test('a user fails to edit a different user comment description', function () {
    $user = User::factory()->create(['role' => 'user']);
    $comment = Comment::factory()->create();

    Sanctum::actingAs($user);
    $response = $this->putJson(`comment/{$comment->id}`, [
        'description' => 'New description'
    ]);

    $response->assertStatus(403);
});

test('non logged-in user fails to edit a comment description', function () {
    $comment = Comment::factory()->create();

    $response = $this->putJson(`comment/{$comment->id}`, [
        'description' => 'New description'
    ]);

    $response->assertStatus(401);
});

test('an admin fails to edit an invalid comment description', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $comment = Comment::factory()->create();

    Sanctum::actingAs($admin);
    $response = $this->putJson(`comment/{$comment->id}`, [
        'description' => ''
    ]);

    $response->assertStatus(422);
});

// Delete -- Happy Path
test('admin can delete a comment successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $comment = Comment::factory()->create();

    Sanctum::actingAs($admin);
    $response = $this->deleteJson(`comment/{$comment->id}`);

    $response->assertStatus(200);
    $this->assertDatabaseCount('comments', 0);
});

test('user can delete his won comment successfully', function () {
    $user = User::factory()->create(['role' => 'user']);
    $comment = Comment::factory()->for($user, 'author')->create();

    Sanctum::actingAs($user);
    $response = $this->deleteJson(`/api/comment/{$comment->id}`);

    $response->assertStatus(200);
    $this->assertDatabaseCount('comments', 0);
});

// Delete -- Unhappy Path
test('user fails to delete a different user\'s comment', function () {
    $user = User::factory()->create(['role' => 'user']);
    $comment = Comment::factory()->create();

    Sanctum::actingAs($user);
    $response = $this->deleteJson(`/api/comment/{$comment->id}`);

    $response->assertStatus(403);
    $this->assertDatabaseCount('comments', 1);
});

test('author fails to delete a comment', function () {
    $author = User::factory()->create(['role' => 'author']);
    $comment = Comment::factory()->create();

    Sanctum::actingAs($author);
    $response = $this->deleteJson(`/api/comment/{$comment->id}`);

    $response->assertStatus(403);
    $this->assertDatabaseCount('comments', 1);
});

test('admin fails to delete an invalid author', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $comment = Comment::factory()->create();

    Sanctum::actingAs($admin);
    $response = $this->deleteJson(`comment/999`);

    $response->assertStatus(404);
    $this->assertDatabaseCount('comments', 1);
});
