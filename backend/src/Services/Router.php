<?php

declare(strict_types=1);

namespace IndianConsular\Services;

use IndianConsular\Controllers\AuthController;
use IndianConsular\Controllers\ApplicationController;
use IndianConsular\Controllers\BookingController;
use IndianConsular\Controllers\AppointmentController;
use IndianConsular\Controllers\ServiceController;
use IndianConsular\Controllers\VerificationCenterController;
use IndianConsular\Controllers\AdminController;
use IndianConsular\Controllers\CounterController;
use IndianConsular\Controllers\ServiceDetailsController;
use IndianConsular\Controllers\TimeSlotController;
use IndianConsular\Controllers\VisaController;
use IndianConsular\Controllers\OcrController;

class Router
{
    private array $routes;

    public function __construct()
    {
        $this->routes = [


            // =============================================
            // AUTHENTICATION ROUTES
            // =============================================
            'POST /auth/login' => [AuthController::class, 'login'],
            'POST /auth/register' => [AuthController::class, 'register'],
            'POST /auth/logout' => [AuthController::class, 'logout'],
            'GET /auth/me' => [AuthController::class, 'me'],
            'PUT /auth/profile' => [AuthController::class, 'updateProfile'],
            'POST /auth/change-password' => [AuthController::class, 'changePassword'],
            'POST /auth/verify-email' => [AuthController::class, 'verifyEmail'],

            // =============================================
            // BOOKING FLOW ROUTES (Public & Authenticated)
            // =============================================
            // Step 1: Get services
            'GET /booking/services' => [BookingController::class, 'getServices'],

            // Step 2: Get centers for service
            'GET /booking/centers/{serviceId}' => [BookingController::class, 'getCentersForService'],

            // Step 3: Get user details (authenticated)
            'GET /booking/user-details' => [BookingController::class, 'getUserDetails'],

            // Step 4: Get available dates
            'GET /booking/available-dates' => [BookingController::class, 'getAvailableDates'],

            // Step 5: Get available slots
            'GET /booking/available-slots' => [BookingController::class, 'getAvailableSlots'],

            // Step 6: Create booking (authenticated)
            'POST /booking/create' => [BookingController::class, 'createBooking'],

            // Step 7: Get confirmation (authenticated)
            'GET /booking/confirmation/{bookingId}' => [BookingController::class, 'getConfirmation'],

            // My bookings (authenticated)
            'GET /booking/my-bookings' => [BookingController::class, 'getMyBookings'],

            // Cancel booking (authenticated)
            'POST /booking/cancel/{bookingId}' => [BookingController::class, 'cancelBooking'],

            // Booking settings
            'GET /booking/settings' => [BookingController::class, 'getSettings'],

            // =============================================
            // PUBLIC SERVICE ROUTES
            // =============================================
            'GET /services' => [ServiceController::class, 'list'],
            'GET /services/categories' => [ServiceController::class, 'categories'],
            'GET /services/search' => [ServiceController::class, 'search'],
            'GET /services/category/{category}' => [ServiceController::class, 'byCategory'],
            'GET /services/{id}' => [ServiceController::class, 'get'],

            // =============================================
            // ADMIN SERVICE ROUTES
            // =============================================
            'GET /admin/services' => [ServiceController::class, 'adminList'],
            'POST /admin/services' => [ServiceController::class, 'addService'],
            'PUT /admin/services/{id}' => [ServiceController::class, 'editService'],
            // 'DELETE /admin/services/{id}' => [ServiceController::class, 'deleteService'],
            'PUT /admin/services/{id}/toggle' => [ServiceController::class, 'toggleActive'],
            'GET /admin/services/{id}' => [ServiceController::class, 'adminGet'],

            // =============================================
            // VERIFICATION CENTERS ROUTES (Public)
            // =============================================
            'GET /centers' => [VerificationCenterController::class, 'list'],
            'GET /centers/{id}' => [VerificationCenterController::class, 'get'],
            'GET /centers/{id}/services' => [VerificationCenterController::class, 'getCenterServices'],
            'GET /centers/city/{city}' => [VerificationCenterController::class, 'getByCity'],
            'GET /centers/country/{country}' => [VerificationCenterController::class, 'getByCountry'],
            'GET /centers/nearby' => [VerificationCenterController::class, 'searchNearby'],
            'GET /centers/{id}/available-slots' => [VerificationCenterController::class, 'getAvailableSlots'],
            // 'GET /centers/active' => [VerificationCenterController::class, 'getActiveCenters'],

            // ADMIN COUNTER ROUTES
            'GET /admin/counters' => [CounterController::class, 'adminList'],
            'POST /admin/counters' => [CounterController::class, 'create'],
            'POST /admin/counters/{id}/toggle' => [CounterController::class, 'toggleActive'],
            'PUT /admin/counters/{id}/services' => [CounterController::class, 'updateServices'],
            'GET /admin/counters/{id}'     => [CounterController::class, 'get'],
            'PUT /admin/counters/{id}'     => [CounterController::class, 'update'],

            // =============================================
            // USER APPOINTMENT ROUTES (Authenticated)
            // =============================================
            'GET /appointments' => [AppointmentController::class, 'getMyAppointments'],
            'GET /appointments/{id}' => [AppointmentController::class, 'getAppointment'],
            'POST /appointments/{id}/cancel' => [AppointmentController::class, 'cancelAppointment'],
            'GET /appointments/stats' => [AppointmentController::class, 'getStats'],

            // =============================================
            // ADMIN DASHBOARD ROUTES
            // =============================================
            'GET /admin/stats' => [AdminController::class, 'stats'],
            'GET /admin/system/status' => [AdminController::class, 'systemStatus'],
            'POST /admin/backup' => [AdminController::class, 'backup'],

            // =============================================
            // ADMIN SERVICE ROUTES
            // =============================================
            'GET /admin/services' => [ServiceController::class, 'adminList'],
            'POST /admin/services' => [ServiceController::class, 'create'],
            'PUT /admin/services/{id}' => [ServiceController::class, 'update'],
            'DELETE /admin/services/{id}' => [ServiceController::class, 'delete'],
            'POST /admin/services/{id}/toggle' => [ServiceController::class, 'toggleActive'],

            // =============================================
            // ADMIN CENTER ROUTES
            // =============================================
            'GET /admin/centers' => [VerificationCenterController::class, 'adminList'],
            'POST /admin/centers' => [VerificationCenterController::class, 'create'],
            'PUT /admin/centers/{id}' => [VerificationCenterController::class, 'update'],
            'POST /admin/centers/{id}/toggle' => [VerificationCenterController::class, 'toggleActive'],
            'GET /admin/centers/{id}/services' => [VerificationCenterController::class, 'adminGetCenterServices'],

            // =============================================
            // ADMIN APPOINTMENT ROUTES
            // =============================================
            'GET /admin/appointments' => [AppointmentController::class, 'adminList'],
            'GET /admin/appointments/{id}' => [AppointmentController::class, 'adminGetAppointment'],
            'PUT /admin/appointments/{id}/status' => [AppointmentController::class, 'updateStatus'],
            'GET /admin/appointments/stats' => [AppointmentController::class, 'adminGetStats'],
            'GET /admin/appointments/upcoming' => [AppointmentController::class, 'getUpcoming'],
            'GET /admin/appointments/date-range' => [AppointmentController::class, 'getByDateRange'],

            // =============================================
            // ADMIN TIME SLOT ROUTES
            // =============================================
            'GET /admin/time-slots' => [TimeSlotController::class, 'adminList'],
            'PUT /admin/time-slots/settings' => [TimeSlotController::class, 'updateSettings'],
            'PUT /admin/time-slots/{id}/toggle' => [TimeSlotController::class, 'toggleSlot'],
            'POST /admin/time-slots/bulk-toggle' => [TimeSlotController::class, 'bulkToggle'],
            'POST /admin/time-slots' => [TimeSlotController::class, 'createSlot'],
            'POST /admin/time-slots/bulk-create' => [TimeSlotController::class, 'bulkCreate'],
            'PUT /admin/time-slots/{id}' => [TimeSlotController::class, 'updateSlot'],
            'DELETE /admin/time-slots/{id}' => [TimeSlotController::class, 'deleteSlot'],

            // =============================================
            // VISA INFORMATION ROUTES (Public - VFS Style)
            // =============================================
            'GET /visa/countries'                  => [VisaController::class, 'getCountries'],
            'GET /visa/country/{country_slug}'     => [VisaController::class, 'getCountryDetail'],
            'GET /visa/type'                   => [VisaController::class, 'getVisaTypeDetail'],
            'GET /visa/type/{visa_slug}'           => [VisaController::class, 'getVisaTypeDetail'],

            // =============================================
            // PUBLIC SERVICE DETAILS ROUTES
            // =============================================
            'GET /service-details/{serviceId}' => [ServiceDetailsController::class, 'get'],

            // =============================================
            // ADMIN SERVICE DETAILS ROUTES
            // =============================================
            'GET /admin/service-details' => [ServiceDetailsController::class, 'adminList'],
            'POST /admin/service-details' => [ServiceDetailsController::class, 'adminCreate'],
            'PUT /admin/service-details/{serviceId}' => [ServiceDetailsController::class, 'adminUpdate'],
            'DELETE /admin/service-details/{serviceId}' => [ServiceDetailsController::class, 'adminDelete'],

            // =============================================
            // OCR DOCUMENT EXTRACTION ROUTES
            // =============================================
            'POST /ocr/extract' => [OcrController::class, 'extractFromDocument'],
            'GET /ocr/health' => [OcrController::class, 'health'],

            // =============================================
            // MISCELLANEOUS APPLICATIONS ROUTES (User)
            // =============================================
            'POST /applications/miscellaneous/submit' => [ApplicationController::class, 'submitMiscellaneous'],

            // =============================================
            // ADMIN MISCELLANEOUS APPLICATIONS ROUTES
            // =============================================
            'GET /admin/applications/miscellaneous' => [ApplicationController::class, 'adminListMiscellaneous'],
            'GET /admin/applications/miscellaneous/stats' => [ApplicationController::class, 'adminGetStats'],
            'GET /admin/applications/miscellaneous/{id}' => [ApplicationController::class, 'adminGetMiscellaneous'],
            'PUT /admin/applications/miscellaneous/{id}' => [ApplicationController::class, 'adminUpdateMiscellaneous'],
            'GET /admin/applications/miscellaneous/{id}/files/{fileId}' => [ApplicationController::class, 'adminGetFile'],
            'GET /admin/applications/miscellaneous/{id}/files/{fileId}/download' => [ApplicationController::class, 'adminDownloadFile'],
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
            error_log("Controller error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

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

    /**
     * Get all registered routes (for documentation/debugging)
     */
    public function getRoutes(): array
    {
        return array_keys($this->routes);
    }

    /**
     * Check if route exists
     */
    public function routeExists(string $method, string $path): bool
    {
        $path = trim($path, '/');
        $routeKey = "{$method} /{$path}";

        if (isset($this->routes[$routeKey])) {
            return true;
        }

        foreach ($this->routes as $pattern => $handler) {
            if ($this->matchRoute($method, $path, $pattern, $params)) {
                return true;
            }
        }

        return false;
    }
}
