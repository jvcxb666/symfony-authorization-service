<?php

namespace App\Interface;

interface AuthorizationServiceInterface extends ServiceInterface
{
    public function login(array $request): bool|string;
}