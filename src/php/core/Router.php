<?php
/**
 * Router Class
 * 
 * Handles URL routing to controllers based on defined routes
 * Maps URL patterns to controller actions
 * 
 * @package ProductivityHub\Core
 * @author Edwardking (Edd)
 * @version 1.0
 */

declare(strict_types=1);

namespace ProductivityHub\Core;

class Router
{
    /**
     * Array of registered routes
     * 
     * @var array
     */
    private array $routes = [];
    
    /**
     * Base path for the application
     * 
     * @var string
     */
    private string $basePath = '';
    
    /**
     * Constructor
     * 
     * @param string $basePath Base path for the application
     */
    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath;
    }
    
    /**
     * Add a route to the router
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $pattern URL pattern to match
     * @param array|callable $handler Controller and action or callable
     * @return self
     */
    public function addRoute(string $method, string $pattern, $handler): self
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $this->basePath . $pattern,
            'handler' => $handler
        ];
        
        return $this;
    }
    
    /**
     * Add a GET route
     * 
     * @param string $pattern URL pattern
     * @param array|callable $handler Controller and action or callable
     * @return self
     */
    public function get(string $pattern, $handler): self
    {
        return $this->addRoute('GET', $pattern, $handler);
    }
    
    /**
     * Add a POST route
     * 
     * @param string $pattern URL pattern
     * @param array|callable $handler Controller and action or callable
     * @return self
     */
    public function post(string $pattern, $handler): self
    {
        return $this->addRoute('POST', $pattern, $handler);
    }
    
    /**
     * Add a PUT route
     * 
     * @param string $pattern URL pattern
     * @param array|callable $handler Controller and action or callable
     * @return self
     */
    public function put(string $pattern, $handler): self
    {
        return $this->addRoute('PUT', $pattern, $handler);
    }
    
    /**
     * Add a DELETE route
     * 
     * @param string $pattern URL pattern
     * @param array|callable $handler Controller and action or callable
     * @return self
     */
    public function delete(string $pattern, $handler): self
    {
        return $this->addRoute('DELETE', $pattern, $handler);
    }
    
    /**
     * Match the current request to a route and dispatch the handler
     * 
     * @param string $method HTTP method
     * @param string $uri Request URI
     * @return mixed The result of the handler
     * @throws \Exception If no route matches
     */
    public function dispatch(string $method, string $uri)
    {
        $method = strtoupper($method);
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Remove trailing slash
        $uri = rtrim($uri, '/');
        
        // If empty URI, set to root
        if (empty($uri)) {
            $uri = '/';
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = $this->preparePattern($route['pattern']);
            
            if (preg_match($pattern, $uri, $matches)) {
                // Remove the full match
                array_shift($matches);
                
                // Handle named parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    } else {
                        $params[] = $value;
                    }
                }
                
                return $this->executeHandler($route['handler'], $params);
            }
        }
        
        throw new \Exception("No route found for $method $uri", 404);
    }
    
    /**
     * Prepare a route pattern for regex matching
     * 
     * @param string $pattern Route pattern
     * @return string Regex pattern
     */
    private function preparePattern(string $pattern): string
    {
        // Replace named parameters like {id} with regex capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?<$1>[^/]+)', $pattern);
        
        // Escape forward slashes for regex
        $pattern = str_replace('/', '\/', $pattern);
        
        // Add start and end anchors
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Execute a route handler
     * 
     * @param array|callable $handler Route handler
     * @param array $params Route parameters
     * @return mixed The result of the handler
     * @throws \Exception If handler is invalid
     */
    private function executeHandler($handler, array $params = [])
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        if (is_array($handler) && count($handler) === 2) {
            [$controllerName, $action] = $handler;
            
            if (is_string($controllerName) && class_exists($controllerName)) {
                $controller = new $controllerName();
                
                if (method_exists($controller, $action)) {
                    return call_user_func_array([$controller, $action], $params);
                }
                
                throw new \Exception("Method $action not found in controller $controllerName", 500);
            }
        }
        
        throw new \Exception("Invalid route handler", 500);
    }
}
