<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Utils\ApiResponseUtil;
use App\Utils\PasswordValidatorUtil;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'confirmed',
                new PasswordValidatorUtil() 
            ],
            'profile_picture' => 'nullable|string',
            'bio' => 'nullable|string',
            ]);

            $user = User::create([
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'password' => $validatedData['password'],
                'profile_picture' => $validatedData['profile_picture'] ?? null,
                'bio' => $validatedData['bio'] ?? null
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
            return ApiResponseUtil::error(
                'Validation Error',
                //Method from ValidationException
                $e->errors(),
                422
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
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

            return ApiResponseUtil::success(
                'Login successful',
                [
                    'user' => $user,
                    'token' => $token
                ],
            );

        } catch (ValidationException $e){
            return ApiResponseUtil::error(
                'Authentication Error',
                $e->errors(),
                401
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Server Error',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return ApiResponseUtil::success(
                'Logged out succesfully',
                null,
                200
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Server Error',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    // //For development only at the moment
    // public function self(Request $requesrt)
    // {
    //     try {
    //         $user = auth()->user();
    //         throw_if(!$user, Exception::class, 'Unauthenticated', 401);

    //         return ApiResponseUtil::success(
    //             'Authenticated user retrieved successfully',
    //             $user
    //         );

    //     } catch (Exception $e) {
    //         return ApiResponseUtil::error(
    //             $e->getMessage(),
    //             null,
    //             $e->getCode() ?: 500
    //         );
    //     }
    // }
}