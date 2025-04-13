<?php

declare(strict_types=1);

namespace App\Services;

use App\RedisClient;
use Redis;
use RedisException;

class ViewTrackerService
{
    private Redis $redis;

    public function __construct()
    {
        $this->redis = RedisClient::getConnection();
    }

    public function trackView(int $videoId): void
    {
        $this->redis->incr("views:video:$videoId");
    }

    /**
     * @return int[]
     * @throws RedisException
     */
    public function getTopViewedIds(int $limit): array
    {
        $keys = $this->redis->keys('views:video:*');

        $viewCounts = [];

        foreach ($keys as $key) {
            $id = (int)str_replace('views:video:', '', $key);
            $viewCounts[$id] = (int)$this->redis->get($key);
        }

        arsort($viewCounts);

        return array_slice(array_keys($viewCounts), 0, $limit);
    }

    public function getViewCount(int $videoId): int
    {
        return (int)$this->redis->get("views:video:$videoId");
    }
}
