<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,doctor,patient'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        if ($request->role === 'doctor') {
            $user->doctor()->create([
                'specialization' => $request->specialization,
                'department' => $request->department
            ]);
        } elseif ($request->role === 'patient') {
            $user->patient()->create([
                'medical_history' => $request->medical_history,
                'emergency_contact' => $request->emergency_contact
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        // Log::debug('Login request received', $request->all());

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Delete any existing tokens for this user
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        try {
            // Delete any existing tokens for this user
            Auth::guard('sanctum')->user()->tokens()->delete();
            Auth::guard('sanctum')->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out successfully']);
        } catch (Exception $e) {
            Log::debug('hospitalsystem: Logout: Caught exception: ', $e->getMessage());
        }
        
        return response()->json(['message' => 'Error: Failed to Log out']);
    }
}