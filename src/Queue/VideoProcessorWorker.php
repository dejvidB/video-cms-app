<?php

declare(strict_types=1);

namespace App\Queue;

use App\Database;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class VideoProcessorWorker
{
    private AMQPStreamConnection $connection;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASS']
        );
    }

    public function run(): void
    {
        $channel = $this->connection->channel();
        $channel->queue_declare('video_jobs', false, true, false, false);

        echo "Waiting for messages.\n";

        $channel->basic_consume('video_jobs', '', false, true, false, false, fn($msg) => $this->handle($msg));

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }

    private function handle(AMQPMessage $msg): void
    {
        echo "Received job: {$msg->getBody()}\n";

        $data = json_decode($msg->getBody(), true);
        $videoId = $data['video_id'] ?? null;

        if (!$videoId) {
            echo "Invalid job payload\n";
            return;
        }

        echo "Processing video ID: {$videoId}\n";
        sleep(rand(1, 10)); // Simulate processing

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE videos SET status = 'ready' WHERE id = :id");
            $stmt->execute([':id' => $videoId]);
            echo "Video {$videoId} status updated to 'ready'\n";
        } catch (\Exception $e) {
            echo "Failed to update video: {$e->getMessage()}\n";
        }
    }
}
