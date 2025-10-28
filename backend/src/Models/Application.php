<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class Application extends BaseModel
{
    protected string $table = 'applications';
    protected string $primaryKey = 'id';

    /**
     * Find application by application_id
     */
    public function findByApplicationId(string $applicationId): ?array
    {
        return $this->findBy('application_id', $applicationId);
    }

    /**
     * Get applications with pagination and filters
     */
    public function getApplicationsWithFilters(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT a.*, s.title as service_title
                FROM applications a
                LEFT JOIN services s ON a.service_id = s.service_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['service_id'])) {
            $sql .= " AND a.service_id = ?";
            $params[] = $filters['service_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND a.submitted_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND a.submitted_at <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (a.application_id LIKE ? OR JSON_EXTRACT(a.applicant_info, '$.firstName') LIKE ? OR JSON_EXTRACT(a.applicant_info, '$.lastName') LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY a.submitted_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Count applications with same filters as getApplicationsWithFilters
     */
    public function countWithFilters(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM applications a WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['service_id'])) {
            $sql .= " AND a.service_id = ?";
            $params[] = $filters['service_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND a.submitted_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND a.submitted_at <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (a.application_id LIKE ? OR JSON_EXTRACT(a.applicant_info, '$.firstName') LIKE ? OR JSON_EXTRACT(a.applicant_info, '$.lastName') LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $stmt = $this->query($sql, $params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Update application status
     */
    public function updateStatus(string $applicationId, string $status, array $updateData = []): bool
    {
        $data = array_merge($updateData, [
            'status' => $status,
            'last_updated' => date('Y-m-d H:i:s')
        ]);

        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        return $this->updateBy('application_id', $applicationId, $data);
    }

    /**
     * Get application statistics
     */
    public function getStats(): array
    {
        $stats = [];

        // Total applications
        $stmt = $this->query("SELECT COUNT(*) as total FROM applications");
        $stats['total'] = $stmt->fetch()['total'];

        // Applications by status
        $stmt = $this->query("SELECT status, COUNT(*) as count FROM applications GROUP BY status");
        $statusCounts = $stmt->fetchAll();

        foreach ($statusCounts as $status) {
            $stats[$status['status']] = $status['count'];
        }

        // Applications this month
        $stmt = $this->query("SELECT COUNT(*) as count FROM applications WHERE submitted_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
        $stats['this_month'] = $stmt->fetch()['count'];

        // Applications today
        $stmt = $this->query("SELECT COUNT(*) as count FROM applications WHERE DATE(submitted_at) = CURDATE()");
        $stats['today'] = $stmt->fetch()['count'];

        return $stats;
    }
}
