<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegistrationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //

    public function register(UserRegistrationRequest $request): JsonResponse
    {
        // Hash the password
        $validated_data = $request->validated();
        $validated_data['password'] = Hash::make($request->password);

        // Save user profile picture in the public folder and store the file name in database
        if ($request->hasFile('profile_picture')) {

        }
    }
}
