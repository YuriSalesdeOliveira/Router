<?php

namespace YuriOliveira\Router;

interface ResponseInterface
{
    public function setStatusHttp(int $status_http): static;

    public function addContent(mixed $content): static;

    public function setContentType(string $content_type): static;

    public function addHeader(string $key, string $value): static;

    public function sendResponse();
}