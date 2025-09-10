<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use App\Models\Seo;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->endPoint = 'seo';
});

// test seo write action - Happy and Unhappy path
test('admin can post seo meta on an author\'s post', closure: function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $author = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author->id]);

    Sanctum::actingAs($admin);

    $response = $this->postJson($this->endPoint, [
        'meta_title' => 'Testing purpose meta title',
        'meta_description' => 'Testing purpose meta description',
        'post_id' => $blog->id
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseCount('seos', 1);
});

test('author can post seo data on his own post', function () {
    $author = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author->id]);

    Sanctum::actingAs($author);

    $response = $this->postJson($this->endPoint, [
        'meta_title' => 'Testing purpose meta title',
        'meta_description' => 'Testing purpose meta description',
        'post_id' => $blog->id
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseCount('seos', 1);
});

test('an author fails to posts seo data on a different author\'s post', function () {
    $author1 = User::factory()->create(['role' => 'author']);
    $author2 = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author1->id]);

    Sanctum::actingAs($author2);

    $response = $this->postJson($this->endPoint, [
        'meta_title' => 'Testing purpose meta title',
        'meta_description' => 'Testing purpose meta description',
        'post_id' => $blog->id
    ]);

    $response->assertStatus(403);
    $response->assertJson(fn($json) => $json
        ->where('status', 'fail')
        ->etc()
    );
    $this->assertDatabaseCount('seo', 0);
});

test('a non logged in user fails to post seo data', function () {
    $blog = Post::factory()->create();

    $response = $this->postJson($this->endPoint, [
        'meta_title' => 'Testing purpose meta title',
        'meta_description' => 'Testing purpose meta description',
        'post_id' => $blog->id
    ]);

    $response->assertStatus(401);
    $response->assertJson(fn($json) => $json
        ->where('status', 'fail')
        ->etc()
    );
    $this->assertDatabaseCount('seo', 0);
});

// test seo read action
test('a non logged in user can retrieve all seo meta data', function () {
    Seo::factory()->count(10)->create();

    $response = $this->getJson($this->endPoint);

    $response->assertStatus(200);
    $response->assertJson(fn($json) => $json
        ->where('status', 'success')
        ->etc()
    );
    $this->assertDatabaseCount('seo', 10);
});

test('a non logged in user can retrieve a single post seo meta data', function () {
    $post = Seo::factory()->create();

    $response = $this->getJson($this->endPoint . '/' . $post->id);
    $response->assertStatus(200);
    $response->assertJson(fn($json) => $json
        ->where('status', 'success')
        ->etc()
    );
});

// test seo edit action - Happy and Unhappy path
test('admin can edit an author\'s meta data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $author = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author->id]);
    $meta = Seo::factory()->create(['post_id' => $blog->id]);

    Sanctum::actingAs($admin);

    $response = $this->putJson($this->endPoint . '/' . $meta, [
        'meta_title' => 'Meta title has been edited'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('seos', ['meta_title' => 'Meta title has been edited']);
});

test('author can edit his own meta data', function () {
    $author = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author->id]);
    $meta = Seo::factory()->create(['post_id' => $blog->id]);

    Sanctum::actingAs($author);

    $response = $this->putJson($this->endPoint . '/' . $meta, [
        'meta_title' => 'Meta title has been edited'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('seos', ['meta_title' => 'Meta title has been edited']);
});

test('unauthorized user fails to edit an author seo meta', function () {
    $author1 = User::factory()->create(['role' => 'author']);
    $author2 = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author1->id]);
    $meta = Seo::factory()->create(['post_id' => $blog->id]);

    Sanctum::actingAs($author2);

    $response = $this->putJson($this->endPoint . '/' . $meta, [
        'meta_title' => 'Meta title has been edited'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('seos', ['meta_title' => 'Meta title has been edited']);
});

test('unauthenticated user fails to edit a blog post', function () {
    $blog = Post::factory()->create();
    $meta = Seo::factory()->create();

    $response = $this->putJson($this->endPoint . '/' . $meta, [
        'meta_title' => 'Meta title has been edited',
        'meta_description' => 'The meta description goes in here',
        'post_id' => $blog->id
    ]);

    $response->assertStatus(401);
    $response->assertJson(fn($json) => $json
        ->where('status', 'fail')
        ->etc()
    );
});

// test seo delete action - Happy and Unhappy path
test('author can delete his own post seo meta', function () {
    $author = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author->id]);
    $meta = Seo::factory()->create(['post_id' => $blog->id]);

    Sanctum::actingAs($author);

    $response = $this->deleteJson($this->endPoint . '/' . $meta->id);

    $response->assertStatus(200);
    $response->assertJson(fn($json) => $json
        ->where('status', 'fail')
        ->etc()
    );
    $this->assertDatabaseEmpty('seos');
});

test('admin can delete delete an author\'s seo meta data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $author = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author->id]);
    $meta = Seo::factory()->create(['post_id' => $blog->id]);

    Sanctum::actingAs($admin);

    $response = $this->deleteJson($this->endPoint . '/' . $meta->id);

    $response->assertStatus(200);
    $response->assertJson(fn($json) => $json
        ->where('status', 'success')
        ->etc()
    );
    $this->assertDatabaseEmpty('seos');
});

test('a non logged in user fails to delete a post', function () {
    $blog = Post::factory()->create(['author_id' => User::factory()->create()->id]);
    $meta = Seo::factory()->create(['post_id' => $blog->id]);

    $response = $this->deleteJson($this->endPoint . '/' . $meta->id);

    $response->assertStatus(401);
    $response->assertJson(fn($json) => $json
        ->where('status', 'fail')
        ->etc()
    );
    $this->assertDatabaseCount('seos', 1);
});

test('a different author fails to delete a different author\'s seo meta data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $author = User::factory()->create(['role' => 'author']);
    $blog = Post::factory()->create(['author_id' => $author->id]);
    $meta = Seo::factory()->create(['post_id' => $blog->id]);

    Sanctum::actingAs($admin);

    $response = $this->deleteJson($this->endPoint . '/' . $meta->id);

    $response->assertStatus(403);
    $response->assertJson(fn($json) => $json
        ->where('status', 'fail')
        ->etc()
    );
    $this->assertDatabaseCount('seos', 1);
});
