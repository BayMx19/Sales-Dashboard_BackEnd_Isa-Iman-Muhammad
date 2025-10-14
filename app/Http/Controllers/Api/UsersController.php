<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index(){
        $users = User::orderBy('id', 'desc')->get();
        return response()->json($users);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => ['required', Rule::in(['Admin', 'User'])],
            'status'   => 'nullable|string',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'status'   => $validated['status'] ?? 'ACTIVE',
        ]);

        return response()->json([
            'message' => 'User berhasil ditambahkan',
            'data'    => $user
        ], 201);
    }

    public function show($id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return response()->json($user);
    }

    public function update(Request $request, $id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:6',
            'role'     => ['sometimes', 'required', Rule::in(['Admin', 'User'])],
            'status'   => 'nullable|string',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'data'    => $user
        ]);
    }

    public function destroy($id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}
