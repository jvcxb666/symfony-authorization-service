<?php

namespace App\Interface;

interface AuthorizationServiceInterface extends ServiceInterface
{
    public function login(): bool|string;
    public function logout(): void;
}