<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class Notification extends BaseModel
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id';

    /**
     * Find notification by notification_id
     */
    public function findByNotificationId(string $notificationId): ?array
    {
        return $this->findBy('notification_id', $notificationId);
    }

    /**
     * Get notifications with filters
     */
    public function getNotificationsWithFilters(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM notifications WHERE 1=1";
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['application_id'])) {
            $sql .= " AND application_id = ?";
            $params[] = $filters['application_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get notification statistics
     */
    public function getStats(): array
    {
        $stats = [];

        // Total notifications
        $stmt = $this->query("SELECT COUNT(*) as total FROM notifications");
        $stats['total'] = $stmt->fetch()['total'];

        // Notifications by status
        $stmt = $this->query("SELECT status, COUNT(*) as count FROM notifications GROUP BY status");
        $statusCounts = $stmt->fetchAll();

        foreach ($statusCounts as $status) {
            $stats[$status['status']] = $status['count'];
        }

        // Notifications by type
        $stmt = $this->query("SELECT type, COUNT(*) as count FROM notifications GROUP BY type");
        $typeCounts = $stmt->fetchAll();

        foreach ($typeCounts as $type) {
            $stats[$type['type']] = $type['count'];
        }

        return $stats;
    }
}
