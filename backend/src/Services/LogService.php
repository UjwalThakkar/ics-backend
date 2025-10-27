<?php

declare(strict_types=1);

namespace IndianConsular\Services;

use IndianConsular\Models\AdminLog;
use IndianConsular\Models\UserLog;

class LogService
{
    private AdminLog $adminLogModel;
    private UserLog $userLogModel;

    public function __construct()
    {
        $this->adminLogModel = new AdminLog();
        $this->userLogModel = new UserLog();
    }

    /**
     * Log admin activity
     */
    public function logAdminActivity(
        string $adminId,
        string $action,
        array $details = [],
        string $ipAddress = '',
        string $userAgent = '',
        string $resourceType = '',
        string $resourceId = ''
    ): void {
        try {
            $this->adminLogModel->insert([
                'log_id' => $this->generateLogId(),
                'admin_id' => $adminId,
                'action' => $action,
                'details' => json_encode($details),
                'ip_address' => $ipAddress ?: 'unknown',
                'user_agent' => $userAgent ?: 'unknown',
                'affected_resource_type' => $resourceType,
                'affected_resource_id' => $resourceId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Log to error log if database logging fails
            error_log("Failed to log admin activity: " . $e->getMessage());
        }
    }

    /**
     * Log user activity
     */
    public function logUserActivity(
        string $userId,
        string $action,
        array $details = [],
        string $ipAddress = '',
        string $userAgent = '',
        string $resourceType = '',
        string $resourceId = ''
    ): void {
        try {
            $this->userLogModel->insert([
                'log_id' => $this->generateLogId(),
                'user_id' => $userId,
                'action' => $action,
                'details' => json_encode($details),
                'ip_address' => $ipAddress ?: 'unknown',
                'user_agent' => $userAgent ?: 'unknown',
                'affected_resource_type' => $resourceType,
                'affected_resource_id' => $resourceId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Log to error log if database logging fails
            error_log("Failed to log user activity: " . $e->getMessage());
        }
    }

    /**
     * Get admin activity logs
     */
    public function getAdminLogs(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        return $this->adminLogModel->getLogsWithFilters($filters, $limit, $offset);
    }

    /**
     * Get user activity logs
     */
    public function getUserLogs(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        return $this->userLogModel->getLogsWithFilters($filters, $limit, $offset);
    }

    /**
     * Get logs for specific admin
     */
    public function getAdminActivityLogs(string $adminId, int $limit = 50): array
    {
        return $this->adminLogModel->findAll(
            ['admin_id' => $adminId],
            'created_at DESC',
            $limit
        );
    }

    /**
     * Get logs for specific user
     */
    public function getUserActivityLogs(string $userId, int $limit = 50): array
    {
        return $this->userLogModel->findAll(
            ['user_id' => $userId],
            'created_at DESC',
            $limit
        );
    }

    /**
     * Get recent activity (combined admin and user logs)
     */
    public function getRecentActivity(int $hours = 24, int $limit = 100): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        // Combine admin and user logs
        $sql = "
            (SELECT 'admin' as type, log_id, admin_id as id, action, details, ip_address, user_agent, affected_resource_type, affected_resource_id, created_at 
             FROM admin_logs 
             WHERE created_at >= ?)
            UNION
            (SELECT 'user' as type, log_id, user_id as id, action, details, ip_address, user_agent, affected_resource_type, affected_resource_id, created_at 
             FROM user_logs 
             WHERE created_at >= ?)
            ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->adminLogModel->query($sql, [$since, $since, $limit]);

        return $stmt->fetchAll();
    }

    /**
     * Clean old logs (older than specified days)
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        $deletedRows = 0;

        // Clean admin logs
        $sql = "DELETE FROM admin_logs WHERE created_at < ?";
        $stmt = $this->adminLogModel->query($sql, [$cutoffDate]);
        $deletedRows += $stmt->rowCount();

        // Clean user logs
        $sql = "DELETE FROM user_logs WHERE created_at < ?";
        $stmt = $this->userLogModel->query($sql, [$cutoffDate]);
        $deletedRows += $stmt->rowCount();

        return $deletedRows;
    }

    /**
     * Generate unique log ID
     */
    private function generateLogId(): string
    {
        return 'LOG' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));
    }
}