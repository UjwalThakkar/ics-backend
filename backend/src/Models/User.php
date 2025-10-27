<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class User extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Find user by user_id
     */
    public function findByUserId(string $userId): ?array
    {
        return $this->findBy('user_id', $userId);
    }

    /**
     * Find user by passport number
     */
    public function findByPassportNumber(string $passportNumber): ?array
    {
        return $this->findBy('passport_number', $passportNumber);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): int
    {
        $userData = [
            'user_id' => $data['user_id'] ?? $this->generateUserId(),
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'passport_number' => $data['passport_number'] ?? null,
            'account_status' => $data['account_status'] ?? 'pending',
            'email_verified' => $data['email_verified'] ?? false,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($userData);
    }

    /**
     * Update user information
     */
    public function updateUser(string $userId, array $data): bool
    {
        $updateData = [];

        $allowedFields = [
            'email',
            'first_name',
            'last_name',
            'phone',
            'date_of_birth',
            'nationality',
            'passport_number',
            'account_status',
            'email_verified'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        // Hash password if provided
        if (isset($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        return $this->updateBy('user_id', $userId, $updateData);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(string $userId): bool
    {
        return $this->updateBy('user_id', $userId, [
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Verify email for a user
     */
    public function verifyEmail(string $userId): bool
    {
        return $this->updateBy('user_id', $userId, [
            'email_verified' => true,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get all active users
     */
    public function getActiveUsers(): array
    {
        return $this->findAll(['account_status' => 'active', 'email_verified' => 1], 'created_at DESC');
    }

    /**
     * Get users by nationality
     */
    public function getUsersByNationality(string $nationality): array
    {
        return $this->findAll(['nationality' => $nationality, 'account_status' => 'active'], 'created_at DESC');
    }

    /**
     * Deactivate user
     */
    public function deactivateUser(string $userId): bool
    {
        return $this->updateBy('user_id', $userId, [
            'account_status' => 'inactive',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, string $excludeUserId = ''): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }

        $stmt = $this->query($sql, $params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if passport number exists
     */
    public function passportNumberExists(string $passportNumber, string $excludeUserId = ''): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE passport_number = ?";
        $params = [$passportNumber];

        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }

        $stmt = $this->query($sql, $params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Generate unique user ID
     */
    private function generateUserId(): string
    {
        do {
            $userId = 'USR' . strtoupper(bin2hex(random_bytes(4)));
        } while ($this->findByUserId($userId));

        return $userId;
    }
}