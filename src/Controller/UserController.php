<?php

namespace App\Controller;

use App\Interface\TokenServiceInterface;
use App\Service\TokenAuthService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private TokenServiceInterface $service;

    public function __construct(TokenAuthService $tokenAuthService)
    {
        $this->service = $tokenAuthService;
    }

    #[Route('v1/user/', name: 'app_getUsersList', methods: "GET")]
    public function getList(Request $request): JsonResponse
    {
        try{
            $this->service->checkToken($request->headers->get("Authorization"));
            $result = $this->service->getUserService()->find($request->get("data") ?? []);
            $status_code = 200;
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
            $status_code = $e->getCode() == 401 ? $e->getCode() : 200;
        }
        
        return $this->json($result,$status_code);
    }

    #[Route('v1/user/{id}', name: 'app_getUser', methods: "GET")]
    public function getUserById(Request $request,string $id): JsonResponse
    {
        try{
            $this->service->checkToken($request->headers->get("Authorization") ?? "");
            $result = $this->service->getUserService()->findOne(["id"=>$id]);
            $status_code = 200;
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
            $status_code = $e->getCode() == 401 ? $e->getCode() : 200;
        }
        
        return $this->json($result,$status_code);
    }

    #[Route('v1/user/', name: 'app_createOrUpdateUser', methods: "POST")]
    public function saveUser(Request $request): JsonResponse
    {
        try{
            if(!empty($request->get("data")['id']) )$this->service->checkToken($request->headers->get("Authorization"));
            $result = $this->service->getUserService()->save($request->get("data") ?? []);
            $status_code = 200;
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
            $status_code = $e->getCode() == 401 ? $e->getCode() : 200;
        }
        
        return $this->json($result,$status_code);
    }

    #[Route('v1/user/', name: 'app_deleteUser', methods: "DELETE")]
    public function delete(Request $request): JsonResponse
    {
        try{
            $this->service->checkToken($request->headers->get("Authorization"));
            $this->service->getUserService()->delete($request->get("data") ?? []);
            $result = true;
            $status_code = 200;
        } catch(Exception $e) {
            $result = ["error" => $e->getMessage()];
            $status_code = $e->getCode() == 401 ? $e->getCode() : 200;
        }
        
        return $this->json($result,$status_code);
    }
}