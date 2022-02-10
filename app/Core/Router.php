<?php

namespace App\Core;

class Router
{
    /**
     * @var Route[] $routes
     */
    protected array $routes = [];
    protected array $errorHandlers = [];

    public function __construct(
        protected Request $request
    ) {
    }

    public function get(string $path, array|callable $handler)
    {
        $this->routes[] = new Route('GET', $path, $handler);
    }

    public function post(string $path, array|callable $handler)
    {
        $this->routes[] = new Route('POST', $path, $handler);
    }

    public function setErrorHandler(int $code, array|callable $handler)
    {
        $this->errorHandlers[$code] = $handler;
    }

    public function handle(): string
    {
        $method = $this->request->method();
        $path = $this->request->path();
        $route = $this->resolveRoute($method, $path);
        if (is_null($route)) {
            return $this->resolveErrorHandler($this->errorHandlers[404]);
        }
        return $route->dispatch();
    }

    private function resolveRoute(string $method, string $path): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }
        return null;
    }

    private function resolveErrorHandler(array|callable $handler): string
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            return (new $class)->{$method}($this->request);
        }
        return $handler($this->request);
    }
}
