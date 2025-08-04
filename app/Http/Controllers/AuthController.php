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
                //Method from ValidationException
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

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credential are invalid.']
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return ResponseApiUtil::success(
                'Login successfull',
                [
                    'user' => $user,
                    'token' => $token
                ],
            );

        } catch (ValidationException $e){
            return ResponseApiUtil::error(
                'Authentication Error',
                $e->errors(),
                401
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