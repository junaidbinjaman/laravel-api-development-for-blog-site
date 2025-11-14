<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::query()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Posts are retrieved successfully',
            'data' => $posts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();

        if ($request->file('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = 'post-thumbnail_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $data['thumbnail'] = $file->storeAs('post-thumbnail', $fileName, 'public');
        }

        $data['author_id'] = auth()->id();
        $data['status'] = auth()->user()->role === 'admin' ? 'published' : 'draft';

        $post = Post::query()->create($data);
        $seoData = $request->only(['meta_title', 'meta_description']);

        if(!empty(array_filter($seoData))) {
            $post->seo()->create($seoData);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'The post has been created successfully',
            'data' => $post
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        //
        $post = Post::query()->where('slug', '=', $slug)->firstOrFail();

        return response()->json([
            'status' => 'success',
            'message' => 'The post is retrieved successfully',
            'data' => $post
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {

        $post->fill($request->only([
            'title', 'slug', 'category_id', 'content', 'excerpt', 'status'
        ]));

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = 'post-thumbnail_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $request->file('thumbnail')->storeAs('post-thumbnail', $fileName, 'public');

            $post->thumbnail = $path;
        }

        $post->save();

        return response()->json([
            'status' => 'success',
            'message' => 'The data has been updated successfully',
            'data' => $post
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if (
            auth()->user()->id !== $post->author_id &&
            auth()->user()->role !== 'admin'
        ) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthenticated.'
            ], 403);
        }

        $post->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'The data has been deleted successfully '
        ], 200);
    }
}
