<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private function sanitizeInput($input){
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return strip_tags(trim($input));
    }

    public function register(Request $request){
        $this->checkRateLimit('register', $request);

        try {
            $input = $this->sanitizeInput($request->all());
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'role' => 'User',
                'status' => 'ACTIVE',
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil.',
                'user' => $user,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $this->checkRateLimit('login', $request);

        try {
            $input = $this->sanitizeInput($request->all());
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            $user = User::where('email', $input['email'])->first();
            if (!$user || !Hash::check($input['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah.',
                ], 401);
            }
            $token = $user->createToken('api_token')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'user' => $user,
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out']);
        }

        return response()->json(['message' => 'User not authenticated'], 401);
    }
    private function checkRateLimit($action, Request $request)
    {
        $key = Str::lower($action) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'error' => ['Terlalu banyak percobaan. Silakan coba lagi nanti.'],
            ])->status(429);
        }
        RateLimiter::hit($key, 60); 
    }
}
