<?php

declare(strict_types=1);

namespace Tests\Controllers;

use App\Controllers\VideoController;
use App\Http\Request;
use App\Http\Response;
use App\Models\Video;
use App\Services\VideoSearchService;
use App\Services\VideoService;
use App\Services\ViewTrackerService;
use PHPUnit\Framework\TestCase;

class VideoControllerTest extends TestCase
{
    private VideoService $videoService;
    private ViewTrackerService $viewTracker;
    private VideoSearchService $searchService;
    private VideoController $controller;

    protected function setUp(): void
    {
        $this->videoService = $this->createMock(VideoService::class);
        $this->viewTracker = $this->createMock(ViewTrackerService::class);
        $this->searchService = $this->createMock(VideoSearchService::class);

        $this->controller = new VideoController(
            service:            $this->videoService,
            viewTrackerService: $this->viewTracker,
            searchService:      $this->searchService
        );
    }

    public function testIndexReturnsVideos(): void
    {
        $this->videoService->method('getAllVideos')->willReturn(
            [
                new Video(1, 'Test Video', 'http://url', 'ready', '2025-04-14'),
                new Video(2, 'Test Video 2', 'https://url', 'pending', '2025-04-15'),
            ]
        );

        $response = $this->controller->index();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals(
            [
                'videos' => [
                    [
                        'id' => 1,
                        'title' => 'Test Video',
                        'url' => 'http://url',
                        'created_at' => '2025-04-14',
                        'status' => 'ready',
                        'views' => null,
                    ],
                    [
                        'id' => 2,
                        'title' => 'Test Video 2',
                        'url' => 'https://url',
                        'created_at' => '2025-04-15',
                        'status' => 'pending',
                        'views' => null,
                    ],
                ]
            ],
            $response->getData()
        );
    }

    public function testStoreReturnsVideoOnValidInput(): void
    {
        $this->videoService->method('createVideo')->willReturn(
            new Video(1, 'Test Video', 'https://url', 'pending', '2025-04-14'),
        );

        $response = $this->controller->store(Request::fromArray(['title' => 'Test Video', 'url' => 'http://url']));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals(
            [
                'id' => 1,
                'title' => 'Test Video',
                'url' => 'https://url',
                'created_at' => '2025-04-14',
                'status' => 'pending',
                'views' => null,
            ],
            $response->getData()
        );
    }

    public function testStoreReturnsErrorWhenTitleMissing(): void
    {
        $request = Request::fromArray(
            [
                'url' => 'https://example.com/video.mp4',
            ]
        );

        $response = $this->controller->store($request);

        $this->assertEquals(400, $response->getStatus());
        $this->assertEquals(['error' => 'Missing required field: title'], $response->getData());
    }

    public function testStoreReturnsErrorWhenURLMissing(): void
    {
        $request = Request::fromArray(
            [
                'title' => 'Video',
            ]
        );

        $response = $this->controller->store($request);

        $this->assertEquals(400, $response->getStatus());
        $this->assertEquals(['error' => 'Missing required field: url'], $response->getData());
    }

    public function testShowReturnsVideo(): void
    {
        $video = new Video(1, 'Test Video', 'https://example.com', 'ready', '2025-04-14');

        $this->videoService->method('getVideoById')->with(1)->willReturn($video);
        $this->viewTracker->expects($this->once())->method('trackView')->with(1);

        $response = $this->controller->show(1);

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals($video->toArray(), $response->getData());
    }

    public function testShowReturns404WhenVideoNotFound(): void
    {
        $this->videoService->method('getVideoById')->with(999)->willReturn(null);
        $response = $this->controller->show(999);

        $this->assertEquals(404, $response->getStatus());
        $this->assertEquals(['error' => 'Video not found'], $response->getData());
    }

    public function testTrendingReturnsTrendingVideos(): void
    {
        $videos = [
            new Video(1, 'Popular 1', 'url1', 'ready', '2025-04-14'),
            new Video(2, 'Popular 2', 'url2', 'ready', '2025-04-15'),
        ];

        $this->videoService->expects($this->once())
                           ->method('getTrendingVideos')
                           ->with(5)
                           ->willReturn($videos);

        $response = $this->controller->trending();

        $this->assertEquals(200, $response->getStatus());

        $expected = [
            'videos' => array_map(fn($v) => $v->toArray(), $videos)
        ];

        $this->assertEquals($expected, $response->getData());
    }

    public function testSearchReturnsResultsWhenQueryIsPresent(): void
    {
        $query = 'php';

        $expectedResults = [
            ['id' => 1, 'title' => 'PHP 101', 'url' => 'https://example.com/video1'],
            ['id' => 2, 'title' => 'Advanced PHP', 'url' => 'https://example.com/video2'],
        ];

        $this->searchService->expects($this->once())
                          ->method('search')
                          ->with($query)
                          ->willReturn($expectedResults);

        $request = $this->createMock(Request::class);
        $request->method('query')->with('q')->willReturn($query);

        $response = $this->controller->search($request);

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals(['videos' => $expectedResults], $response->getData());
    }

    public function testSearchReturnsErrorWhenQueryIsMissing(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('query')->with('q')->willReturn(null);

        $response = $this->controller->search($request);

        $this->assertEquals(400, $response->getStatus());
        $this->assertEquals(['error' => 'No query string'], $response->getData());
    }
}
