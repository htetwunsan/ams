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

    public function store(Request $request, QueryBuilderContract $qb): string
    {
        $data = $request->getBody();
        $exploded = explode('/', $data['slug']);
        $tag = $data['tag'];
        $resultEpisode = $qb->upsert('episodes', [
            'slug' => '/' . $tag . '/' . end($exploded),
            'tag' => $data['tag'],
            'video_cover' => $data['video']['cover'],
            'video_title' => $data['video']['title'],
            'video_description' => $data['video']['description'],
            'original_id' => $data['id'],
            'embed' => $data['embed'],
            'name' => $data['name'],
            'number' => $data['number'],
            'image_src' => $data['image']['src'],
            'image_alt' => $data['image']['alt'],
            'sub' => $data['sub'],
            'original_date' => date('Y-m-d H:i:s', strtotime($data['date']))
        ], true);

        header('Content-Type: application/json; charset=utf-8');
        http_response_code(201);
        return json_encode(['episode' => $resultEpisode]);
    }
}
