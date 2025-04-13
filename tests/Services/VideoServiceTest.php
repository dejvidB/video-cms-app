<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Database;
use App\Models\Video;
use App\Queue\QueuePublisher;
use App\Repositories\VideoRepository;
use App\Services\VideoSearchService;
use App\Services\VideoService;
use App\Services\ViewTrackerService;
use PHPUnit\Framework\TestCase;

class VideoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        Database::getConnection()->exec('TRUNCATE TABLE `videos`');
    }

    public function testGetAllVideos(): void
    {
        (new VideoRepository())->create('Video 1', 'https://youtube.com/watch?v=video-1');
        (new VideoRepository())->create('Video 2', 'https://youtube.com/watch?v=video-2');

        $videos = (new VideoService(queuePublisher: $this->createMock(QueuePublisher::class)))->getAllVideos();
        $this->assertCount(2, $videos);
        $this->assertContainsOnlyInstancesOf(Video::class, $videos);
    }

    public function testCreateVideo(): void
    {
        $searchService = $this->createMock(VideoSearchService::class);
        $searchService
            ->expects($this->once())
            ->method('indexVideo')
            ->with($this->callback(fn($arg) => $arg instanceof Video));

        $queueMock = $this->createMock(QueuePublisher::class);

        $queueMock
            ->expects($this->once())
            ->method('publish')
            ->with(
                'video_jobs',
                $this->callback(function (array $payload) {
                    return isset($payload['video_id']) && is_int($payload['video_id']);
                })
            );

        $service = new VideoService(
            searchService: $searchService,
            queuePublisher: $queueMock
        );

        $video = $service->createVideo('Test Video', 'https://example.com');

        $this->assertInstanceOf(Video::class, $video);
        $this->assertGreaterThan(0, $video->getId());
    }

    public function testGetVideoById(): void
    {
        $service = new VideoService(
            queuePublisher: $this->createMock(QueuePublisher::class),
        );

        $video = $service->createVideo('Test Video', 'https://example.com');

        $found = $service->getVideoById($video->getId());

        $this->assertInstanceOf(Video::class, $found);
        $this->assertEquals($found->getId(), $video->getId());

        $this->assertNull($service->getVideoById(-1));
    }

    public function testGetTrendingVideos(): void
    {
        $video1 = new Video(1, 'One', 'https://youtube.com/watch?v=video-1');
        $video2 = new Video(2, 'Two', 'https://youtube.com/watch?v=video-2');

        $repoMock = $this->createMock(VideoRepository::class);
        $repoMock->method('findById')
                 ->willReturnMap(
                     [
                         [1, $video1],
                         [2, $video2],
                     ]
                 );

        $trackerMock = $this->createMock(ViewTrackerService::class);

        $trackerMock->expects($this->once())
                    ->method('getTopViewedIds')
                    ->with(2)
                    ->willReturn([2, 1]);

        $trackerMock->method('getViewCount')
                    ->willReturnMap(
                        [
                            [1, 5],
                            [2, 10],
                        ]
                    );

        $service = new VideoService(
            $repoMock,
            $trackerMock,
            $this->createMock(VideoSearchService::class),
            $this->createMock(QueuePublisher::class)
        );

        $result = $service->getTrendingVideos(2);

        $this->assertCount(2, $result);
        $this->assertEquals(2, $result[0]->getId());
        $this->assertEquals(10, $result[0]->getViews());
        $this->assertEquals(1, $result[1]->getId());
        $this->assertEquals(5, $result[1]->getViews());
    }
}
