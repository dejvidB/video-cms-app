<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Video;
use App\Queue\QueuePublisher;
use App\Repositories\VideoRepository;
use RedisException;

readonly class VideoService
{
    public function __construct(
        private VideoRepository $repository = new VideoRepository(),
        private ViewTrackerService $viewTrackerService = new ViewTrackerService(),
        private VideoSearchService $searchService = new VideoSearchService(),
        private QueuePublisher $queuePublisher = new QueuePublisher()
    ) {
    }

    /**
     * @return Video[]
     */
    public function getAllVideos(): array
    {
        return $this->repository->findAll();
    }

    public function createVideo(string $title, string $url): Video
    {
        $video = $this->repository->create($title, $url);

        $this->searchService->indexVideo($video);

        $this->queuePublisher->publish('video_jobs', [
            'video_id' => $video->getId()
        ]);

        return $video;
    }

    public function getVideoById(int $id): ?Video
    {
        return $this->repository->findById($id);
    }

    /**
     * @param int $limit
     * @return Video[]
     * @throws RedisException
     */
    public function getTrendingVideos(int $limit): array
    {
        $topIds = $this->viewTrackerService->getTopViewedIds($limit);
        $trending = [];

        foreach ($topIds as $id) {
            $video = $this->repository->findById($id);

            if ($video) {
                $video->setViews($this->viewTrackerService->getViewCount($id));
                $trending[] = $video;
            }
        }

        return $trending;
    }
}
