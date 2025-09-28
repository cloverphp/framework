<?php

declare(strict_types=1);

namespace Clover\Router;

use Clover\Http\Request;
use Clover\Http\Response;

final class Router
{
    protected array $routes = [];
    protected array $middlewares = [];
    protected array $errorMiddlewares = [];

    // Register middleware
    public function use(string|callable $prefix, ?callable $middleware = null): static
    {
        if (is_callable($prefix) && $middleware === null) {
            $this->middlewares[] = ['prefix' => '/', 'handler' => $prefix];
        } else {
            $this->middlewares[] = ['prefix' => $prefix, 'handler' => $middleware];
        }
        return $this;
    }

    // Register error-handling middleware (4 parameters)
    public function useError(callable $middleware): static
    {
        $this->errorMiddlewares[] = $middleware;
        return $this;
    }

    // Register routes
    public function get(string $path, callable $handler): static
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    public function post(string $path, callable $handler): static
    {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    // ... put/delete/mount as before ...

    // Dispatch with async-style middleware + error handling
    public function dispatch(Request $req, Response $res): void
    {
        $method = $req->getMethod();
        $path = $req->getPath();

        $stack = [];

        // Add matching middlewares
        foreach ($this->middlewares as $mw) {
            if (str_starts_with($path, $mw['prefix'])) {
                $stack[] = $mw['handler'];
            }
        }

        // Add route handler last
        if (isset($this->routes[$method][$path])) {
            $stack[] = $this->routes[$method][$path];
        } else {
            $stack[] = fn($req, $res, $next) => $res->status(404)->send("Not Found");
        }

        $runner = function ($index, $err = null) use (&$runner, $stack, $req, $res) {
            if ($err !== null) {
                // Run error middleware chain
                foreach ($this->errorMiddlewares as $errorMw) {
                    $errorMw($err, $req, $res, function () {});
                }
                return;
            }

            if ($index < count($stack)) {
                $handler = $stack[$index];
                try {
                    $handler($req, $res, function ($err = null) use ($runner, $index) {
                        $runner($index + 1, $err);
                    });
                } catch (\Throwable $e) {
                    $runner($index + 1, $e);
                }
            }
        };

        $runner(0);
    }
}
