<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostCategoryRequest;
use App\Http\Requests\UpdatePostCategory;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $allCategories = Category::query()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'The call is successful.',
            'data' => $allCategories
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePostCategoryRequest $request)
    {
        //
        $category = Category::query()->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'The blog category has been created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
        return response()->json([
            'status' => 'success',
            'message' => 'The category is found successfully',
            'category' => $category
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostCategory $request, Category $category)
    {
       $category->name = $request->name;
       $category->save();

        return response()->json([
            'status' => 'success',
            'message' => 'The category has been updated successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => 'fail',
                'message' => 'You are not allowed to perform this action'
            ], 403);
        }
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'The category has been deleted successfully'
        ], 200);
    }
}
