<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        if (!$post) {
            return true;
        }

        return auth()->user()->role === 'admin' || auth()->user()->id === $post->author_id;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:posts,slug',
            'content' => 'sometimes|string|nullable',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'excerpt' => 'sometimes|string|nullable',
            'thumbnail' => 'sometimes|file|mimes:jpg,png,jpeg,webp|max:2048',
        ];
    }
}
