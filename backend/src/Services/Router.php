<?php

declare(strict_types=1);

namespace IndianConsular\Services;

use IndianConsular\Controllers\ApplicationController;
use IndianConsular\Controllers\AdminController;
use IndianConsular\Controllers\AuthController;
use IndianConsular\Controllers\AppointmentController;
use IndianConsular\Controllers\ServiceController;
use IndianConsular\Controllers\FileController;

class Router
{
    private array $routes;

    public function __construct()
    {
        $this->routes = [
            // Authentication routes
            'POST /auth/login' => [AuthController::class, 'login'],
            'POST /auth/register' => [AuthController::class, 'register'],
            'POST /auth/logout' => [AuthController::class, 'logout'],
            'GET /auth/me' => [AuthController::class, 'me'],

            // Public application routes
            'POST /applications/submit' => [ApplicationController::class, 'submit'],
            'GET /applications/track/{id}' => [ApplicationController::class, 'track'],

            // Public service routes
            'GET /services' => [ServiceController::class, 'list'],
            'GET /services/{id}' => [ServiceController::class, 'get'],

            // Public appointment routes
            'POST /appointments/book' => [AppointmentController::class, 'book'],
            'GET /appointments/availability' => [AppointmentController::class, 'availability'],

            // File upload routes
            'POST /upload/secure' => [FileController::class, 'upload'],

            // Admin routes (require authentication)
            'GET /admin/dashboard/stats' => [AdminController::class, 'stats'],
            'GET /admin/applications' => [AdminController::class, 'getApplications'],
            'PUT /admin/applications/{id}' => [AdminController::class, 'updateApplication'],
            'DELETE /admin/applications/{id}' => [AdminController::class, 'deleteApplication'],
            'POST /admin/applications/bulk-update' => [AdminController::class, 'bulkUpdateApplications'],

            'GET /admin/appointments' => [AdminController::class, 'getAppointments'],
            'POST /admin/appointments' => [AdminController::class, 'createAppointment'],
            'PUT /admin/appointments/{id}' => [AdminController::class, 'updateAppointment'],
            'DELETE /admin/appointments/{id}' => [AdminController::class, 'deleteAppointment'],
            'POST /admin/appointments/bulk-create' => [AdminController::class, 'bulkCreateAppointments'],

            'GET /admin/users' => [AdminController::class, 'getUsers'],
            'POST /admin/users' => [AdminController::class, 'createUser'],
            'PUT /admin/users/{id}' => [AdminController::class, 'updateUser'],
            'DELETE /admin/users/{id}' => [AdminController::class, 'deleteUser'],

            'GET /admin/services' => [AdminController::class, 'getServices'],
            'POST /admin/services' => [AdminController::class, 'createService'],
            'PUT /admin/services/{id}' => [AdminController::class, 'updateService'],
            'DELETE /admin/services/{id}' => [AdminController::class, 'deleteService'],

            'GET /admin/analytics' => [AdminController::class, 'analytics'],
            'POST /admin/backup' => [AdminController::class, 'backup'],
            'GET /admin/system/status' => [AdminController::class, 'systemStatus'],

            // Notification routes
            'POST /admin/notifications/send' => [AdminController::class, 'sendNotification'],
            'GET /admin/notifications/templates' => [AdminController::class, 'getNotificationTemplates'],
            'POST /admin/notifications/templates' => [AdminController::class, 'createNotificationTemplate'],
        ];
    }

    public function handle(string $method, string $path): array
    {
        // Clean path
        $path = trim($path, '/');

        // Try exact match first
        $routeKey = "{$method} /{$path}";
        if (isset($this->routes[$routeKey])) {
            return $this->callController($this->routes[$routeKey], []);
        }

        // Try pattern matching for routes with parameters
        foreach ($this->routes as $pattern => $handler) {
            if ($this->matchRoute($method, $path, $pattern, $params)) {
                return $this->callController($handler, $params);
            }
        }

        // Route not found
        return [
            'status' => 404,
            'data' => [
                'success' => false,
                'error' => 'Route not found',
                'path' => $path,
                'method' => $method
            ]
        ];
    }

    private function matchRoute(string $method, string $path, string $pattern, &$params): bool
    {
        // Extract method and pattern path
        [$patternMethod, $patternPath] = explode(' ', $pattern, 2);
        $patternPath = trim($patternPath, '/');

        if ($patternMethod !== $method) {
            return false;
        }

        // Convert pattern to regex
        $regex = preg_replace('/\{([^}]+)\}/', '([^/]+)', $patternPath);
        $regex = "#^{$regex}$#";

        if (preg_match($regex, $path, $matches)) {
            // Extract parameter names
            preg_match_all('/\{([^}]+)\}/', $patternPath, $paramNames);

            $params = [];
            for ($i = 1; $i < count($matches); $i++) {
                $paramName = $paramNames[1][$i - 1] ?? "param{$i}";
                $params[$paramName] = $matches[$i];
            }

            return true;
        }

        return false;
    }

    private function callController(array $handler, array $params): array
    {
        [$controllerClass, $method] = $handler;

        try {
            $controller = new $controllerClass();

            // Get request data
            $requestData = $this->getRequestData();

            // Call controller method
            return $controller->$method($requestData, $params);

        } catch (\Exception $e) {
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'error' => 'Controller error',
                    'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Internal server error'
                ]
            ];
        }
    }

    private function getRequestData(): array
    {
        $data = [];

        // Get JSON body for POST/PUT requests
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'])) {
            $input = file_get_contents('php://input');
            if ($input) {
                $jsonData = json_decode($input, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = $jsonData;
                }
            }

            // Merge with POST data
            $data = array_merge($data, $_POST);
        }

        // Add GET parameters
        $data = array_merge($data, $_GET);

        // Add headers
        $data['_headers'] = getallheaders() ?: [];

        // Add files
        if (!empty($_FILES)) {
            $data['_files'] = $_FILES;
        }

        return $data;
    }
}
