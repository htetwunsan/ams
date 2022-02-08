<?php

namespace App\Core;

class Request
{

    protected array $parameters = [];

    public function path(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $pos = strpos($path, '?');
        if ($pos === false) {
            return $path;
        }
        return substr($path, 0, $pos);
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function getBody(): array
    {
        if ($this->method() === 'GET') {
            return $_GET;
        }
        return $_POST;
    }

    public function setParameters(array $parameters = [])
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
