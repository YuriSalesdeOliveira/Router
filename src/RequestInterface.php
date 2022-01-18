<?php

namespace YuriOliveira\Router;

interface RequestInterface
{
    public function method(): string;

    public function uri(bool $normalized = true): string|array;

    public function normalizeUri(string $uri_url): bool;

    public function files(): array;

    public function get(): array;

    public function post(): array;

    public function headers(string|null $key = null): string|array|false;
}