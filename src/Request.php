<?php

namespace YuriOliveira\Router;

class Request implements RequestInterface
{
    protected string $method;
    protected string $uri;
    protected array $files;
    protected array $get;
    protected array $post;
    protected array|false $headers;

    public function __construct(string $method, string $uri, array $files,
        array $get = [], array $post = [], array|false $headers = [])
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->files = $files;
        $this->get = $get;
        $this->post = $post;
        $this->headers = $headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function get(): array
    {
        return $this->get;
    }

    public function post(): array
    {
        return $this->post;
    }

    public function headers(string|null $key = null): string|array|false
    {
        if (is_array($this->headers))
        {
            return $key ? $this->headers[$key] : $this->headers;
        }

        return false;
    }
}