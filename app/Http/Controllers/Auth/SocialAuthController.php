<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\SocialLoginRequest;
use App\Http\Requests\Auth\SocialRegisterRequest;
use App\Strategies\AuthenticationStrategy\AuthStrategy;
use App\Strategies\AuthenticationStrategy\SocialAuthStrategy;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SocialAuthController extends Controller
{
    protected AuthStrategy $authStrategy;

    // Constructor to inject the SocialAuthStrategy
    public function __construct()
    {
        $this->authStrategy = new SocialAuthStrategy();
    }

    public function login(SocialLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        try {
            $result = $this->authStrategy->login($credentials);

            return response()->json([
                'message' => 'Logged in successfully',
                'data' => ['user' => $result['user'], 'token' => $result['token']],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function register(SocialRegisterRequest $request)
    {
        $credentials = $request->validated();
//
//       dd($credentials);
        try {
            $result = $this->authStrategy->register($credentials);

            // If registration was successful, return a token
            return response()->json([
                'message' => 'User registered successfully',
                'data' => ['token' => $result['token']],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
