<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\ContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

class Container implements ContainerInterface
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * @var array<string, callable|string>
     */
    protected array $bindings = [];

    /**
     * @var array<string, object>
     */
    protected array $instances = [];


    public function get(string $id)
    {
        if ($this->has($id)) {
            $instance = $this->instances[$id] ?? null;
            if (!is_null($instance)) {
                return $instance;
            }

            $binding = $this->bindings[$id];
            if (is_callable($binding)) {
                return $this->bindings[$id]($this);
            }

            $id = $binding;
        }
        return $this->resolve($id);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    public function bind(string $id, callable|string $concrete): void
    {
        $this->bindings[$id] = $concrete;
    }

    public function instance(string $id, object $instance)
    {
        $this->instances[$id] = $instance;
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param  \App\Core\Container
     * @return \App\Core\Container|static
     */
    public static function setInstance(?Container $container = null)
    {
        return static::$instance = $container;
    }

    protected function resolve(string $id)
    {
        $reflectionClass = new ReflectionClass($id);

        if (!$reflectionClass->isInstantiable()) {
            throw new ContainerException("Class $id cannot instantiable.");
        }

        $constructor = $reflectionClass->getConstructor();

        if (!$constructor) return $reflectionClass->newInstance();

        $parameters = $constructor?->getParameters();

        if (!$parameters) return $reflectionClass->newInstance();

        $dependencies = array_map(function (ReflectionParameter $param) use ($id) {
            $name = $param->getName();
            $type = $param->getType();

            if (!$type) {
                throw new ContainerException("Failed to resolve class $id beacuse parameter $name is not type hinted and container is so confused to resolve.");
            }

            if ($type instanceof ReflectionUnionType) {
                throw new ContainerException("UnionType is currently not supported.");
            }

            if ($type instanceof ReflectionIntersectionType) {
                throw new ContainerException("IntersectionType is currently not supported.");
            }

            if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
                throw new ContainerException("BuiltinType is currently not supported.");
            }
            /**
             * @var ReflectionNamedType $type
             */
            return $this->get($type->getName());
        }, $parameters);

        return $reflectionClass->newInstanceArgs($dependencies);
    }
}
