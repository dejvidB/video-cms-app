<?php

declare(strict_types=1);

namespace Tests\Queue;

use App\Queue\QueuePublisher;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;

class QueuePublisherTest extends TestCase
{
    public function testPublishSendsMessageToQueue(): void
    {
        $mockChannel = $this->createMock(AMQPChannel::class);

        $mockChannel->expects($this->once())
                    ->method('queue_declare')
                    ->with('video_jobs', false, true, false, false);

        $mockChannel->expects($this->once())
                    ->method('basic_publish')
                    ->with(
                        $this->isInstanceOf(AMQPMessage::class),
                        '',
                        'video_jobs'
                    );

        $mockChannel->expects($this->once())->method('close');

        $mockConnection = $this->createMock(AMQPStreamConnection::class);
        $mockConnection->method('channel')->willReturn($mockChannel);
        $mockConnection->expects($this->once())->method('close');

        $publisher = new QueuePublisher($mockConnection);

        $publisher->publish('video_jobs', ['video_id' => 123]);
    }
}
