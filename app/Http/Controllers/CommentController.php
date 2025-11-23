<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            $allComments = Comment::all();
            return response()->json([
                'status' => 'success',
                'message' => 'All status comments are retrieved successfully',
                'data' => $allComments
            ], 200);
        }

        $approvedComments = Comment::query()->where('status', 'approved')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Approved comments are retrieved successfully',
            'data' => $approvedComments
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request)
    {
        $description = $request->description;
        $post_id = $request->post_id;

        Comment::query()->create([
            'name' => auth()->user()->name,
            'description' => $description,
            'author_id' => auth()->user()->id,
            'post_id' => $post_id,
            'status' => auth()->user()->role === 'admin' ? 'approved' : 'draft',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'The comment has been posted successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
