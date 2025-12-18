<?php

namespace App\Strategies\AuthenticationStrategy;

interface AuthStrategy
{
    public function register(array $data): array;
    public function login(array $credentials): array;
}