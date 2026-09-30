<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        if (! Auth::validate(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();

        $plain = ApiToken::createToken($user, $credentials['device_name'] ?? 'api-client');

        return response()->json([
            'token' => $plain,
            'user' => $user->only(['id', 'name', 'email']),
        ], 201);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $role = Role::where('slug', 'cashier')->first();

        abort_if(! $role, 500, 'Roles are not seeded yet. Run the seeders first.');

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => $role->id,
        ]);

        $plain = ApiToken::createToken($user, $validated['device_name'] ?? 'api-client');

        return response()->json([
            'token' => $plain,
            'user' => $user->only(['id', 'name', 'email']),
        ], 201);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'role' => $user->role?->name,
            'permissions' => $user->role?->permissions()->pluck('slug') ?? [],
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->attributes->get('api_token');

        $token?->delete();

        return response()->json(['message' => 'Logged out. Token revoked.']);
    }
}