<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class UserLog extends BaseModel
{
    protected string $table = 'user_logs';
    protected string $primaryKey = 'id';

    /**
     * Create a new log entry
     */
    public function createLog(array $data): int
    {
        $logData = [
            'log_id' => $this->generateLogId(),
            'user_id' => $data['user_id'],
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
     * Log user action (convenience method)
     */
    public function logAction(
        string $userId, 
        string $action, 
        ?array $details = null,
        ?string $resourceType = null,
        ?string $resourceId = null
    ): int {
        return $this->createLog([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'affected_resource_type' => $resourceType,
            'affected_resource_id' => $resourceId
        ]);
    }

    /**
     * Get logs by user
     */
    public function getUserLogs(string $userId, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM user_logs 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->query($sql, [$userId, $limit, $offset]);
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
        $sql = "SELECT * FROM user_logs 
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
        $sql = "SELECT * FROM user_logs 
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
        $sql = "SELECT ul.*, u.first_name, u.last_name, u.email 
                FROM user_logs ul
                LEFT JOIN user u ON ul.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND ul.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND ul.action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['resource_type'])) {
            $sql .= " AND ul.affected_resource_type = ?";
            $params[] = $filters['resource_type'];
        }

        if (!empty($filters['resource_id'])) {
            $sql .= " AND ul.affected_resource_id = ?";
            $params[] = $filters['resource_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND ul.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND ul.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['ip_address'])) {
            $sql .= " AND ul.ip_address = ?";
            $params[] = $filters['ip_address'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (ul.action LIKE ? OR ul.details LIKE ? OR u.email LIKE ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        $sql .= " ORDER BY ul.created_at DESC LIMIT ? OFFSET ?";
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
     * Get recent user activity
     */
    public function getRecentActivity(int $limit = 50): array
    {
        $sql = "SELECT 
                    ul.*,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM user_logs ul
                INNER JOIN user u ON ul.user_id = u.user_id
                ORDER BY ul.created_at DESC
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
        $sql = "SELECT COUNT(*) as total FROM user_logs $whereClause";
        $stmt = $this->query($sql, $params);
        $stats['total'] = (int)$stmt->fetch()['total'];

        // By action
        $sql = "SELECT action, COUNT(*) as count 
                FROM user_logs 
                $whereClause
                GROUP BY action 
                ORDER BY count DESC 
                LIMIT 10";
        $stmt = $this->query($sql, $params);
        $stats['by_action'] = $stmt->fetchAll();

        // Most active users
        $sql = "SELECT 
                    ul.user_id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    COUNT(*) as activity_count
                FROM user_logs ul
                INNER JOIN user u ON ul.user_id = u.user_id
                $whereClause
                GROUP BY ul.user_id
                ORDER BY activity_count DESC
                LIMIT 10";
        $stmt = $this->query($sql, $params);
        $stats['most_active_users'] = $stmt->fetchAll();

        // Activity by hour
        $sql = "SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as count
                FROM user_logs
                $whereClause
                GROUP BY HOUR(created_at)
                ORDER BY hour";
        $stmt = $this->query($sql, $params);
        $stats['by_hour'] = $stmt->fetchAll();

        // Activity by day of week
        $sql = "SELECT 
                    DAYNAME(created_at) as day_name,
                    DAYOFWEEK(created_at) as day_num,
                    COUNT(*) as count
                FROM user_logs
                $whereClause
                GROUP BY day_name, day_num
                ORDER BY day_num";
        $stmt = $this->query($sql, $params);
        $stats['by_day_of_week'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * Get user's last activity
     */
    public function getLastActivity(string $userId): ?array
    {
        $sql = "SELECT * FROM user_logs 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $stmt = $this->query($sql, [$userId]);
        $log = $stmt->fetch();

        if ($log && !empty($log['details'])) {
            $log['details'] = json_decode($log['details'], true);
        }

        return $log ?: null;
    }

    /**
     * Count logs by user
     */
    public function countUserLogs(string $userId, ?string $action = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM user_logs WHERE user_id = ?";
        $params = [$userId];

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
        $sql = "DELETE FROM user_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->query($sql, [$daysToKeep]);
        return $stmt->rowCount();
    }

    /**
     * Get suspicious activity
     */
    public function getSuspiciousActivity(int $limit = 50): array
    {
        // Multiple failed login attempts from same IP
        $sql = "SELECT 
                    ip_address,
                    user_id,
                    COUNT(*) as attempt_count,
                    MAX(created_at) as last_attempt
                FROM user_logs
                WHERE action LIKE '%failed%' 
                OR action LIKE '%unauthorized%'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY ip_address, user_id
                HAVING attempt_count >= 3
                ORDER BY attempt_count DESC, last_attempt DESC
                LIMIT ?";
        
        $stmt = $this->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Generate unique log ID
     */
    private function generateLogId(): string
    {
        return 'LOG' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Common action constants
     */
    const ACTION_LOGIN = 'user_login';
    const ACTION_LOGOUT = 'user_logout';
    const ACTION_REGISTER = 'user_register';
    const ACTION_PROFILE_UPDATE = 'profile_update';
    const ACTION_PASSWORD_CHANGE = 'password_change';
    const ACTION_APPOINTMENT_BOOK = 'appointment_book';
    const ACTION_APPOINTMENT_CANCEL = 'appointment_cancel';
    const ACTION_APPOINTMENT_VIEW = 'appointment_view';
    const ACTION_FAILED_LOGIN = 'login_failed';
    const ACTION_PASSWORD_RESET = 'password_reset';
}