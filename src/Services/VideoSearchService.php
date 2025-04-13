<?php

declare(strict_types=1);

namespace App\Services;

use App\ElasticClient;
use App\Models\Video;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class VideoSearchService
{
    private Client $client;
    private const INDEX = 'videos';

    public function __construct()
    {
        $this->client = ElasticClient::getClient();
    }

    public function indexVideo(Video $video): void
    {
        $this->client->index(
            [
                'index' => self::INDEX,
                'id' => (string)$video->getId(),
                'body' => [
                    'title' => $video->getTitle(),
                    'url' => $video->getUrl(),
                ]
            ]
        );
    }

    /**
     * @param string $query
     * @return Video[]
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function search(string $query): array
    {
        $response = $this->client->search(
            [
                'index' => self::INDEX,
                'body' => [
                    'query' => [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => [
                                'title',
                                'url',
                            ]
                        ]
                    ]
                ]
            ]
        );

        $results = [];

        foreach ($response['hits']['hits'] as $hit) {
            $source = $hit['_source'];
            $source['id'] = $hit['_id'];
            $results[] = $source;
        }

        return $results;
    }
}
