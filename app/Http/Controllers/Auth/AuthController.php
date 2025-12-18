<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BanType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Http\Requests\Auth\SocialRegisterRequest;
use App\Models\User;
use App\Strategies\AuthenticationStrategy\AuthStrategy;
use App\Strategies\AuthenticationStrategy\StandardAuthStrategy;
use App\Strategies\AuthenticationStrategy\SocialAuthStrategy;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthStrategy $authStrategy;

    /**
     * Inject a default strategy (StandardAuthStrategy).
     */
    public function __construct(AuthStrategy $authStrategy = null)
    {
        $this->authStrategy = $authStrategy ?? new StandardAuthStrategy();
    }

    /**
     * Dynamically set the authentication strategy.
     */
    protected function setStrategy(array $data): void
    {
        if (isset($data['provider'])) {
            $this->authStrategy = new SocialAuthStrategy();
        } else {
            $this->authStrategy = new StandardAuthStrategy();
        }
    }

    /**
     * Resolve the appropriate request class for registration.
     */
    protected function resolveRegisterRequest(Request $request)
    {
        return isset($request->provider)
            ? app(SocialRegisterRequest::class)
            : app(RegisterRequest::class);
    }

    /**
     * Resolve the appropriate request class for login.
     */
    protected function resolveLoginRequest(Request $request)
    {
        return isset($request->provider)
            ? app(SocialLoginRequest::class)
            : app(LoginRequest::class);
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $formRequest = $this->resolveRegisterRequest($request);
        $data = $formRequest->validated();
        $this->setStrategy($data);

        try {
            $result = $this->authStrategy->register($data);
            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $formRequest = $this->resolveLoginRequest($request);
        $credentials = $formRequest->validated();

        $this->setStrategy($credentials);

        try {
            $result = $this->authStrategy->login($credentials);
            // Use the findByEmail method to find the user
            $user = $result['user'];

            if ($user) {
                // Check if the user is banned
                $banType = $user->banType();

                // If the user is banned, return a 403 response
                if ($banType === BanType::PERMANENT) {
                    return response()->json(
                        [
                            'message' => 'Your account is permanently banned.',
                            'data' => [
                                'ban_type' => $banType,
                            ]
                        ],
                        403);
                }

                if ($banType === BanType::TEMPORARY) {
                    return response()->json([
                        'message' => 'Your account is temporarily banned until ' . $user->banned_until . '.',
                        'data' => [
                            'ban_type' => $banType,
                            'banned_until' => $user->banned_until
                        ]
                    ], 403);
                }
            }

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }
}
