<?php

namespace App\Strategies\AuthenticationStrategy;

class AuthContext
{
    private AuthStrategy $strategy;

    /**
     * Set the authentication strategy.
     *
     * @param AuthStrategy $strategy
     */
    public function setStrategy(AuthStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Delegate the register process to the current strategy.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        return $this->strategy->register($data);
    }

    /**
     * Delegate the login process to the current strategy.
     *
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials): array
    {
        return $this->strategy->login($credentials);
    }
}
