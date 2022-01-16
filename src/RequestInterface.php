<?php

namespace YuriOliveira\Router;

interface RequestInterface
{
    public function method(): string;

    public function uri(): string;

    public function files(): array;

    public function get(): array;

    public function post(): array;

    public function headers(string|null $key = null): string|array|false;
}