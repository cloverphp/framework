<?php

declare(strict_types=1);

namespace Clover\Http;

use Clover\Interfaces\RequestInterface;

final class Request implements RequestInterface
{
    private string $method;
    private string $path;
    private array $query;
    private array $body;
    private array $headers;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->query = $_GET;
        $this->headers = getallheaders() ?: [];

        if(($this->headers['Content-Type'] ?? '') === 'application/json'){
            $input = file_get_contents("php://input");
            $this->body = json_decode($input, true) ?? [];
        } else {
            $this->body = $_POST;
        }

    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}

