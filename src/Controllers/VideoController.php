<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\VideoSearchService;
use App\Services\VideoService;
use App\Services\ViewTrackerService;
use InvalidArgumentException;

class VideoController
{
    private const TRENDING_VIDEOS_LIMIT = 5;

    public function __construct(
        private readonly VideoService $service = new VideoService(),
        private readonly ViewTrackerService $viewTrackerService = new ViewTrackerService(),
        private readonly VideoSearchService $searchService = new VideoSearchService()
    ) {
    }

    public function index(): Response
    {
        return new Response(['videos' => $this->service->getAllVideos()]);
    }

    public function store(Request $request): Response
    {
        try {
            $input = $request->validate(['title', 'url']);
        } catch (InvalidArgumentException $e) {
            return new Response(['error' => $e->getMessage()], 400);
        }

        return new Response($this->service->createVideo($input['title'], $input['url']));
    }

    public function show(int $id): Response
    {
        $video = $this->service->getVideoById($id);

        if (!$video) {
            return new Response(['error' => 'Video not found'], 404);
        }

        $this->viewTrackerService->trackView($id);

        return new Response($video);
    }

    public function trending(): Response
    {
        return new Response(['videos' => $this->service->getTrendingVideos(self::TRENDING_VIDEOS_LIMIT)]);
    }

    public function search(Request $request): Response
    {
        $query = $request->query('q');

        if (empty($query)) {
            return new Response(['error' => 'No query string'], 400);
        }

        $results = $this->searchService->search($query);

        return new Response(['videos' => $results]);
    }
}
