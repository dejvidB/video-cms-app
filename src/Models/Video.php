<?php

declare(strict_types=1);

namespace App\Models;

class Video implements Model
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public string $status = 'pending',
        public string $created_at = '',
        public ?int $views = null
    ) {
    }

    /**
     * @param array<string, mixed> $array
     * @return Video
     */
    public static function fromArray(array $array): Video
    {
        return new self(
            $array['id'],
            $array['title'],
            $array['url'],
            $array['status'],
            $array['created_at']
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
