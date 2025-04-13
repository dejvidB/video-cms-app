<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Video;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    public function testToArray(): void
    {
        $video = new Video(1, 'Title', 'https://url.com', 'ready', '2025-04-14', 999);

        $expected = [
            'id' => 1,
            'title' => 'Title',
            'url' => 'https://url.com',
            'status' => 'ready',
            'created_at' => '2025-04-14',
            'views' => 999,
        ];

        $this->assertSame($expected, $video->toArray());
    }
}
