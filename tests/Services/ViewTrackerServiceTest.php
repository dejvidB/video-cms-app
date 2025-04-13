<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\ViewTrackerService;
use PHPUnit\Framework\TestCase;
use Redis;

class ViewTrackerServiceTest extends TestCase
{
    private Redis $redis;
    private ViewTrackerService $service;

    protected function setUp(): void
    {
        $this->redis = new Redis();
        $this->redis->connect($_ENV['REDIS_HOST'], (int)$_ENV['REDIS_PORT']);
        $this->redis->flushAll();

        $this->service = new ViewTrackerService();
    }

    protected function tearDown(): void
    {
        $this->redis->flushAll();
        $this->redis->close();
    }

    public function testTrackViewIncrementsCount(): void
    {
        $this->service->trackView(1);
        $this->service->trackView(1);

        $count = $this->service->getViewCount(1);
        $this->assertEquals(2, $count);
    }

    public function testGetTopViewedIdsReturnsSorted(): void
    {
        $this->service->trackView(1);
        $this->service->trackView(1);

        $this->service->trackView(2);
        $this->service->trackView(2);
        $this->service->trackView(2);

        $this->service->trackView(3);

        $topIds = $this->service->getTopViewedIds(2);

        $this->assertEquals([2, 1], $topIds);
    }

    public function testGetViewCountReturnsZeroIfMissing(): void
    {
        $count = $this->service->getViewCount(999);
        $this->assertEquals(0, $count);
    }
}
