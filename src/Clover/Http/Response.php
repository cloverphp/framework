<?php

declare(strict_types=1);

namespace Clover\Http;

use Clover\Interfaces\ResponseInterface;

final class Response implements ResponseInterface
{
    public int $statusCode = 200;

    public function send(array|string $body): void
    {
        echo $body;
        exit(1);
    }

    public function status(int $code = 200): ResponseInterface
    {
        $this->statusCode = $code;
       // http_response_code($code);
        return $this;
    }

    public function json(array $body): void
    {
        header("Content-Type: application/json");
        echo json_encode($body, JSON_PRETTY_PRINT);
        exit;
    }
}
