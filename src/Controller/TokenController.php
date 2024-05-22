<?php

namespace App\Controller;

use App\Interface\TokenServiceInterface;
use App\Service\TokenAuthService;
use App\Utils\Redis\QueueAdapter;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TokenController extends AbstractController
{
    private TokenServiceInterface $service;
    private QueueAdapter $queue;

    public function __construct(TokenAuthService $tokenAuthService)
    {
        $this->service = $tokenAuthService;
        $this->queue = new QueueAdapter();
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
            $result = $this->service->createOrRefreshToken($request->get("data") ?? []);
            $status_code = 200;
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
            $status_code = $e->getCode() == 401 ? $e->getCode() : 200;
        }
        
        return $this->json($result,$status_code);
    }

    #[Route('v1/token/', name: 'app_dropToken', methods: "DELETE")]
    public function dropToken(Request $request): JsonResponse
    {
        try{
            $this->service->dropToken($request->headers->get("Authorization") ?? "");
            $status_code = 200;
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
            $status_code = $e->getCode() == 401 ? $e->getCode() : 200;
        }
        
        return $this->json($result,$status_code);
    }

    #[Route('v1/token/check/', name: 'app_checkToken', methods: "POST")]
    public function checkToken(Request $request): JsonResponse
    {
        try{
            $result = $this->service->checkToken($request->headers->get("Authorization") ?? "");
            $status_code = 200;
            $this->queue->push(["action"=>"bash","content"=>"php bin/console tokens:clear expired"]);
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
            $status_code = $e->getCode() == 401 ? $e->getCode() : 200;
        }
        
        return $this->json($result,$status_code);
    }
}
