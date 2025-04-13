<?php

declare(strict_types=1);

namespace App\Queue;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueuePublisher
{
    public function __construct(
        private ?AMQPStreamConnection $connection = null
    ) {
        $this->connection = $connection ?? new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASS']
        );
    }

    /**
     * @param string $queueName
     * @param array<string, mixed> $data
     * @return void
     * @throws Exception
     */
    public function publish(string $queueName, array $data): void
    {
        $channel = $this->connection->channel();
        $channel->queue_declare($queueName, false, true, false, false);

        $message = new AMQPMessage(json_encode($data));
        $channel->basic_publish($message, '', $queueName);

        $channel->close();
        $this->connection->close();
    }
}
