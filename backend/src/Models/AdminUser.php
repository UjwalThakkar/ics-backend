<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class AdminUser extends BaseModel
{
    protected string $table = 'admin_users';
    protected string $primaryKey = 'id';

    /**
     * Find admin by username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    /**
     * Find admin by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Find admin by admin_id
     */
    public function findByAdminId(string $adminId): ?array
    {
        return $this->findBy('admin_id', $adminId);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(string $adminId): bool
    {
        return $this->updateBy('admin_id', $adminId, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Create new admin user
     */
    public function createAdmin(array $data): int
    {
        $adminData = [
            'admin_id' => $data['admin_id'] ?? $this->generateAdminId(),
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'] ?? 'officer',
            'permissions' => json_encode($data['permissions'] ?? []),
            'is_active' => $data['is_active'] ?? true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($adminData);
    }

    /**
     * Update admin user
     */
    public function updateAdmin(string $adminId, array $data): bool
    {
        $updateData = [];

        $allowedFields = ['username', 'email', 'first_name', 'last_name', 'role', 'permissions', 'is_active'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'permissions') {
                    $updateData[$field] = json_encode($data[$field]);
                } else {
                    $updateData[$field] = $data[$field];
                }
            }
        }

        // Hash password if provided
        if (isset($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        return $this->updateBy('admin_id', $adminId, $updateData);
    }

    /**
     * Get all active admins
     */
    public function getActiveAdmins(): array
    {
        return $this->findAll(['is_active' => 1], 'created_at DESC');
    }

    /**
     * Get admins by role
     */
    public function getAdminsByRole(string $role): array
    {
        return $this->findAll(['role' => $role, 'is_active' => 1], 'created_at DESC');
    }

    /**
     * Deactivate admin
     */
    public function deactivateAdmin(string $adminId): bool
    {
        return $this->updateBy('admin_id', $adminId, [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if username exists
     */
    public function usernameExists(string $username, string $excludeAdminId = ''): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE username = ?";
        $params = [$username];

        if ($excludeAdminId) {
            $sql .= " AND admin_id != ?";
            $params[] = $excludeAdminId;
        }

        $stmt = $this->query($sql, $params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, string $excludeAdminId = ''): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeAdminId) {
            $sql .= " AND admin_id != ?";
            $params[] = $excludeAdminId;
        }

        $stmt = $this->query($sql, $params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Generate unique admin ID
     */
    private function generateAdminId(): string
    {
        do {
            $adminId = 'ADM' . strtoupper(bin2hex(random_bytes(4)));
        } while ($this->findByAdminId($adminId));

        return $adminId;
    }
}
