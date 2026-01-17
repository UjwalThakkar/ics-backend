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
            'http://145.223.18.182:3000',
            'https://145.223.18.182:3000',
        ];

        // Add VPS IP if provided in env
        if (!empty($_ENV['VPS_IP'])) {
            $vpsIp = $_ENV['VPS_IP'] ?? 'http://145.223.18.182:3000';
            $allowedOrigins[] = "http://{$vpsIp}";
            $allowedOrigins[] = "http://{$vpsIp}:3000";
            $allowedOrigins[] = "http://{$vpsIp}:80";
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // If origin matches allowed list, set it
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$origin}");
        } elseif (!empty($origin) && (strpos($origin, 'http://') === 0 || strpos($origin, 'https://') === 0)) {
            // For development: allow any origin that looks valid (IP addresses, localhost, etc.)
            // In production, you should restrict this
            $isDevelopment = ($_ENV['APP_ENV'] ?? 'development') !== 'production';
            if ($isDevelopment) {
                header("Access-Control-Allow-Origin: {$origin}");
            }
        } else {
            // Fallback: if no origin header, allow requests (for same-origin or server-to-server)
            // But still set credentials header
        }

        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        header('Access-Control-Max-Age: 86400'); // 24 hours
    }
}
