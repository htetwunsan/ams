<?php

namespace App\Core\Contracts;

interface Cache
{
    public function remember(string $key, callable $getValue, int $seconds = 300);
}
