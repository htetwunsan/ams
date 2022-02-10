<?php

declare(strict_types=1);

namespace App\Core;

class Application extends Container
{
    public string $basePath;

    public function __construct(?string $basePath = null)
    {
        static::setInstance($this);

        $this->instance(Application::class, $this);
        $this->instance(Request::class, $this->get(Request::class));
        $this->instance(Router::class, $this->get(Router::class));

        ini_set('date.timezone', 'Asia/Yangon');

        $this->basePath = $basePath ?? dirname(dirname(__DIR__));
    }

    public function run()
    {
        /**
         * @var Router $router
         */
        $router = $this->get(Router::class);
        echo $router->handle();
    }
}
