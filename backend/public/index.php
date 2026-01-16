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

// Handle CORS first (before any headers are sent)
CorsMiddleware::handle();

// Set headers for JSON API (can be overridden by controllers for file downloads)
// Only set if headers haven't been sent yet
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

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
    
    // Get the request URI and extract just the path
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    
    // Handle case where REQUEST_URI might contain full URL or malformed path
    // Remove any leading slash that might be before http://
    if (strpos($requestUri, '/http://') === 0) {
        $requestUri = substr($requestUri, 1);
    } elseif (strpos($requestUri, '/https://') === 0) {
        $requestUri = substr($requestUri, 1);
    }
    
    // Extract path from URI
    if (strpos($requestUri, 'http://') === 0 || strpos($requestUri, 'https://') === 0) {
        // Full URL - extract path component
        $path = parse_url($requestUri, PHP_URL_PATH);
        if ($path === null) {
            // Fallback: try to extract manually
            $parts = parse_url($requestUri);
            $path = $parts['path'] ?? '/';
        }
    } else {
        // Already a path
        $path = parse_url($requestUri, PHP_URL_PATH);
        if ($path === null) {
            $path = $requestUri;
        }
    }
    
    // Ensure path is valid
    if (empty($path) || $path === '/') {
        $path = '/';
    }
    
    // Remove base path if needed
    $basePath = '/api';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    
    // Clean up the path - remove leading/trailing slashes
    $path = trim($path, '/');
    
    // Debug logging
    error_log("index.php: REQUEST_URI = " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
    error_log("index.php: Cleaned path = " . $path);

    // Initialize router and handle request
    $router = new Router();
    $response = $router->handle($method, $path);

    // Check if response was already handled (e.g., file download)
    if (isset($response['data']['_handled']) && $response['data']['_handled'] === true) {
        // Response was already sent (headers, file content, etc.)
        exit;
    }

    // Send JSON response
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
