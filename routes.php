<?php
// routes.php

$routes = [];

// Register GET route
function get($route, $callback) {
    global $routes;
    $routes['GET'][$route] = $callback;
}

// Register POST route
function post($route, $callback) {
    global $routes;
    $routes['POST'][$route] = $callback;
}

// Dispatch route
function dispatch() {
    global $routes;

    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');
    // Handle root path
    if ($uri === '') {
        $uri = '/';
    }
    
    // If URI doesn't contain .php but we have it registered with .php, try the non-.php version first
    $found = false;
    
    foreach ($routes[$method] ?? [] as $route => $callback) {
        $pattern = "@^" . preg_replace('/\{([\w]+)\}/', '([\w-]+)', $route) . "$@";
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            call_user_func_array($callback, $matches);
            $found = true;
            break;
        }
    }
    
    // If not found and doesn't have .php extension, try adding it
    if (!$found && strpos($uri, '.php') === false) {
        $uri_with_php = $uri . '.php';
        foreach ($routes[$method] ?? [] as $route => $callback) {
            $pattern = "@^" . preg_replace('/\{([\w]+)\}/', '([\w-]+)', $route) . "$@";
            if (preg_match($pattern, $uri_with_php, $matches)) {
                array_shift($matches);
                call_user_func_array($callback, $matches);
                $found = true;
                break;
            }
        }
    }
    
    if (!$found) {
        http_response_code(404);
        echo "404 Not Found";
    }
}