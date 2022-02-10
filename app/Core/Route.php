<?php

namespace App\Core;

use Exception;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class Route
{
    protected array $parameters = [];

    public function __construct(
        protected string $method,
        protected string $path,
        protected $handler
    ) {
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function dispatch()
    {
        request()->setParameters($this->parameters);

        if (is_array($this->handler)) {
            [$class, $method] = $this->handler;
            $reflectionMethod = new ReflectionMethod($class, $method);
            $parameters = $reflectionMethod->getParameters();

            if (!$parameters) {
                return $reflectionMethod->invoke(new $class);
            }

            $dependencies = array_map(function (ReflectionParameter $param) use ($class, $method) {
                $name = $param->getName();
                $type = $param->getType();

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    return app()->get($type->getName());
                }
                throw new Exception("Class $class's $method cannot be invoke because method's param $name cannot be resolved.");
            }, $parameters);

            return $reflectionMethod->invokeArgs(new $class, $dependencies);
        }
        return call_user_func($this->handler);
    }

    public function matches(string $method, string $path): bool
    {
        if ($this->method !== $method) {
            return false;
        }
        if ($this->path === $path) {
            return true;
        }
        $parameterNames = [];

        $pattern = $this->normalisePath($this->path);

        $pattern = preg_replace_callback(
            '#{([^/]+)}/#',
            function ($found) use (&$parameterNames) {
                array_push($parameterNames, rtrim($found[1], '?'));

                if (str_ends_with($found[1], '?')) {
                    return '([^/]*)(?:/?)';
                }

                return '([^/]+)/';
            },
            $pattern
        );

        if (!str_contains($pattern, '+') && !str_contains($pattern, '*')) {
            return false;
        }

        preg_match_all(
            "#{$pattern}#",
            $this->normalisePath($path),
            $matches
        );

        $parameterValues = [];

        if (count($matches) > 0) {
            // if the route matches the request path then
            // we need to assemble the parameters before
            // we can return true for the match
            foreach ($matches as $key => $value) {
                if ($key === 0) continue;
                foreach ($value as $v) {
                    array_push($parameterValues, $v);
                }
            }
            // make an empty array so that we can still
            // call array_combine with optional parameters
            // which may not have been provided
            $emptyValues = array_fill(
                0,
                count($parameterNames),
                null
            );
            // += syntax for arrays means: take values from the
            // right-hand side and only add them to the left-hand
            // side if the same key doesn't already exist.
            //
            // you'll usually want to use array_merge to combine
            // arrays, but this is an interesting use for +=
            $parameterValues += $emptyValues;
            $this->parameters = array_combine(
                $parameterNames,
                $parameterValues,
            );
            return true;
        }
        return false;
    }

    private function normalisePath(string $path): string
    {
        $path = trim($path, '/');
        $path = "/$path/";
        $path = preg_replace('/[\/]{2,}/', '/', $path);
        return $path;
    }
}
