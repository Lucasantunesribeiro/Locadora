<?php

class Router
{
    private $routes = [];
    private $middlewares = [];

    public function addRoute($method, $path, $handler, $middlewares = [])
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function get($path, $handler, $middlewares = [])
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post($path, $handler, $middlewares = [])
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put($path, $handler, $middlewares = [])
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete($path, $handler, $middlewares = [])
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Normalizar path
        $path = $path ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                // Executar middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = new $middleware();
                    if (!$middlewareInstance->handle()) {
                        return;
                    }
                }

                // Executar handler
                if (is_callable($route['handler'])) {
                    call_user_func($route['handler']);
                } else {
                    [$controller, $action] = explode('@', $route['handler']);
                    $controllerInstance = new $controller();
                    $controllerInstance->$action();
                }
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        if (strpos($path, '/api/') === 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Rota não encontrada']);
        } else {
            echo '<h1>404 - Página não encontrada</h1>';
        }
    }

    private function matchPath($routePath, $requestPath)
    {
        return $routePath === $requestPath || 
               fnmatch($routePath, $requestPath);
    }
}