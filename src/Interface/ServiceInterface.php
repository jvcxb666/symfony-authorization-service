<?php

namespace App\Interface;

interface ServiceInterface
{
    public function findOne(array $request): ModelInterface|null;
    public function save(array $request): ModelInterface|null;
    public function find(array $request): array|null;
    public function delete(array $request): void;
}