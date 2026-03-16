<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /**
     * @var array<int, array{
     *     method: string,
     *     pattern: string,
     *     handler: callable
     * }>
     */
    private array $routes = [];

    private $notFoundHandler = null;

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function setNotFound(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPatternToRegex($route['pattern']);

            if (preg_match($pattern, $path, $matches)) {
                $params = [];

                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }

                call_user_func($route['handler'], $params);
                return;
            }
        }

        if (is_callable($this->notFoundHandler)) {
            call_user_func($this->notFoundHandler);
            return;
        }

        not_found($path);
    }

    private function convertPatternToRegex(string $pattern): string
    {
        $pattern = rtrim($pattern, '/');
        $pattern = $pattern === '' ? '/' : $pattern;

        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\:([^}]+)\}/',
            static fn(array $m): string => '(?P<' . $m[1] . '>' . $m[2] . ')',
            $pattern
        );

        return '#^' . $regex . '$#';
    }
}