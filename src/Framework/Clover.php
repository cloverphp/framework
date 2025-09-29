<?php

declare(strict_types=1);

namespace Clover\Framework;

use Clover\Framework\Router\Router;
use Dotenv\Dotenv;
use Clover\Framework\Http\Response;
use Clover\Framework\Http\Request;

class Clover
{
    protected ?Router $router = null;

    // Lazy initialize router
    public function router(): Router
    {
        if ($this->router === null) {
            $this->router = new Router();
        }
        return $this->router;
    }

    // Mount a sub-router at a prefix
    public function use(string $prefix, Router $router): void
    {
        $this->router()->mount($prefix, $router);
    }

    public function run(?int $port, ?bool $devMode = null): void
    {
        // --- Load .env ---
        $dotenvPath = dirname(__DIR__, 2);
        if (file_exists($dotenvPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($dotenvPath);
            $dotenv->load();
        }

        // --- Auto-detect dev mode ---
        if ($devMode === null) {
            $devMode = (php_sapi_name() === 'cli');
        }

        if ($devMode) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            error_reporting(0);
        }

        // --- Port Priority ---
        $finalPort = $port
            ?? ($_ENV['PORT'] ?? getenv('PORT'))
            ?? 3000;

        try {
            //$callback();

            $green = "\033[32m";
            $reset = "\033[0m";
            $green . "✅ Server running at: http://localhost:$finalPort" . $reset . PHP_EOL;
            shell_exec("php -S localhost:{$finalPort}");
            $req = new Request();
            $res = new Response();

            $this->router()
                 ->dispatch($req, $res);


        } catch (\Throwable $e) {
            $red = "\033[31m";
            $reset = "\033[0m";

            echo $red . "❌ Server failed: " . $e->getMessage() . $reset . PHP_EOL;
            // $e->getMessage();

            if ($devMode) {
                echo $e->getTraceAsString() . PHP_EOL;
            }
            exit(1);
        }
    }
}
