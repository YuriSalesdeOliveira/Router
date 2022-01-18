<?php

namespace YuriOliveira\Router;

use Exception;

class Request implements RequestInterface
{
    protected string $method;
    protected array $uri;
    protected array $files;
    protected array $get;
    protected array $post;
    protected array|false $headers;

    public function __construct(string $method, string $uri, array $files,
        array $get = [], array $post = [], array|false $headers = [])
    {
        $this->method = $method;
        $this->uri['uri'] = $uri;
        $this->files = $files;
        $this->get = $get;
        $this->post = $post;
        $this->headers = $headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(bool $normalized = true): string|array
    {
        if ($normalized)
        {
            if (isset($this->uri['uri_normalized'])) { return $this->uri['uri_normalized']; }

            throw new Exception('Primeiro chame a função normalizeUri');
        }

        return $this->uri['uri'];
    }

    public function normalizeUri(string $base_url): bool
    {
        $uri = $this->normalizeUrlPath($this->uri['uri']);
        $base_url = $this->normalizeUrlPath($base_url);

        foreach ($base_url as $base_url_path_part) {

            $index = array_search($base_url_path_part, $uri);

            unset($uri[$index]);
        }

        $this->uri['uri_normalized'] = array_values($uri);

        return true;
    }

    protected function normalizeUrlPath(string $uri_url)
    {
        $uri_url = urldecode(parse_url($uri_url, PHP_URL_PATH));
        return array_values(array_filter(explode('/', $uri_url)));
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