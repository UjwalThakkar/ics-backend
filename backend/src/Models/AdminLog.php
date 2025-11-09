<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class AdminLog extends BaseModel
{
    protected string $table = 'admin_logs';
    protected string $primaryKey = 'id';

    /**
     * Create a new log entry
     */
    public function createLog(array $data): int
    {
        $logData = [
            'log_id' => $this->generateLogId(),
            'admin_id' => $data['admin_id'],
            'action' => $data['action'],
            'details' => isset($data['details']) && is_array($data['details']) 
                        ? json_encode($data['details']) 
                        : $data['details'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'affected_resource_type' => $data['affected_resource_type'] ?? null,
            'affected_resource_id' => $data['affected_resource_id'] ?? null
        ];

        return $this->insert($logData);
    }

    /**
     * Get logs by admin
     */
    public function getAdminLogs(string $adminId, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM admin_logs 
                WHERE admin_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->query($sql, [$adminId, $limit, $offset]);
        $logs = $stmt->fetchAll();

        // Decode JSON details
        foreach ($logs as &$log) {
            if (!empty($log['details'])) {
                $log['details'] = json_decode($log['details'], true);
            }
        }

        return $logs;
    }

    /**
     * Get logs by action
     */
    public function getLogsByAction(string $action, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM admin_logs 
                WHERE action = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->query($sql, [$action, $limit, $offset]);
        $logs = $stmt->fetchAll();

        // Decode JSON details
        foreach ($logs as &$log) {
            if (!empty($log['details'])) {
                $log['details'] = json_decode($log['details'], true);
            }
        }

        return $logs;
    }

    /**
     * Get logs by resource
     */
    public function getResourceLogs(string $resourceType, string $resourceId, int $limit = 100): array
    {
        $sql = "SELECT * FROM admin_logs 
                WHERE affected_resource_type = ? 
                AND affected_resource_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $stmt = $this->query($sql, [$resourceType, $resourceId, $limit]);
        $logs = $stmt->fetchAll();

        // Decode JSON details
        foreach ($logs as &$log) {
            if (!empty($log['details'])) {
                $log['details'] = json_decode($log['details'], true);
            }
        }

        return $logs;
    }

    /**
     * Get logs with filters
     */
    public function getLogsWithFilters(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT al.*, au.username, au.first_name, au.last_name, au.email 
                FROM admin_logs al
                LEFT JOIN admin_users au ON al.admin_id = au.admin_id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['admin_id'])) {
            $sql .= " AND al.admin_id = ?";
            $params[] = $filters['admin_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['resource_type'])) {
            $sql .= " AND al.affected_resource_type = ?";
            $params[] = $filters['resource_type'];
        }

        if (!empty($filters['resource_id'])) {
            $sql .= " AND al.affected_resource_id = ?";
            $params[] = $filters['resource_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['ip_address'])) {
            $sql .= " AND al.ip_address = ?";
            $params[] = $filters['ip_address'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (al.action LIKE ? OR al.details LIKE ? OR au.username LIKE ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        $logs = $stmt->fetchAll();

        // Decode JSON details
        foreach ($logs as &$log) {
            if (!empty($log['details'])) {
                $log['details'] = json_decode($log['details'], true);
            }
        }

        return $logs;
    }

    /**
     * Get recent admin activity
     */
    public function getRecentActivity(int $limit = 50): array
    {
        $sql = "SELECT 
                    al.*,
                    au.username,
                    au.first_name,
                    au.last_name,
                    au.email
                FROM admin_logs al
                INNER JOIN admin_users au ON al.admin_id = au.admin_id
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        $stmt = $this->query($sql, [$limit]);
        $logs = $stmt->fetchAll();

        // Decode JSON details
        foreach ($logs as &$log) {
            if (!empty($log['details'])) {
                $log['details'] = json_decode($log['details'], true);
            }
        }

        return $logs;
    }

    /**
     * Get log statistics
     */
    public function getLogStats(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $stats = [];
        $whereClause = "WHERE 1=1";
        $params = [];

        if ($dateFrom) {
            $whereClause .= " AND created_at >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $whereClause .= " AND created_at <= ?";
            $params[] = $dateTo;
        }

        // Total logs
        $sql = "SELECT COUNT(*) as total FROM admin_logs $whereClause";
        $stmt = $this->query($sql, $params);
        $stats['total'] = (int)$stmt->fetch()['total'];

        // By action
        $sql = "SELECT action, COUNT(*) as count 
                FROM admin_logs 
                $whereClause
                GROUP BY action 
                ORDER BY count DESC 
                LIMIT 10";
        $stmt = $this->query($sql, $params);
        $stats['by_action'] = $stmt->fetchAll();

        // Most active admins
        $sql = "SELECT 
                    al.admin_id,
                    au.username,
                    au.first_name,
                    au.last_name,
                    au.email,
                    COUNT(*) as activity_count
                FROM admin_logs al
                INNER JOIN admin_users au ON al.admin_id = au.admin_id
                $whereClause
                GROUP BY al.admin_id
                ORDER BY activity_count DESC
                LIMIT 10";
        $stmt = $this->query($sql, $params);
        $stats['most_active_admins'] = $stmt->fetchAll();

        // Activity by hour
        $sql = "SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as count
                FROM admin_logs
                $whereClause
                GROUP BY HOUR(created_at)
                ORDER BY hour";
        $stmt = $this->query($sql, $params);
        $stats['by_hour'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * Get admin's last activity
     */
    public function getLastActivity(string $adminId): ?array
    {
        $sql = "SELECT * FROM admin_logs 
                WHERE admin_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $stmt = $this->query($sql, [$adminId]);
        $log = $stmt->fetch();

        if ($log && !empty($log['details'])) {
            $log['details'] = json_decode($log['details'], true);
        }

        return $log ?: null;
    }

    /**
     * Count logs by admin
     */
    public function countAdminLogs(string $adminId, ?string $action = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM admin_logs WHERE admin_id = ?";
        $params = [$adminId];

        if ($action) {
            $sql .= " AND action = ?";
            $params[] = $action;
        }

        $stmt = $this->query($sql, $params);
        return (int)$stmt->fetch()['count'];
    }

    /**
     * Delete old logs (cleanup)
     */
    public function deleteOldLogs(int $daysToKeep = 90): int
    {
        $sql = "DELETE FROM admin_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->query($sql, [$daysToKeep]);
        return $stmt->rowCount();
    }

    /**
     * Generate unique log ID
     */
    private function generateLogId(): string
    {
        return 'ADLOG' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Common action constants
     */
    const ACTION_LOGIN = 'admin_login';
    const ACTION_LOGOUT = 'admin_logout';
    const ACTION_CREATE_SERVICE = 'create_service';
    const ACTION_UPDATE_SERVICE = 'update_service';
    const ACTION_DELETE_SERVICE = 'delete_service';
    const ACTION_CREATE_CENTER = 'create_center';
    const ACTION_UPDATE_CENTER = 'update_center';
    const ACTION_CREATE_APPOINTMENT = 'create_appointment';
    const ACTION_UPDATE_APPOINTMENT = 'update_appointment';
    const ACTION_VIEW_DASHBOARD = 'view_dashboard';
}