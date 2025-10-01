<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UserRegistrationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\json;

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

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid credentials',
                'data' => [
                    'email' => $request->email,
                ],
            ], 401);
        }

        $user = Auth::user();
        $loginToken = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'The user has logged-in successfully',
            'data' => $user,
            'login_token' => $loginToken,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'The user has been logged out successfully'
        ], 200);
    }

    public function getUserData(Request $request, int $id)
    {
        $authenticated_user = Auth::user();

        if ($authenticated_user->id !== $id && $request->user()->role !== 'admin') {
            return response()->json([
                'status' => 'fail',
                'message' => 'You are not authorized to do this action',
                'data' => []
            ], 403);
        }

        $user = User::query()->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No user found matching your provided user id',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User data retrieved successfully',
            'data' => $user
        ], 200);
    }
}
