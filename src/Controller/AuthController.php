<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): JsonResponse
    {
        return $this->json("Welcome to auth service!");
    }
}
