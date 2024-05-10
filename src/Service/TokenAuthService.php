<?php

namespace App\Service;

use App\Entity\Token;
use App\Entity\User;
use App\Interface\ModelInterface;
use App\Interface\TokenServiceInterface;
use App\Repository\TokenRepository;
use App\Service\AbstractAuthService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenAuthService extends AbstractAuthService implements TokenServiceInterface
{
    private const ALOGORITHM = "HS256";
    private EntityManagerInterface $em;
    private TokenRepository $repository;

    public function __construct(EntityManagerInterface $entityManagerInterface, UserService $userService)
    {
        $this->em = $entityManagerInterface;
        $this->repository = $this->em->getRepository(Token::class);
        parent::__construct($userService);
    }

    public function createOrRefreshToken(array $request): ModelInterface|null
    {
        if(!empty($request['username']) && !empty($request['password']))
        {
            if($this->base->login($request))
            {
                $user_id = $this->em->getRepository(User::class)->findOneByAnyCredit($request)['id'];
                return $this->createToken($user_id);
            }else{
                throw new Exception("Wrong authorization credits");
            }
        } else if(!empty($request['refresh'])) {
            return $this->refreshToken($request);
        } else {
            throw new Exception("Missing required fields");
        }

        return [];
    }

    public function dropToken(string|null $token): void
    {
        $token = str_replace("Bearer ","",$token ?? "");
        $token = $this->repository->findOneBy(['value'=>$token]);
        if(empty($token)) return;
        $this->validateToken($token);
        $this->em->remove($token);
        $this->em->flush();
    }

    public function checkToken(string|null $token): bool
    {
        $token = str_replace("Bearer ","",$token ?? "");
        $token = $this->repository->findOneBy(['value'=>$token]);
        if(empty($token)) throw new Exception("Bad token");
        $this->validateToken($token);
        if($token->getExpired()->format("Y-m-d H:i:s") < date("Y-m-d H:i:s")) throw new Exception("Token is expired");

        return true;
    }

    private function createToken(string $user_id): Token
    {
        $token = new Token();
        
        $token->setUserId($user_id);
        $this->createTokens($token);
        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    private function refreshToken(array $request): Token
    {
        $token = $this->repository->findOneBy(['refresh'=>$request['refresh']]);
        if(empty($token)) throw new Exception("Token is not found");

        $this->validateToken($token);

        if($token->getRefreshExpired()->format("Y-m-d H:i:s") < date("Y-m-d H:i:s")) {
            $this->em->remove($token);
            $this->em->flush();
            throw new Exception("Token refresh is expired");
        }

        $this->createTokens($token);
        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    private function createTokens(Token &$token): void
    {
        $user_id = $token->getUserId();
        $token->setValue(JWT::encode(["type=access","user_id"=>$user_id],"user_{$user_id}",static::ALOGORITHM));
        $token->setRefresh(JWT::encode(["type"=>"refresh","user_id"=>$user_id],"refresh_{$user_id}",static::ALOGORITHM));
        $token->setCreated(new DateTime());
        $token->setExpired((new DateTime())->modify("+1 days"));
        $token->setRefreshExpired((new DateTime())->modify("+2 days"));
    }

    private function validateToken(Token $token): void
    {
        $access_key = "user_".$token->getUserId();
        $refresh_key = "refresh_".$token->getUserId();
        
        try{
            JWT::decode($token->getValue(),new Key($access_key,static::ALOGORITHM));
            JWT::decode($token->getRefresh(),new Key($refresh_key,static::ALOGORITHM));
        } catch (Exception $e) {
            $this->em->remove($token);
            $this->em->flush();
            throw new Exception("Invalid token");
        }
    }
}