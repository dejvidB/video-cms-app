<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Database;
use App\Models\Video;
use App\Repositories\VideoRepository;
use PHPUnit\Framework\TestCase;

class VideoRepositoryTest extends TestCase
{
    private VideoRepository $videoRepository;

    protected function setUp(): void
    {
        $this->videoRepository = new VideoRepository();
        Database::getConnection()->exec('TRUNCATE TABLE `videos`');
    }

    public function testFindAllReturnsVideosInDescendingOrder(): void
    {
        $this->videoRepository->create('First', 'https://first.com');
        sleep(1);
        $this->videoRepository->create('Second', 'https://second.com');

        $videos = $this->videoRepository->findAll();

        $this->assertCount(2, $videos);
        $this->assertEquals('Second', $videos[0]->getTitle());
        $this->assertEquals('First', $videos[1]->getTitle());

        $this->assertEquals('https://second.com', $videos[0]->getUrl());
        $this->assertEquals('https://first.com', $videos[1]->getUrl());
    }

    public function testCreateAndFindById(): void
    {
        $video = $this->videoRepository->create('Cool Video', 'https://example.com');

        $this->assertInstanceOf(Video::class, $video);
        $this->assertGreaterThan(0, $video->getId());

        $found = $this->videoRepository->findById($video->getId());

        $this->assertNotNull($found);
        $this->assertEquals('Cool Video', $found->getTitle());
        $this->assertEquals('https://example.com', $found->getUrl());
    }

    public function testFindByIdReturnsNullIfNotFound(): void
    {
        $result = $this->videoRepository->findById(99999);

        $this->assertNull($result);
    }
}
