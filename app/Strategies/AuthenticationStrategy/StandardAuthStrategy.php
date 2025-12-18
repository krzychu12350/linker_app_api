<?php

namespace App\Strategies\AuthenticationStrategy;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StandardAuthStrategy implements AuthStrategy
{
    public function register(array $data): array
    {
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('user'); // Default role as USER

        return [
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Unauthorized');
        }

        return [
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }
}
