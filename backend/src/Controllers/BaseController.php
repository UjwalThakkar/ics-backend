<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Services\AuthService;
use IndianConsular\Services\LogService;

abstract class BaseController
{
    protected AuthService $authService;
    protected LogService $logService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->logService = new LogService();
    }

    /**
     * Return success response
     */
    protected function success(array $data = [], int $status = 200): array
    {
        return [
            'status' => $status,
            'data' => array_merge(['success' => true], $data)
        ];
    }

    /**
     * Return error response
     */
    protected function error(string $message, int $status = 400, array $details = []): array
    {
        $data = [
            'success' => false,
            'error' => $message
        ];

        if (!empty($details)) {
            $data['details'] = $details;
        }

        return [
            'status' => $status,
            'data' => $data
        ];
    }

    /**
     * Validate required fields
     */
    protected function validateRequired(array $data, array $required): array
    {
        $missing = [];

        foreach ($required as $field) {
            // For array fields, allow empty arrays (they're valid)
            if (in_array($field, ['fees', 'required_documents', 'eligibility_requirements'])) {
                if (!isset($data[$field])) {
                    $missing[] = $field;
                }
            } else {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $missing[] = $field;
                }
            }
        }

        return $missing;
    }

    /**
     * Get authenticated admin user
     */
    protected function requireAuth(array $data): ?array
    {
        $token = $this->extractToken($data);

        if (!$token) {
            return null;
        }

        return $this->authService->verifyToken($token);
    }

    /**
     * Require admin authentication - returns admin data or null
     * Use this for admin-only endpoints
     */
    protected function requireAdminAuth(array $data): ?array
    {
        $auth = $this->requireAuth($data);
        
        if (!$auth || ($auth['type'] ?? '') !== 'admin') {
            return null;
        }
        
        return $auth;
    }

    /**
     * Require user authentication - returns user data or null
     * Use this for user-only endpoints
     */
    protected function requireUserAuth(array $data): ?array
    {
        $auth = $this->requireAuth($data);
        
        if (!$auth || ($auth['type'] ?? '') !== 'user') {
            return null;
        }
        
        return $auth;
    }

    /**
     * Extract JWT token from request
     * Enforces usage of HTTP-only cookie.
     */
    protected function extractToken(array $data): ?string
    {
        // Only accept HTTP-only cookie
        return $this->authService->getTokenFromCookie();
    }

    /**
     * Get client IP address
     */
    protected function getClientIp(): string
    {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_CF_CONNECTING_IP'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get user agent
     */
    protected function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }

    /**
     * Sanitize input data
     */
    protected function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Generate unique ID
     */
    protected function generateId(string $prefix = ''): string
    {
        return $prefix . strtoupper(bin2hex(random_bytes(6)));
    }

    /**
     * Paginate results
     */
    protected function paginate(array $data, int $page = 1, int $limit = 10): array
    {
        $total = count($data);
        $totalPages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;

        $items = array_slice($data, $offset, $limit);

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => $totalPages,
                'hasNext' => $page < $totalPages,
                'hasPrev' => $page > 1
            ]
        ];
    }
}
