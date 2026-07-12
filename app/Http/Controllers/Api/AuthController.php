<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nic' => 'required|integer|unique:users,nic',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:191|unique:users,email',
            'pno' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'bill_no' => 'required|string|max:50',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'nic' => $request->nic,
                'name' => $request->name,
                'email' => $request->email,
                'pno' => $request->pno,
                'address' => $request->address,
                'bill_no' => $request->bill_no,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'nic' => $user->nic,
                    'name' => $user->name,
                    'email' => $user->email,
                    'pno' => $user->pno,
                    'address' => $user->address,
                    'bill_no' => $user->bill_no,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nic' => 'required|integer',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('nic', $request->nic)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Generate a simple token (you can use Laravel Sanctum or Passport for better token management)
            $token = $user->createToken('api-token')->plainTextToken ?? null;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'nic' => $user->nic,
                    'name' => $user->name,
                    'email' => $user->email,
                    'pno' => $user->pno,
                    'address' => $user->address,
                    'bill_no' => $user->bill_no,
                    'token' => $token
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            // Revoke the token
            if ($request->user()) {
                $request->user()->tokens()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'nic' => $user->nic,
                    'name' => $user->name,
                    'email' => $user->email,
                    'pno' => $user->pno,
                    'address' => $user->address,
                    'bill_no' => $user->bill_no,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
