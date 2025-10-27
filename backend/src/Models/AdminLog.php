<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class AdminLog extends BaseModel
{
    protected string $table = 'admin_logs';
    protected string $primaryKey = 'id';

    /**
     * Get logs with filters
     */
    public function getLogsWithFilters(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT al.*, au.username, au.first_name, au.last_name
                FROM admin_logs al
                LEFT JOIN admin_users au ON al.admin_id = au.admin_id
                WHERE 1=1";

        $params = [];

        // Apply filters
        if (!empty($filters['admin_id'])) {
            $sql .= " AND al.admin_id = ?";
            $params[] = $filters['admin_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND al.action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['resource_type'])) {
            $sql .= " AND al.affected_resource_type = ?";
            $params[] = $filters['resource_type'];
        }

        if (!empty($filters['ip_address'])) {
            $sql .= " AND al.ip_address = ?";
            $params[] = $filters['ip_address'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats(string $period = '24h'): array
    {
        $timeCondition = match($period) {
            '1h' => 'created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
            '24h' => 'created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)',
            '7d' => 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
            '30d' => 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
            default => 'created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)'
        };

        // Total activities
        $sql = "SELECT COUNT(*) as total_activities FROM admin_logs WHERE {$timeCondition}";
        $stmt = $this->query($sql);
        $totalActivities = $stmt->fetch()['total_activities'];

        // Activities by action
        $sql = "SELECT action, COUNT(*) as count
                FROM admin_logs
                WHERE {$timeCondition}
                GROUP BY action
                ORDER BY count DESC
                LIMIT 10";
        $stmt = $this->query($sql);
        $activitiesByAction = $stmt->fetchAll();

        // Activities by admin
        $sql = "SELECT al.admin_id, au.username, au.first_name, au.last_name, COUNT(*) as count
                FROM admin_logs al
                LEFT JOIN admin_users au ON al.admin_id = au.admin_id
                WHERE {$timeCondition}
                GROUP BY al.admin_id
                ORDER BY count DESC
                LIMIT 10";
        $stmt = $this->query($sql);
        $activitiesByAdmin = $stmt->fetchAll();

        // Failed login attempts
        $sql = "SELECT COUNT(*) as failed_logins
                FROM admin_logs
                WHERE {$timeCondition} AND action = 'LOGIN_FAILED'";
        $stmt = $this->query($sql);
        $failedLogins = $stmt->fetch()['failed_logins'];

        return [
            'period' => $period,
            'total_activities' => (int) $totalActivities,
            'failed_logins' => (int) $failedLogins,
            'activities_by_action' => $activitiesByAction,
            'activities_by_admin' => $activitiesByAdmin
        ];
    }

    /**
     * Get login attempts for specific IP
     */
    public function getLoginAttempts(string $ipAddress, string $since = '1 hour'): array
    {
        $sinceDate = date('Y-m-d H:i:s', strtotime("-{$since}"));

        $sql = "SELECT * FROM admin_logs
                WHERE ip_address = ?
                AND action IN ('LOGIN_SUCCESS', 'LOGIN_FAILED')
                AND created_at >= ?
                ORDER BY created_at DESC";

        $stmt = $this->query($sql, [$ipAddress, $sinceDate]);
        return $stmt->fetchAll();
    }
}
