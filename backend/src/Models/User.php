<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class User extends BaseModel
{
    protected string $table = 'user';
    protected string $primaryKey = 'user_id';

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
    public function findByUserId(int $userId): ?array
    {
        return $this->find($userId);
    }

    /**
     * Find user by passport number
     */
    public function findByPassportNumber(string $passportNumber): ?array
    {
        return $this->findBy('passport_no', $passportNumber);
    }

    /**
     * Create a new user (registration)
     */
    public function createUser(array $data): int
    {
        $userData = [
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone_no' => $data['phone_no'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'passport_no' => $data['passport_no'] ?? null,
            'passport_expiry' => $data['passport_expiry'] ?? null,
            'account_status' => $data['account_status'] ?? 'active',
            'email_validated' => $data['email_validated'] ?? 0
        ];

        return $this->insert($userData);
    }

    /**
     * Update user information
     */
    public function updateUser(int $userId, array $data): bool
    {
        $updateData = [];

        $allowedFields = [
            'email',
            'first_name',
            'last_name',
            'phone_no',
            'gender',
            'date_of_birth',
            'nationality',
            'passport_no',
            'passport_expiry',
            'account_status',
            'email_validated'
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

        if (empty($updateData)) {
            return true;
        }

        $sql = "UPDATE user SET " . implode(', ', array_map(fn($k) => "$k = ?", array_keys($updateData))) .
            ", updated_at = NOW() WHERE user_id = ?";

        $params = array_merge(array_values($updateData), [$userId]);
        $stmt = $this->query($sql, $params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Verify email for a user
     */
    public function verifyEmail(int $userId): bool
    {
        $sql = "UPDATE user SET email_validated = 1, updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Verify user credentials (login)
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        // Check if account is active
        if ($user['account_status'] !== 'active') {
            return null;
        }

        return $user;
    }

    /**
     * Get all active users
     */
    public function getActiveUsers(): array
    {
        return $this->findAll(['account_status' => 'active', 'email_validated' => 1], 'created_at DESC');
    }

    /**
     * Get users by nationality
     */
    public function getUsersByNationality(string $nationality): array
    {
        return $this->findAll(['nationality' => $nationality, 'account_status' => 'active'], 'created_at DESC');
    }

    /**
     * Get users by account status
     */
    public function getUsersByStatus(string $status): array
    {
        return $this->findAll(['account_status' => $status], 'created_at DESC');
    }

    /**
     * Deactivate user
     */
    public function deactivateUser(int $userId): bool
    {
        $sql = "UPDATE user SET account_status = 'inactive', updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Suspend user
     */
    public function suspendUser(int $userId): bool
    {
        $sql = "UPDATE user SET account_status = 'suspended', updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Activate user
     */
    public function activateUser(int $userId): bool
    {
        $sql = "UPDATE user SET account_status = 'active', updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM user WHERE email = ?";
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
    public function passportNumberExists(string $passportNumber, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM user WHERE passport_no = ? AND passport_no IS NOT NULL";
        $params = [$passportNumber];

        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }

        $stmt = $this->query($sql, $params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Update user profile from appointment booking
     */
    public function updateProfileFromBooking(int $userId, array $data): bool
    {
        $updateData = [];

        // Only update if values are provided and not empty
        if (!empty($data['gender'])) {
            $updateData['gender'] = $data['gender'];
        }

        if (!empty($data['phone_no'])) {
            $updateData['phone_no'] = $data['phone_no'];
        }

        if (!empty($data['date_of_birth'])) {
            $updateData['date_of_birth'] = $data['date_of_birth'];
        }

        if (!empty($data['nationality'])) {
            $updateData['nationality'] = $data['nationality'];
        }

        if (!empty($data['passport_no'])) {
            $updateData['passport_no'] = $data['passport_no'];
        }

        if (!empty($data['passport_expiry'])) {
            $updateData['passport_expiry'] = $data['passport_expiry'];
        }

        // Only update if there are fields to update
        if (empty($updateData)) {
            return true;
        }

        $sql = "UPDATE user SET " . implode(', ', array_map(fn($k) => "$k = ?", array_keys($updateData))) .
            ", updated_at = NOW() WHERE user_id = ?";

        $params = array_merge(array_values($updateData), [$userId]);
        $stmt = $this->query($sql, $params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Update password
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE user SET password_hash = ?, updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->query($sql, [$passwordHash, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        $stats = [];

        // Total users
        $stmt = $this->query("SELECT COUNT(*) as total FROM user");
        $stats['total'] = (int)$stmt->fetch()['total'];

        // By account status
        $stmt = $this->query("SELECT account_status, COUNT(*) as count FROM user GROUP BY account_status");
        foreach ($stmt->fetchAll() as $row) {
            $stats['status_' . $row['account_status']] = (int)$row['count'];
        }

        // Email validated
        $stmt = $this->query("SELECT COUNT(*) as count FROM user WHERE email_validated = 1");
        $stats['email_validated'] = (int)$stmt->fetch()['count'];

        // With passport info
        $stmt = $this->query("SELECT COUNT(*) as count FROM user WHERE passport_no IS NOT NULL");
        $stats['with_passport'] = (int)$stmt->fetch()['count'];

        // Recent registrations (last 30 days)
        $stmt = $this->query("SELECT COUNT(*) as count FROM user WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['recent_registrations'] = (int)$stmt->fetch()['count'];

        return $stats;
    }

    /**
     * Search users
     */
    public function searchUsers(string $searchTerm, int $limit = 50): array
    {
        $searchPattern = '%' . $searchTerm . '%';
        $sql = "SELECT * FROM user 
                WHERE first_name LIKE ? 
                OR last_name LIKE ? 
                OR email LIKE ? 
                OR passport_no LIKE ?
                ORDER BY created_at DESC
                LIMIT ?";

        $stmt = $this->query($sql, [$searchPattern, $searchPattern, $searchPattern, $searchPattern, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get user with appointment count
     */
    public function getUserWithAppointmentCount(int $userId): ?array
    {
        $sql = "SELECT 
                    u.*,
                    COUNT(a.appointment_id) as total_appointments,
                    SUM(CASE WHEN a.appointment_status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                    SUM(CASE WHEN a.appointment_status = 'scheduled' THEN 1 ELSE 0 END) as upcoming_appointments
                FROM user u
                LEFT JOIN appointment a ON u.user_id = a.booked_by
                WHERE u.user_id = ?
                GROUP BY u.user_id";

        $stmt = $this->query($sql, [$userId]);
        return $stmt->fetch() ?: null;
    }


}
