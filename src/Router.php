<?php

namespace YuriOliveira\Router;

use Exception;

class Router
{
    protected array $base_url;
    protected array $uri;
    protected string $namespace;
    protected string|null $group;
    protected array $routes = [
        'GET' => [],
        'POST' => [],
    ];
    protected array $route;
    protected array $data;
    protected int $error;

    protected RequestInterface $request;
    protected ResponseInterface $response;

    const BAD_REQUEST = 400;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_IMPLEMENTED = 501;
    const OK = 200;

    public function __construct(RequestInterface $request, ResponseInterface $response, string $base_url)
    {
        $this->request = $request;
        $this->response = $response;

        $this->baseUrl($base_url);
        $this->uri();
    }

    protected function baseUrl(string $base_url): void
    {
        $this->base_url = [
            'url' => $base_url,
            'uri' => $this->normalizeUri($base_url)
        ];
    }

    protected function uri(): void
    {
        $uri = $this->normalizeUri($this->request->uri());

        foreach ($this->base_url['uri'] as $base_url_path_part) {

            $index = array_search($base_url_path_part, $uri);

            unset($uri[$index]);
        }

        $this->uri = [
            'uri' => $this->request->uri(),
            'uri_nomalized' => array_values($uri)
        ];
    }

    protected function normalizeUri(string $uri): array
    {
        $uri = urldecode(parse_url($uri, PHP_URL_PATH));
        return array_values(array_filter(explode('/', $uri)));
    }

    public function namespace(string|null $namespace = null): static
    {
        $this->namespace = $namespace ? ucwords($namespace) : null;

        return $this;
    }

    public function group(string|null $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function post(string $route, $handler, string $name): static
    {
        $this->addRoute('POST', $route, $handler, $name);

        return $this;
    }

    public function get(string $route, $handler, string $name): static
    {
        $this->addRoute('GET', $route, $handler, $name);
        
        return $this;
    }

    protected function addRoute(string $method, string $route,
        callable|string $handler, string $name): void
    {
        if (!empty($this->group))
        {
            $route = $route === '/' ? $this->group : $this->group . $route;
        }

        $route = array_values(array_filter(explode('/', $route)));

        $this->routes[$method][] = [
            'route' => $route,
            'handler' => $this->handler($handler),
            'action' => $this->action($handler),
            'name' => $this->name($name)
        ];
    }

    protected function name(string $name): string
    {
        $name = strtolower($name);
        
        // validar name

        return $name;
    }

    protected function handler(callable|string $handler): callable|string
    {
        return is_string($handler) ? "{$this->namespace}\\" . explode(':', $handler)[0] : $handler;
    }

    protected function action(callable|string $handler): string|null
    {
       return is_string($handler) ? explode(':', $handler)[1] : null;
    }

    protected function addData(array $data)
    {
        foreach ($data as $key => $value)
        {
            if (strpos(':', $key[0]) === 0) { $key = substr($key, 1); }

            $this->data[$key] = $value;
        }
    }

    protected function data()
    {
        $data = $this->request->files() + $this->request->get() + $this->request->post();

        return !empty($this->data) ? $this->data + $data : $data;
    }

    protected function findRoute(): bool
    {
        if (isset($this->routes[$this->request->method()]))
        {
            foreach ($this->routes[$this->request->method()] as $route)
            {
                if (count($this->uri['uri_nomalized']) === count($route['route']))
                {    
                    [$route['route'], $changes] = $this->normalizeRouteUsingUri(
                        $route['route'],
                        $this->uri['uri_nomalized']
                    );
                    
                    if ($route['route'] === $this->uri['uri_nomalized'])
                    {
                        $this->addData($changes);

                        $this->route = $route;

                        return true;
                    }
                }
            }
        }

        $this->error = self::NOT_FOUND;

        return false;
    }

    protected function normalizeRouteUsingUri(array $route, array $uri): array
    {
        $changes = [];

        foreach ($route as $index => $route_part)
        {
            if (strpos(':', $route_part[0]) === 0)
            {
                $changes[$route[$index]] = $uri[$index];

                $route[$index] = $uri[$index];
            }
        }

        return [$route, $changes];
    }

    private function executeRoute(): bool
    {
        if (is_callable($this->route['handler'])) {

            call_user_func($this->route['handler'],
                $this,
                $this->data(),
                $this->response
            );

            return true;
        }

        $controller = $this->route['handler'];
        $controller_method = $this->route['action'];

        if (class_exists($controller)) {

            $controller = new $controller($this);

            if (method_exists($controller, $controller_method)) {

                $controller->$controller_method(
                    $this->data(),
                    $this->response
                );

                return true;
            }
        }

        $this->error = static::METHOD_NOT_ALLOWED;

        return false;
    }

    public function dispatch(): bool
    {
        if ($this->findRoute()) {

            return $this->executeRoute();
        }

        return false;
    }

    public function route(string $name, array $parameters = []): string|false
    {
        foreach ($this->routes as $method)
        {
            foreach ($method as $route)
            {
                if (isset($route['name']) && $route['name'] === $name) {
                    
                    $route = $this->normalizeRouteUsingParameters($route['route'], $parameters);
    
                    $url = $this->base_url['url'] . "/{$route}";
    
                    return $url;
                }
            }
        }

        return false;
    }

    protected function normalizeRouteUsingParameters(array $route, array $parameters): string
    {
        foreach ($parameters as $parameter => $value)
        {
            foreach ($route as $index => $route_part)
            {
                if (":{$parameter}" === $route_part)
                {
                    $route[$index] = $value;
                }   
            }
        }
        
        return implode('/', $route);
    }

    public function redirect(string $route, array $parameters = [])
    {
        if (filter_var($route, FILTER_VALIDATE_URL)) { Redirect::redirect(to: $route); }

        if (str_contains($route, '/'))
        {
            $route = $this->base_url['url'] . $route;

            Redirect::redirect(to: $route);
        }

        if ($route = $this->route($route, $parameters)) { Redirect::redirect(to: $route); }
    }

    public function error(): string|null
    {
        return isset($this->error) ? $this->error : null;
    }
}
