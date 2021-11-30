<?php

namespace Source\Router;

use Exception;

class Router
{
    private $uri;
    private $method;
    private $root;
    private $namespace;
    private $group;
    private $routes;
    private $route;
    private $data;
    private $error;

    private const NOT_FOUND = 404;
    private const METHOD_NOT_ALLOWED = 405;

    public function __construct(string $root)
    {
        $this->uri();

        $this->method();

        $this->root = $root;
    }

    private function uri(): void
    {
        $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        $this->uri = array_values(array_filter(explode('/', $uri)));
    }

    private function method(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->method = $_SERVER['REQUEST_METHOD'];
        }
    }

    public function namespace($namespace)
    {
        $this->namespace = $namespace ? ucwords($namespace) : null;
    }

    public function group(?string $group): Router
    {
        $this->group = $group;

        return $this;
    }

    public function post(string $route, $handler, string $name): Router
    {
        $this->addRoute('POST', $route, $handler, $name);

        return $this;
    }

    public function get(string $route, $handler, string $name): Router
    {
        $this->addRoute('GET', $route, $handler, $name);

        return $this;
    }

    private function addRoute(string $method, string $route, $handler, string $name): void
    {
        if ($this->group) {

            if ($route === '/') {

                $route = $this->group;
            } else {

                $route = $this->group . $route;
            }
        }

        $route = array_values(array_filter(explode('/', $route)));

        $this->routes[$method][] = [
            'route' => $route,
            'handler' => $this->handler($handler),
            'action' => $this->action($handler),
            'name' => $name
        ];
    }

    private function handler($handler)
    {
        if (is_string($handler)) {

            return "{$this->namespace}\\" . explode(':', $handler)[0];

        } elseif (is_callable($handler)) {

            return $handler;
        }

        throw new Exception('O handler deve ser um callable
            ou uma string que faz referencia a um controller.');
    }

    private function action($handler): ?string
    {
       return is_callable($handler) ? null : explode(':', $handler)[1];
    }

    private function findRoute(): bool
    {
        if (isset($this->routes[$this->method])) {

            foreach ($this->routes[$this->method] as $route) {

                if (count($this->uri) === count($route['route'])) {

                    $difference = array_diff($route['route'], $this->uri);

                    $indexes = $this->getIndex(':', $difference);

                    if ($indexes) {

                        $route = $this->normalizeRoute($route, $indexes);
                    }

                    if ($route['route'] === $this->uri) {

                        $this->route = $route;

                        return true;
                    }
                }
            }
        }

        $this->error = self::NOT_FOUND;

        return false;
    }

    private function getIndex(string $find, array $difference): ?array
    {
        foreach ($difference as $index => $value) {

            if (strpos($find, $value[0]) === 0) {

                $indexes[] = $index;
            }
        }

        return $indexes ?? null;
    }

    private function normalizeRoute(array $route, array $indexes): array
    {
        $this->setData($route, $indexes);

        foreach ($indexes as $index) {

            $route['route'][$index] = $this->uri[$index];
        }

        return $route;
    }

    private function setData(array $route, array $indexes): void
    {
        foreach ($indexes as $index) {

            $key = substr($route['route'][$index], 1, -1);

            $parameters[$key] = $this->uri[$index];
        }

        $this->data = $parameters ?? null;
    }

    private function getData(): ?array
    {
        if ($this->method === 'POST') {

            $data = ['_FILES' => $_FILES] + ['_POST' => $_POST] + ($this->data ?? []);

            return $data;
        }

        return $this->data ?? null;
    }

    private function executeRoute(): bool
    {
        if (is_callable($this->route['handler'])) {

            call_user_func($this->route['handler'], $this->getData());

            return true;
        }

        $controller = $this->route['handler'];
        $controller_method = $this->route['action'];

        if (class_exists($controller)) {

            $controller = new $controller($this);

            if (method_exists($controller, $controller_method)) {

                $controller->$controller_method($this->getData());

                return true;
            }
        }

        $this->error = self::METHOD_NOT_ALLOWED;

        return false;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    public function dispatch(): bool
    {
        if ($this->findRoute()) {

            return $this->executeRoute();
        }

        return false;
    }

    public function redirect(string $name_or_path): void
    {
        foreach ($this->routes['GET'] as $route) {

            if (isset($route['name']) && $route['name'] === $name_or_path) {

                $route = implode('/', $route['route']);

                $path = $this->root . "/{$route}";

                header("Location: {$path}");

                exit();
            }
        }

        $path = $this->root . "/{$name_or_path}";

        header("Location: {$path}");

        exit();
    }
}
