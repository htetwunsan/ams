<?php

declare(strict_types=1);

namespace App\Core;

use Exception;

class Application extends Container
{
    public string $basePath;
    protected Router $router;

    public function __construct(?string $basePath = null)
    {
        static::setInstance($this);

        $this->instance(Application::class, $this);
        $this->instance(Request::class, $this->get(Request::class));
        $this->instance(Router::class, $this->get(Router::class));

        $this->basePath = $basePath ?? dirname(dirname(__DIR__));
        $this->router = $this->get(Router::class);
    }

    public function run()
    {
        echo $this->router->handle();
    }
}
