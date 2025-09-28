<?php

declare(strict_types=1);

namespace Clover\Interfaces;

interface ResponseInterface
{
    public function status(int $code = 200): ResponseInterface;

    public function send(string $body): void;

    public function json(array $body): void;
}
