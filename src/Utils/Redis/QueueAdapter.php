<?php

namespace App\Utils\Redis;

class QueueAdapter extends AbstractRedisConnection
{
    public function push(array|string $content): void
    {
        $this->client->lpush("default",json_encode($content));
    }

    public function consume(string $queue = "default"): array|null
    {
        $consumed = $this->client->lpop($queue);
        return !empty($consumed) ? json_decode($consumed,1) : null;
    }
}