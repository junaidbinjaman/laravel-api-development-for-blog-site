<?php

namespace App\Http\Requests\Auth;

use AllowDynamicProperties;
use Illuminate\Foundation\Http\FormRequest;

#[AllowDynamicProperties]
class UpdateUserdataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $targetUserId = (int) $this->route('id');
        return $user && ($user->role === 'admin' || $user->id === $targetUserId);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'sometimes|email|unique:users,email',
            'password' => 'sometimes|min:6',
            'profile_picture' => 'nullable|file|max:2048|mimes:jpg,png,jpeg,webp',
            'phone_number' => 'sometimes|max_digits:13'
        ];
    }

}
