<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use IndianConsular\Database\Connection;
use IndianConsular\Middleware\CorsMiddleware;
use IndianConsular\Services\Router;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] === 'true' ? '1' : '0');

// Set headers for JSON API
header('Content-Type: application/json; charset=utf-8');

// Handle CORS
CorsMiddleware::handle();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Initialize database connection
    Connection::initialize();

    // Get request info
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Remove base path if needed
    $basePath = '/api';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }

    // Initialize router and handle request
    $router = new Router();
    $response = $router->handle($method, $path);

    // Send response
    http_response_code($response['status']);
    echo json_encode($response['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Handle errors
    http_response_code(500);

    $errorResponse = [
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
    ];

    if ($_ENV['APP_DEBUG'] === 'true') {
        $errorResponse['trace'] = $e->getTraceAsString();
    }

    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
