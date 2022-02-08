<?php

namespace App\Core;

use App\Core\Contracts\Cache;

class FileCache implements Cache
{
    public function remember(string $key, callable $getValue, int $seconds = 300)
    {
        $key = base_path() . "/caches/$key";
        if (file_exists($key) && (filemtime($key) > (time() - $seconds))) {
            return file_get_contents($key);
        }
        $value = $getValue();
        file_put_contents($key, $value, LOCK_EX);
        return $value;
    }
}
