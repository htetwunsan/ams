<?php

namespace App\Core\Cache\Contracts;

interface CacheContract
{
    public function remember(string $key, callable $getValue, int $seconds = 300);
}
