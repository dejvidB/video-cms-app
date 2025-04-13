<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use App\Models\Video;
use PDO;

class VideoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * @return Video[]
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM videos ORDER BY created_at DESC");
        $videos =  $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($video) => Video::fromArray($video), $videos);
    }

    public function findById(int $id): ?Video
    {
        $stmt = $this->db->prepare("SELECT * FROM videos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);

        return $video ? Video::fromArray($video) : null;
    }

    public function create(string $title, string $url): Video
    {
        $stmt = $this->db->prepare("INSERT INTO videos (title, url) VALUES (:title, :url)");
        $stmt->execute(
            [
                ':title' => $title,
                ':url' => $url
            ]
        );

        return new Video((int)$this->db->lastInsertId(), $title, $url);
    }
}
