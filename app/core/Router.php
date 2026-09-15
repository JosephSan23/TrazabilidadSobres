<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $requestMethod = strtoupper($method);
        $requestPath = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = preg_replace(
                '/\{([a-zA-Z][a-zA-Z0-9_]*)\}/',
                '(?P<$1>[^/]+)',
                $route['path']
            );

            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $requestPath, $matches)) {
                continue;
            }

            $parameters = [];

            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $parameters[$key] = $value;
                }
            }

            call_user_func($route['handler'], $parameters);

            return;
        }

        http_response_code(404);

        echo 'Página no encontrada.';
    }
}