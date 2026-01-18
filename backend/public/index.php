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
error_log("index.php: Before CORS middleware");
CorsMiddleware::handle();
error_log("index.php: After CORS middleware");

// Set headers for JSON API (can be overridden by controllers for file downloads)
// Only set if headers haven't been sent yet
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}
error_log("index.php: Headers set");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("index.php: OPTIONS request, exiting");
    http_response_code(200);
    exit();
}

error_log("index.php: Starting request processing");

try {
    // Initialize database connection with error handling
    error_log("index.php: Attempting database connection");
    try {
        Connection::initialize();
        error_log("index.php: Database connection successful");
    } catch (Exception $dbException) {
        error_log("Database connection error: " . $dbException->getMessage());
        error_log("Database connection stack trace: " . $dbException->getTraceAsString());
        // Don't fail the request if DB connection fails - some endpoints might not need DB
        // But log it for debugging
    }

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
    error_log("index.php: Creating router");
    $router = new Router();
    error_log("index.php: Router created, calling handle()");
    $response = $router->handle($method, $path);
    error_log("index.php: Router handle() completed, response status: " . ($response['status'] ?? 'N/A'));

    // Check if response was already handled (e.g., file download)
    if (isset($response['data']['_handled']) && $response['data']['_handled'] === true) {
        // Response was already sent (headers, file content, etc.)
        exit;
    }

    // Send JSON response
    error_log("index.php: Preparing to send response");
    http_response_code($response['status']);
    $jsonResponse = json_encode($response['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    error_log("index.php: JSON encoded, length: " . strlen($jsonResponse));
    echo $jsonResponse;
    error_log("index.php: Response echoed");
    
    // Ensure output is flushed
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        flush();
    }
    error_log("index.php: Response flushed, request complete");

} catch (Exception $e) {
    // Handle errors
    error_log("index.php: Exception caught: " . $e->getMessage());
    error_log("index.php: Exception trace: " . $e->getTraceAsString());
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
