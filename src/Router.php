<?php

declare(strict_types=1);

namespace App;

use App\Controllers\VideoController;
use App\Http\Request;
use App\Http\Response;
use ReflectionMethod;
use ReflectionNamedType;

class Router
{
    /**
     * @var array<string, array<string, array{class-string, string}>>
     */
    private array $routes = [];

    public function __construct()
    {
        $this->register('GET', '/videos', [VideoController::class, 'index']);
        $this->register('POST', '/videos', [VideoController::class, 'store']);
        $this->register('GET', '/videos/trending', [VideoController::class, 'trending']);
        $this->register('GET', '/videos/search', [VideoController::class, 'search']);
        $this->register('GET', '/videos/{id}', [VideoController::class, 'show']);
    }

    /**
     * @param string $method
     * @param string $pattern
     * @param array<string> $handler
     * @return void
     */
    public function register(string $method, string $pattern, array $handler): void
    {
        $this->routes[$method][$pattern] = $handler;
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $regex = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*}#', '([^/]+)', $pattern);
            $regex = "#^" . $regex . "$#";

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);

                $controllerClass = $handler[0];
                $methodName = $handler[1];

                $controller = new $controllerClass();

                $methodReflection = new ReflectionMethod($controllerClass, $methodName);
                $parameters = $methodReflection->getParameters();

                if (!empty($parameters)) {
                    $type = $parameters[0]->getType();

                    if ($type instanceof ReflectionNamedType && $type->getName() === Request::class) {
                        array_unshift($matches, new Request());
                    }
                }

                $response = call_user_func_array([$controller, $methodName], $matches);

                if ($response instanceof Response) {
                    $response->send();
                } else {
                    (new Response($response))->send();
                }

                return;
            }
        }

        (new Response(['error' => 'Not found'], 404))->send();
    }
}
