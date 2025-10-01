<?php

namespace App\Http\Requests;

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
            'first_name' => 'nullable|min:2|regex:/^[\w\s\-]+$/',
            'last_name' => 'nullable|min:2|regex:/^[\w\s\-]+$/',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'profile_picture' => 'nullable',
            'phone_number' => 'nullable|regex:/^[\d\s\+\-]{7,15}$/'
        ];
    }
}
