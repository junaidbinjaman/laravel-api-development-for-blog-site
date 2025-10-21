<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRegistrationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            $fileName = 'profilePicture_' . now()->timestamp . '.' . $request->file('profile_picture')->getClientOriginalExtension();
            $url = $request->file('profile_picture')->storeAs('profile', $fileName, 'public');

            $validated_data['profile_picture'] = $url;
        }

        $user = User::query()
            ->create($validated_data);

        return response()->json([
            'status' => 'success',
            'message' => 'The user is registered successfully',
            'data' => $user
        ], 201);

    }
}
