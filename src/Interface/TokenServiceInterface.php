<?php

namespace App\Interface;

interface TokenServiceInterface
{
    public function createOrRefreshToken(array $request): ModelInterface|null;
    public function checkToken(string|null $token): bool;
    public function dropToken(string|null $token): void;
    public function getUserService(): AuthorizationServiceInterface;
}