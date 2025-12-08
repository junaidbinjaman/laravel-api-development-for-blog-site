<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;

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
        return response()->json([
            'status' => 'success',
            'data' => $comment
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $request->validated();
        $comment->fill($request->only('name', 'description'));
        $comment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'The comment is updated successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $user = auth()->user();

        if ($user->role !== 'admin' && $user->id !== $comment->author_id) {
            return response()->json([
                'status' => 'fail',
                'message' => 'You are not allowed to perform this action'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'The comment is deleted success.'
        ], 200);
    }

    public function getCommentsByPostId(int $post_id)
    {
        $posts = Post::query()->with(['comments'])->findOrFail($post_id);

        return response()->json([
            'status' => 'success',
            'post' => $posts
        ], 200);
    }

    public function approveComment(int $commentId)
    {
        $comment = Comment::query()->find($commentId);

        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => 'fail',
                'message' => 'Sorry!! You are not allowed to perform this action.'
            ], 403);
        }

        if (!$comment) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No comment found'
            ], 404);
        }

        if ($comment->status === 'approved') {
            return response()->json([
                'status' => 'failed',
                'message' => 'The comment is already approved'
            ], 409);
        }

        $comment->status = 'approved';
        $comment->save();

        return response()->json([
            'status' => 'success',
            'comment' => $comment,
            'message' => 'The comment is approved successfully'
        ], 200);
    }

    public function rejectComment(int $commentId)
    {
        $comment = Comment::query()->find($commentId);

        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => 'fail',
                'message' => 'Sorry!! You are not allowed to perform this action.'
            ], 403);
        }

        if (!$comment) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No comment found'
            ], 404);
        }

        if ($comment->status === 'archived') {
            return response()->json([
                'status' => 'failed',
                'message' => 'The comment is already rejected'
            ], 409);
        }

        $comment->status = 'archived';
        $comment->save();

        return response()->json([
            'status' => 'success',
            'comment' => $comment,
            'message' => 'The comment is rejected successfully'
        ], 200);
    }
}
