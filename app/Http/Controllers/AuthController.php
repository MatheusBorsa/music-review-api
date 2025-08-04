<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_picture' => 'nullable|string',
            'bio' => 'nullable|string',
            ]);

            $user = User::create([
                'username' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => $validatedData['password'],
                'profile_picture' => $validatedData['profile_picture'],
                'bio' => $validatedData['bio']
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponseUtil::success(
                'User Registered Successfully',
                [
                    'user' => $user,
                    'token' => $token
                ],
                201
            );

        } catch (ValidationException $e) {
            return ResponseApiUtil::error(
                'Validation Error',
                $e->errors(),
                422
            );

        } catch (Exception $e) {
            return ResponseApiUtil::error(
                'Server Error',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}