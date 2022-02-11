<?php

namespace App\Services\Contracts;

use App\Enums\EpisodeFilter;

interface EpisodeContract
{
    public function recently(EpisodeFilter $type = EpisodeFilter::SUB): string;

    public function random(): string;

    public function search(): string;
    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function get(): string;

    public function store(): string;

    public function getExistingEpisodes(): string;
}
