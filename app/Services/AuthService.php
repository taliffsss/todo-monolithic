<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials): array
    {
        // Check if the email exists first
        $user = User::where('email', $credentials['email'])->first();

        // Return a 404 error if the email does not exist
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No account found with the provided email address.'],
            ])->status(404);
        }

        // Attempt to log in with the provided credentials
        if (! Auth::attempt($credentials)) {
            // Return a 422 error if the credentials are incorrect
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate a token if authentication is successful
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function guest()
    {
        $guestUser = User::create([
            'name' => 'Guest_'.Str::random(8),
            'email' => 'guest_'.Str::random(8).'@example.com',
            'password' => Str::random(20),
            'is_guest' => true,
        ]);

        $token = $guestUser->createToken('guest-token')->plainTextToken;

        return [
            'user' => $guestUser,
            'token' => $token,
        ];
    }

    public function logout(): void
    {
        Auth::user()->currentAccessToken()->delete();
    }
}
