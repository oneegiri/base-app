<?php

namespace App;

use App\Attributes\Route;
use App\Attributes\Middleware;
use ReflectionClass;
use ReflectionMethod;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function registerController(string $controller): void
    {
        $reflection = new ReflectionClass($controller);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttribute = $this->getAttributeInstance($method, Route::class);

            if ($routeAttribute) {
                $this->add(
                    $routeAttribute->method,
                    $routeAttribute->path,
                    [$controller, $method->getName()]
                );

                $middlewareAttribute = $this->getAttributeInstance($method, Middleware::class);

                if ($middlewareAttribute) {
                    $this->middlewares[$routeAttribute->method][$routeAttribute->path] = $middlewareAttribute->middlewares;
                }
            }
        }
    }

    private function getAttributeInstance(ReflectionMethod $method, string $attributeClass): ?object
    {
        $attributes = $method->getAttributes($attributeClass);
        return $attributes[0]->newInstance() ?? null;
    }

    public function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(string $method, string $uri)
    {
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Apply middlewares
                $middlewares = $this->middlewares[$method][$route] ?? [];
                foreach ($middlewares as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $response = $middleware->handle($params);
                    if ($response !== null) {
                        return $response;
                    }
                }

                if (is_array($handler)) {
                    [$controller, $method] = $handler;
                    return (new $controller)->$method($params);
                }

                return $handler($params);
            }
        }

        http_response_code(404);
        return '404 Not Found';
    }
}
