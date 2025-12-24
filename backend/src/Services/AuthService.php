<?php

declare(strict_types=1);

namespace IndianConsular\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthService
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $expiration = 86400; // 24 hours
    private string $cookieName = 'ics_auth_token';
    private string $csrfCookieName = 'ics_csrf_token';

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'default-secret-key-change-this';
    }

    /**
     * Generate JWT token
     */
    public function generateToken(array $payload): string
    {
        $now = time();

        $tokenPayload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $this->expiration,
            'iss' => 'indian-consular-services'
        ]);

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    /**
     * Verify and decode JWT token
     */
    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            $payload = (array) $decoded;

            // Remove JWT standard claims
            unset($payload['iat'], $payload['exp'], $payload['iss']);

            return $payload;

        } catch (Exception $e) {
            error_log("JWT verification failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired(string $token): bool
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return false;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Refresh token (generate new token with same payload)
     */
    public function refreshToken(string $token): ?string
    {
        $payload = $this->verifyToken($token);

        if (!$payload) {
            return null;
        }

        return $this->generateToken($payload);
    }

    /**
     * Set JWT token in HTTP-only cookie
     */
    public function setAuthCookie(string $token): void
    {
        $isSecure = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        
        setcookie($this->cookieName, $token, [
            'expires' => time() + $this->expiration,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    /**
     * Clear auth cookie (logout)
     */
    public function clearAuthCookie(): void
    {
        setcookie($this->cookieName, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => ($_ENV['APP_ENV'] ?? 'development') === 'production',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        // Also clear CSRF cookie
        setcookie($this->csrfCookieName, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => ($_ENV['APP_ENV'] ?? 'development') === 'production',
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
    }

    /**
     * Get token from cookie
     */
    public function getTokenFromCookie(): ?string
    {
        return $_COOKIE[$this->cookieName] ?? null;
    }

    /**
     * Generate CSRF token and set in cookie (readable by JS)
     */
    public function generateCsrfToken(): string
    {
        $csrfToken = bin2hex(random_bytes(32));
        $isSecure = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        
        // Set CSRF token in a readable cookie (not httponly)
        setcookie($this->csrfCookieName, $csrfToken, [
            'expires' => time() + $this->expiration,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => false,  // JS needs to read this
            'samesite' => 'Lax'
        ]);
        
        return $csrfToken;
    }

    /**
     * Validate CSRF token from request header against cookie
     */
    public function validateCsrfToken(string $headerToken): bool
    {
        $cookieToken = $_COOKIE[$this->csrfCookieName] ?? '';
        
        if (empty($cookieToken) || empty($headerToken)) {
            return false;
        }
        
        return hash_equals($cookieToken, $headerToken);
    }

    /**
     * Get cookie name (for reference)
     */
    public function getCookieName(): string
    {
        return $this->cookieName;
    }
}

