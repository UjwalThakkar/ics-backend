<?php

declare(strict_types=1);

namespace IndianConsular\Middleware;

class CorsMiddleware
{
    public static function handle(): void
    {
        $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000';
        $allowedOrigins = [
            $frontendUrl,
            'http://localhost:3000',
            'https://localhost:3000',
        ];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$origin}");
        }

        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400'); // 24 hours
    }
}
