<?php

use App\Core\Application;
use App\Core\Container;
use App\Core\Contracts\Cache;
use App\Core\Request;
use App\Core\Router;

if (!function_exists('app')) {
    function app(): Application
    {
        return Container::getInstance();
    }
}

if (!function_exists('router')) {
    function router(): Router
    {
        return app()->get(Router::class);
    }
}

if (!function_exists('request')) {
    function request(): Request
    {
        return app()->get(Request::class);
    }
}

if (!function_exists('cache')) {
    function cache(): Cache
    {
        return app()->get(Cache::class);
    }
}


if (!function_exists('base_path')) {
    function base_path(): string
    {
        return app()->basePath;
    }
}
