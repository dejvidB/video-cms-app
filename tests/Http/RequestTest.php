<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Http\Request;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testValidateReturnsBodyWhenFieldsArePresent(): void
    {
        $data = [
            'title' => 'Test Video',
            'url' => 'https://example.com/video.mp4'
        ];

        $request = Request::fromArray($data);

        $result = $request->validate(['title', 'url']);

        $this->assertEquals($data, $result);
    }

    public function testValidateThrowsWhenFieldIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: url');

        $data = ['title' => 'Only title'];
        $request = Request::fromArray($data);

        $request->validate(['title', 'url']);
    }

    public function testQueryReturnsExpectedValue(): void
    {
        $_GET['q'] = 'php';

        $request = new Request();

        $this->assertEquals('php', $request->query('q'));
    }

    public function testQueryReturnsDefaultWhenKeyMissing(): void
    {
        $_GET = [];

        $request = new Request();

        $this->assertEquals('default-value', $request->query('q', 'default-value'));
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }
}
