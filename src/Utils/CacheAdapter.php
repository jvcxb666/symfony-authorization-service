<?php

namespace App\Utils;

use Predis\Client;

class CacheAdapter
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client("tcp://authredis:6379");
    }

    public function get(string $key): array|null
    {
        $string = $this->client->get($key);
        return json_decode($string,1);
    }

    public function delete(string $key): void
    {
        $this->client->del($key);
    }

    public function save(string $key, string|array $value): void
    {
        if(is_array($value)) json_encode($value);

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