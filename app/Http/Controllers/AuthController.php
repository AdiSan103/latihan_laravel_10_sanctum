<?php

// AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], 422);
            }

            if (Auth::attempt($request->only('email', 'password'))) {
                $user = User::where('email', $request->email)->first();
                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json(['token' => $token]);
            }

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during login.'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during logout.'], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            return response()->json(['user' => $request->user()]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the user profile.'], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], 422);
            }
        


            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
            // Your registration logic here...

            return response()->json(['message' => 'User registered successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during registration.'], 500);
        }
    }
}
