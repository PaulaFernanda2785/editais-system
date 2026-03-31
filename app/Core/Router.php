<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use RuntimeException;

class Router
{
    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, mixed $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, mixed $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    public function add(string $method, string $path, mixed $handler, array $middlewares = []): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($path);

        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $this->routes[$method][] = [
            'path' => $path,
            'pattern' => $this->compilePath($path),
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->getMethod();
        $path = $this->normalizePath($request->getPath());
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $request->setRouteParams($this->extractParams($matches));

            $destination = function (Request $request, Response $response) use ($route): void {
                $this->executeHandler($route['handler'], $request, $response);
            };

            $pipeline = array_reduce(
                array_reverse($route['middlewares']),
                function (callable $next, string $middlewareClass): callable {
                    return function (Request $request, Response $response) use ($middlewareClass, $next): void {
                        if (!class_exists($middlewareClass)) {
                            throw new RuntimeException(sprintf('Middleware "%s" nao encontrado.', $middlewareClass));
                        }

                        $middleware = new $middlewareClass();

                        if (!method_exists($middleware, 'handle')) {
                            throw new RuntimeException(
                                sprintf('Middleware "%s" deve possuir metodo handle().', $middlewareClass)
                            );
                        }

                        $middleware->handle($request, $response, $next);
                    };
                },
                $destination
            );

            $pipeline($request, $response);
            return;
        }

        $response->setStatusCode(404);

        if (View::exists('errors/404')) {
            $response->setContent(View::make('errors/404', ['path' => $path]));
        } else {
            $response->setContent('404 - Rota nao encontrada.');
        }

        $response->send();
    }

    private function executeHandler(mixed $handler, Request $request, Response $response): void
    {
        if (is_callable($handler)) {
            $result = $handler($request, $response);
            $this->handleResult($result, $response);
            return;
        }

        if (!is_string($handler)) {
            throw new InvalidArgumentException('Handler invalido para rota.');
        }

        $separator = str_contains($handler, '@') ? '@' : '::';
        if (!str_contains($handler, '@') && !str_contains($handler, '::')) {
            throw new InvalidArgumentException(
                sprintf('Handler "%s" invalido. Use Controller@metodo.', $handler)
            );
        }

        [$controller, $action] = explode($separator, $handler, 2);
        $controllerClass = str_contains($controller, '\\')
            ? $controller
            : 'App\\Controllers\\' . $controller;

        if (!class_exists($controllerClass)) {
            throw new RuntimeException(sprintf('Controller "%s" nao encontrado.', $controllerClass));
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $action)) {
            throw new RuntimeException(
                sprintf('Metodo "%s" nao encontrado em "%s".', $action, $controllerClass)
            );
        }

        if ($instance instanceof Controller) {
            $instance->setRequestResponse($request, $response);
        }

        $result = $instance->{$action}($request, $response);
        $this->handleResult($result, $response);
    }

    private function handleResult(mixed $result, Response $response): void
    {
        if ($response->isSent()) {
            return;
        }

        if ($result instanceof Response) {
            if (!$result->isSent()) {
                $result->send();
            }
            return;
        }

        if ($result === null) {
            $response->send();
            return;
        }

        if (is_array($result)) {
            $response->json($result);
            return;
        }

        $response->setContent((string) $result);
        $response->send();
    }

    private function compilePath(string $path): string
    {
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static fn(array $matches): string => '(?P<' . $matches[1] . '>[^/]+)',
            $path
        );

        return '#^' . $pattern . '$#';
    }

    /**
     * @param array<int|string, string> $matches
     * @return array<string, string>
     */
    private function extractParams(array $matches): array
    {
        $params = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        if ($normalized === '//') {
            $normalized = '/';
        }

        return rtrim($normalized, '/') === '' ? '/' : rtrim($normalized, '/');
    }
}
