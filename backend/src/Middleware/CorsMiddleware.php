<?php

declare(strict_types=1);

namespace IndianConsular\Middleware;

class CorsMiddleware
{
    public static function handle(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? 'no-origin-header';
        error_log("CORS Origin received: " . $origin);
        $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000';
        $allowedOrigins = [
            $frontendUrl,
            'http://localhost:3000',
            'https://localhost:3000',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:3000',
            'http://145.223.18.182:3000',
            'https://145.223.18.182:3000',

        ];

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

            error_log("OPTIONS request - Origin: " . ($origin ?: 'missing'));

            // Pick exact origin if allowed, fallback to request origin in dev
            if (in_array($origin, $allowedOrigins)) {
                header("Access-Control-Allow-Origin: $origin");
            } else {
                // Dev fallback - be careful, remove in prod
                header("Access-Control-Allow-Origin: $origin");
            }

            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept, X-Requested-With, X-CSRF-Token, x-xsrf-token, X-XSRF-TOKEN');
            header('Access-Control-Max-Age: 86400');
            header('Content-Length: 0');           // helps some servers/browsers
            http_response_code(204);               // 204 No Content is preferred for OPTIONS
            exit;                                  // ← critical: stop here
        }
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        error_log("Non-OPTIONS request - Origin: " . ($origin ?: 'missing'));

        // Determine which origin to allow
        $isDevelopment = ($_ENV['APP_ENV'] ?? 'development') !== 'production';
        
        // If there's an origin header, we MUST set CORS headers (browser requirement)
        if (!empty($origin)) {
            // Clean the origin (remove any trailing issues)
            $origin = trim($origin);
            
            // Determine allowed origin
            $allowedOrigin = null;
            if (in_array($origin, $allowedOrigins)) {
                // Origin is in allowed list
                $allowedOrigin = $origin;
            } elseif ($isDevelopment) {
                // For development: always allow the origin (makes debugging easier)
                $allowedOrigin = $origin;
            }
            
            // Set CORS headers - CRITICAL: Cannot use * with credentials
            // If we have an origin, we MUST set the header (browser requirement for CORS)
            if ($allowedOrigin) {
                header("Access-Control-Allow-Origin: {$allowedOrigin}");
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
                header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept, X-Requested-With, X-CSRF-Token, x-xsrf-token, X-XSRF-TOKEN');
                header('Access-Control-Max-Age: 86400'); // 24 hours
            }
        }
        
        error_log("CORS middleware: Completed successfully");
    }
}
