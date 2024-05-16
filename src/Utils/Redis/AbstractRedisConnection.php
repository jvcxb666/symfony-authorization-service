<?php

namespace App\Utils\Redis;

use Predis\Client;
use Predis\ClientInterface;

abstract class AbstractRedisConnection
{
    protected ClientInterface $client;

    public function __construct()
    {
        $this->client = new Client("tcp://authredis:6379");
    }
}
