<?php

namespace App\Controller;

use App\Interface\TokenServiceInterface;
use App\Service\TokenAuthService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TokenController extends AbstractController
{
    private TokenServiceInterface $service;

    public function __construct(TokenAuthService $tokenAuthService)
    {
        $this->service = $tokenAuthService;
    }

    #[Route('/', name: 'app_index')]
    public function index(): JsonResponse
    {
        return $this->json("Welcome to auth service!");
    }

    #[Route('v1/token/', name: 'app_getToken', methods: "POST")]
    public function getToken(Request $request): JsonResponse
    {
        try{
            $result = $this->service->createOrRefreshToken($request->get("data"));
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
        }
        
        return $this->json($result);
    }

    #[Route('v1/token/', name: 'app_dropToken', methods: "DELETE")]
    public function dropToken(Request $request): JsonResponse
    {
        try{
            $this->service->dropToken($request->headers->get("Authorization"));
            $result = true;
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
        }
        
        return $this->json($result);
    }

    #[Route('v1/token/check/', name: 'app_checkToken', methods: "POST")]
    public function checkToken(Request $request): JsonResponse
    {
        try{
            $result = $this->service->checkToken($request->headers->get("Authorization"));
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
        }
        
        return $this->json($result);
    }
}
