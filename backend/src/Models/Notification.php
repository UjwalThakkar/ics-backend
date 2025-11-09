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
     * Get notifications by type
     */
    public function getByType(string $type): array
    {
        return $this->findAll(['type' => $type], 'created_at DESC');
    }

    /**
     * Get notifications by status
     */
    public function getByStatus(string $status, int $limit = 100): array
    {
        return $this->findAll(['status' => $status], 'created_at DESC', $limit);
    }

    /**
     * Get pending notifications
     */
    public function getPendingNotifications(int $limit = 50): array
    {
        return $this->findAll(['status' => 'pending'], 'created_at ASC', $limit);
    }

    /**
     * Get failed notifications for retry
     */
    public function getFailedNotifications(int $limit = 50): array
    {
        return $this->findAll(['status' => 'failed'], 'created_at DESC', $limit);
    }

    /**
     * Get notifications for a user
     */
    public function getUserNotifications(string $userId, int $limit = 50): array
    {
        return $this->findAll(['user_id' => $userId], 'created_at DESC', $limit);
    }

    /**
     * Get notifications for an appointment
     */
    public function getAppointmentNotifications(string $appointmentId): array
    {
        return $this->findAll(['appointment_id' => $appointmentId], 'created_at DESC');
    }

    /**
     * Get notifications for an application
     */
    public function getApplicationNotifications(string $applicationId): array
    {
        return $this->findAll(['application_id' => $applicationId], 'created_at DESC');
    }

    /**
     * Update notification status
     */
    public function updateStatus(string $notificationId, string $status, ?string $errorMessage = null): bool
    {
        $updateData = [
            'status' => $status,
            'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null
        ];

        if ($errorMessage) {
            $updateData['error_message'] = $errorMessage;
        }

        return $this->updateBy('notification_id', $notificationId, $updateData);
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent(string $notificationId): bool
    {
        return $this->updateStatus($notificationId, 'sent');
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed(string $notificationId, string $errorMessage): bool
    {
        return $this->updateStatus($notificationId, 'failed', $errorMessage);
    }

    /**
     * Retry failed notification
     */
    public function retryNotification(string $notificationId): bool
    {
        return $this->updateBy('notification_id', $notificationId, [
            'status' => 'pending',
            'error_message' => null
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getStats(?string $dateFrom = null, ?string $dateTo = null): array
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

        // Total notifications
        $sql = "SELECT COUNT(*) as total FROM notifications $whereClause";
        $stmt = $this->query($sql, $params);
        $stats['total'] = (int)$stmt->fetch()['total'];

        // By status
        $sql = "SELECT status, COUNT(*) as count FROM notifications $whereClause GROUP BY status";
        $stmt = $this->query($sql, $params);
        foreach ($stmt->fetchAll() as $row) {
            $stats['status_' . $row['status']] = (int)$row['count'];
        }

        // By type
        $sql = "SELECT type, COUNT(*) as count FROM notifications $whereClause GROUP BY type";
        $stmt = $this->query($sql, $params);
        foreach ($stmt->fetchAll() as $row) {
            $stats['type_' . $row['type']] = (int)$row['count'];
        }

        // Success rate
        if ($stats['total'] > 0) {
            $sent = $stats['status_sent'] ?? 0;
            $stats['success_rate'] = round(($sent / $stats['total']) * 100, 2);
        } else {
            $stats['success_rate'] = 0;
        }

        return $stats;
    }

    /**
     * Get notifications with filters
     */
    public function getWithFilters(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM notifications WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['appointment_id'])) {
            $sql .= " AND appointment_id = ?";
            $params[] = $filters['appointment_id'];
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
     * Delete old notifications (cleanup)
     */
    public function deleteOldNotifications(int $daysToKeep = 90): int
    {
        $sql = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY) AND status = 'sent'";
        $stmt = $this->query($sql, [$daysToKeep]);
        return $stmt->rowCount();
    }

    /**
     * Count notifications by filters
     */
    public function countWithFilters(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }

        $stmt = $this->query($sql, $params);
        return (int)$stmt->fetch()['count'];
    }

    /**
     * Get recent notifications
     */
    public function getRecentNotifications(int $hours = 24, int $limit = 100): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $stmt = $this->query($sql, [$hours, $limit]);
        return $stmt->fetchAll();
    }
}