<?php

namespace App\Service;

use App\Interface\AuthorizationServiceInterface;

abstract class AbstractAuthService
{
    protected AuthorizationServiceInterface $base;

    public function __construct(UserService $userService)
    {
        $this->base = $userService;
    }
}