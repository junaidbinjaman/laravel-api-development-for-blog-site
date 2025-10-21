<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|unique:users,name|regex:/^[A-Za-z\d\-]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'profile_picture' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'phone_number' => 'nullable|regex:/^[\d\s\+\-]{7,15}$/'
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Regex for the name field failed.'
        ];
    }
}
