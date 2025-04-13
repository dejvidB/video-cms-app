<?php

declare(strict_types=1);

namespace App;

use Redis;
use RedisException;

class RedisClient
{
    private static ?Redis $redis = null;

    /**
     * @throws RedisException
     */
    public static function getConnection(): Redis
    {
        if (self::$redis === null) {
            self::$redis = new Redis();
            self::$redis->connect($_ENV['REDIS_HOST'], (int)$_ENV['REDIS_PORT']);
        }

        return self::$redis;
    }
}
