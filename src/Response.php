<?php

namespace YuriOliveira\Router;

use Exception;

class Response implements ResponseInterface
{
    protected int $status_http = 200;
    protected array $headers = [];
    protected mixed $content;
    protected string $content_type = 'text/html';

    protected array $allowed_content_types = [
        'application/javascript',
        'application/json',

        'text/html',
        'text/css',
        'text/csv',
        'text/html',
        'text/plain',
        'text/xml',

        'video/mpeg',
        'video/mp4',
        'video/quicktime',
        'video/x-ms-wmv',
        'video/x-msvideo',
        'video/x-flv',
        'video/web',

        'image/gif',
        'image/jpeg',
        'image/png',
        'image/tiff',
        'image/vnd.microsoft.icon',
        'image/x-icon',
        'image/vnd.djvu',
        'image/svg+xml',
    ];

    public function setStatusHttp(int $status_http): static
    {
        $this->status_http = $status_http;

        return $this;
    }

    public function addContent(mixed $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function setContentType(string $content_type): static
    {
        $this->allowedContentTypes($content_type);

        $this->content_type = $content_type;

        $this->addHeader('Content-Type', $content_type);

        return $this;
    }

    protected function allowedContentTypes($content_type)
    {
        if (!in_array($content_type, $this->allowed_content_types))
        {
            throw new Exception("Tipo de conteúdo {$content_type} não permitido.");
        }
    }

    public function addHeader(string $key, string $value): static
    {
        $this->headers[$key] = $value;

        return $this;
    }

    protected function sendHeaders(): void
    {
        http_response_code($this->status_http);

        foreach ($this->headers as $key => $value)
        {
            header("{$key}: {$value}");
        }
    }

    public function sendResponse()
    {
        $this->sendHeaders();

        switch ($this->content_type)
        {
            case 'text/html':
                echo $this->content;
            break;
        }
    }
}