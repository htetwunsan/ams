<?php

namespace App\Controllers\Api;

use App\Core\Database\Contracts\QueryBuilderContract;
use App\Core\Request;
use App\Services\Contracts\EpisodeContract;
use App\Enums\EpisodeFilter;

class EpisodeController
{
    public function random(EpisodeContract $episodeService): string
    {
        return $episodeService->random();
    }

    public function recentlyAddedSub(EpisodeContract $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::SUB);
    }

    public function recentlyAddedRaw(EpisodeContract $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::RAW);
    }

    public function movies(EpisodeContract $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::MOVIE);
    }

    public function kshow(EpisodeContract $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::KSHOW);
    }

    public function popular(EpisodeContract $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::POPULAR);
    }

    public function ongoingSeries(EpisodeContract $episodeService): string
    {
        return $episodeService->recently(EpisodeFilter::ONGOING_SERIES);
    }

    public function search(EpisodeContract $episodeService): string
    {
        return $episodeService->search();
    }

    public function get(EpisodeContract $episodeService): string
    {
        return $episodeService->get();
    }

    public function getExistingEpisodes(EpisodeContract $episodeService): string
    {
        return $episodeService->getExistingEpisodes();
    }

    public function store(EpisodeContract $episodeService): string
    {
        return $episodeService->store();
    }
}
