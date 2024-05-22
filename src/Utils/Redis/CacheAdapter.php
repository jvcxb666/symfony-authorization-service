<?php

namespace App\Utils\Redis;

use Symfony\Component\Serializer\SerializerInterface;

class CacheAdapter extends AbstractRedisConnection
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializerInterface)
    {
        parent::__construct();
        $this->serializer = $serializerInterface;
    }

    public function get(string $key, string $type = null): mixed
    {
        $string = $this->client->get($key);
        return empty($type) ? json_decode($string,1) : $this->serializer->deserialize($string,$type,"json");
    }

    public function delete(string $key): void
    {
        $this->client->del($key);
    }

    public function save(string $key, mixed $value): void
    {
        $value = $this->serializer->serialize($value,"json");

        $this->client->set($key,$value);
    }

    public function deleteByParts(array $parts): void
    {
        foreach($this->client->keys("*") as $key) {
            $delete = true;
            foreach($parts as $part) {
                if(!str_contains($key,$part)){
                    $delete = false;
                    break;
                }
            }
            if($delete) $this->client->del($key);
        }
    }
}