<?php

use App\Core\Application;
use App\Core\Cache\Contracts\CacheContract;
use App\Core\Cache\FileCache;
use App\Core\Database\Contracts\QueryBuilderContract;
use App\Core\Database\QueryBuilder;

use App\Services\Contracts\EpisodeContract;
use App\Services\NewEpisodeService;

return function (Application $app) {
    $app->bind(EpisodeContract::class, NewEpisodeService::class);
    $app->bind(CacheContract::class, FileCache::class);
    $app->bind(QueryBuilderContract::class, QueryBuilder::class);
};
