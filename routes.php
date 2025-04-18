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

    // 👇 Debug line: shows what URI PHP is seeing
    echo "<pre>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "Parsed URI: $uri\n</pre>";

    foreach ($routes[$method] ?? [] as $route => $callback) {
        $pattern = "@^" . preg_replace('/\{([\w]+)\}/', '([\w-]+)', $route) . "$@";
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            return call_user_func_array($callback, $matches);
        }
    }

    http_response_code(404);
    echo "404 Not Found";
}

