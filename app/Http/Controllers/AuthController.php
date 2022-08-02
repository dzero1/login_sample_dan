<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request) {

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Set default role
        $user->assignRole($_ENV['USER_DEFAULT_ROLE']);

        // Set api token
        $token = $user->createToken('app-token')->plainTextToken;

        return ['user' => $user, 'id' => $user->id, 'token' => $token];
    }

    public function login(Request $request) {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)){
            return response([
                'message' => "Invalid credentials"
            ], 401);
        }

        $token = $user->createToken('app-token')->plainTextToken;
        
        return ['user' => $user, 'id' => $user->id, 'token' => $token, 'roles' => $user->getRoleNames()];
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return ['success' => true];
    }
}
