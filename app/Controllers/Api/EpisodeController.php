<?php

namespace App\Controllers\Api;

use App\Services\EpisodeService;
use App\Enums\EpisodeFilter;

class EpisodeController
{
    public function __construct()
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    public function recentlyAddedSub(EpisodeService $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::SUB);
    }

    public function recentlyAddedRaw(EpisodeService $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::RAW);
    }

    public function movies(EpisodeService $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::MOVIE);
    }

    public function kshow(EpisodeService $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::KSHOW);
    }

    public function popular(EpisodeService $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::POPULAR);
    }

    public function ongoingSeries(EpisodeService $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::ONGOING_SERIES);
    }

    public function search(EpisodeService $episodeService): string
    {
        return $episodeService->search();
    }

    public function get(EpisodeService $episodeService): string
    {
        return $episodeService->get();
    }
}
