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
}
