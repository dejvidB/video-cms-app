<?php

declare(strict_types=1);

namespace App;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;

class ElasticClient
{
    private static ?Client $client = null;

    /**
     * @throws AuthenticationException
     */
    public static function getClient(): Client
    {
        if (self::$client === null) {
            self::$client = ClientBuilder::create()->setHosts([$_ENV['ELASTIC_HOST']])->build();
        }

        return self::$client;
    }
}
