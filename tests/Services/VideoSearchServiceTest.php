<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Models\Video;
use App\Services\VideoSearchService;
use PHPUnit\Framework\TestCase;

class VideoSearchServiceTest extends TestCase
{
    private VideoSearchService $service;

    protected function setUp(): void
    {
        $this->service = new VideoSearchService();
    }

    public function testIndexAndSearchVideo(): void
    {
        $video = new Video(1, 'How to Create a Website', 'https://example.com/website');

        $this->service->indexVideo($video);

        // Give ES a moment to index
        sleep(1);

        $results = $this->service->search('website');

        $this->assertNotEmpty($results);
        $this->assertEquals('How to Create a Website', $results[0]['title']);
        $this->assertEquals('https://example.com/website', $results[0]['url']);
        $this->assertEquals(1, (int)$results[0]['id']);
    }

    public function testSearchReturnsEmptyIfNoMatch(): void
    {
        $results = $this->service->search('nonexistent123');
        $this->assertEmpty($results);
    }
}
