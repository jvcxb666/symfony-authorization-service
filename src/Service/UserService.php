<?php

namespace App\Service;

use App\Entity\User;
use App\Interface\AuthorizationServiceInterface;
use App\Interface\ModelInterface;
use App\Repository\UserRepository;
use App\Utils\CacheAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\SodiumPasswordHasher;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class UserService implements AuthorizationServiceInterface
{
    private const EMAIL_REGEX = "/^\S+@\S+\.\S+$/";
    private const PHONE_REGEX = "/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/";
    private EntityManagerInterface $em;
    private UserRepository $repository;
    private PasswordHasherInterface $hasher;
    private CacheAdapter $cacher;

    public function __construct(EntityManagerInterface $entityManagerInterface,CacheAdapter $cacheAdapter)
    {
        $this->hasher = new SodiumPasswordHasher();
        $this->em = $entityManagerInterface;
        $this->repository = $this->em->getRepository(User::class);
        $this->cacher = $cacheAdapter;
    }

    public function login(array $request): bool|string
    {
        $key = "user_login_".json_encode($request);
        if(!empty($this->cacher->get($key))) return boolval($this->cacher->get($key));
        $result = false;
        $user = $this->repository->findOneByAnyCredit($request);
        if(!empty($user)) $result = $this->hasher->verify($user['password'],$request['password']);
        $this->cacher->save($key,strval($result));
        return $result;
    }

    public function find(array $request): array|null
    {
        $key = "user_find_".(!empty($request) ? json_encode($request) : "all");
        if(!empty($this->cacher->get($key))) return $this->cacher->get($key);
        $result = $this->repository->findBy($request);
        $this->cacher->save($key,$result);
        return $result;
    }

    public function findOne(array $request): ModelInterface|null
    {
        $key = "user_get_".json_encode($request);
        if(!empty($this->cacher->get($key))) return $this->cacher->get($key,User::class);
        $result = $this->repository->findOneBy($request);
        $this->cacher->save($key,$result);
        return $result;
    }

    public function delete(array $request): void
    {
        if(!empty($request['id'])) {
            $this->repository->deleteById($request['id']);
            $this->cacher->deleteByParts(["user_",$request['id']]);
            $this->cacher->deleteByParts(["user_login"]);
        }else{
            throw new Exception("Missing required id");
        }
    }

    public function save(array $request): ModelInterface|null
    {
        $this->validateSaveData($request);

        if(!empty($request['id'])) {
            $model = $this->repository->find($request['id']);
        }else{
            $model = new User();
        }

        if(!empty($request['first_name'])) $model->setFirstName($request['first_name']);
        if(!empty($request['last_name'])) $model->setLastName($request['last_name']);
        if(!empty($request['middle_name'])) $model->setMiddleName($request['middle_name']);
        if(!empty($request['email'])) $model->setEmail($request['email']);
        if(!empty($request['phone'])) $model->setPhone($request['phone']);
        if(!empty($request['username'])) $model->setUsername($request['username']);
        if(!empty($request['password'])) $model->setPassword($this->hasher->hash($request['password']));

        $this->em->persist($model);
        $this->em->flush();
        $this->cacher->deleteByParts(["user_",$model->getId()]);
        $this->cacher->deleteByParts(["user_login"]);

        return $model;
    }

    private function validateSaveData(array &$data): void
    {
        if(!empty($data['email']) && !preg_match(static::EMAIL_REGEX,$data['email'])) throw new Exception("Invalid email address");
        if(!empty($data['phone'])) {
            $data['phone'] = preg_replace("/[^\d.]/","",$data['phone']);
            if(!preg_match(static::PHONE_REGEX,$data['phone'])) throw new Exception("Invalid phone number");
        }
        if(!empty($data['username'])) {
            $check = $this->repository->findOneBy(["username"=>$data['username']]);
            if(!empty($check) && $check->getId() != $data['id'] ?? 0) throw new Exception("Username is already taken");
        } 
        if(!empty($data['email'])) {
            $check = $this->repository->findOneBy(["email"=>$data['email']]);
            if(!empty($check) && $check->getId() != $data['id'] ?? 0) throw new Exception("Email is already taken");
        }
        if(!empty($data['phone'])) {
            $check = $this->repository->findOneBy(["phone"=>$data['phone']]);
            if(!empty($check) && $check->getId() != $data['id'] ?? 0) throw new Exception("Phone is already taken");
        }
        if(empty($data['id']) && (empty($data['password']) && empty($data['username']))) throw new Exception("Missing required fields");
    }
}