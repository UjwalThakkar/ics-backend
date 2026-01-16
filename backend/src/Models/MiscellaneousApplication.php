<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class MiscellaneousApplication extends BaseModel
{
    protected string $table = 'miscellaneous_applications';
    protected string $primaryKey = 'id';

    /**
     * Find application by application_id
     */
    public function findByApplicationId(string $applicationId): ?array
    {
        return $this->findBy('application_id', $applicationId);
    }

    /**
     * Get applications with pagination, search, sort, and filters
     */
    public function getApplicationsWithFilters(array $filters = [], int $limit = 20, int $offset = 0, string $sortBy = 'submitted_at', string $sortOrder = 'DESC'): array
    {
        $sql = "SELECT 
                    ma.*,
                    s.title as service_title,
                    s.category as service_category,
                    u.first_name as user_first_name,
                    u.last_name as user_last_name,
                    u.email as user_email,
                    COUNT(af.id) as file_count
                FROM miscellaneous_applications ma
                LEFT JOIN service s ON ma.service_id = s.service_id
                LEFT JOIN user u ON ma.user_id = u.user_id
                LEFT JOIN application_files af ON ma.application_id = af.application_id
                WHERE 1=1";

        $params = [];

        // Status filter
        if (!empty($filters['status'])) {
            $sql .= " AND ma.status = ?";
            $params[] = $filters['status'];
        }

        // Service filter
        if (!empty($filters['service_id'])) {
            $sql .= " AND ma.service_id = ?";
            $params[] = $filters['service_id'];
        }

        // Date range filters
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(ma.submitted_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(ma.submitted_at) <= ?";
            $params[] = $filters['date_to'];
        }

        // User filter
        if (!empty($filters['user_id'])) {
            $sql .= " AND ma.user_id = ?";
            $params[] = $filters['user_id'];
        }

        // Search filter (searching in JSON form_data)
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (
                ma.application_id LIKE ? OR
                JSON_EXTRACT(ma.form_data, '$.full_name') LIKE ? OR
                JSON_EXTRACT(ma.form_data, '$.email_address') LIKE ? OR
                JSON_EXTRACT(ma.form_data, '$.phone_number') LIKE ? OR
                JSON_EXTRACT(ma.form_data, '$.passport_number') LIKE ? OR
                u.email LIKE ? OR
                s.title LIKE ?
            )";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Group by to handle file count
        $sql .= " GROUP BY ma.id";

        // Validate sort column
        $allowedSortColumns = ['submitted_at', 'updated_at', 'status', 'service_title'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'submitted_at';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        // Handle sorting by form_data fields
        if ($sortBy === 'full_name') {
            $sql .= " ORDER BY JSON_EXTRACT(ma.form_data, '$.full_name') {$sortOrder} LIMIT ? OFFSET ?";
        } else {
            $sql .= " ORDER BY ma.{$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        }
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Count applications with same filters
     */
    public function countWithFilters(array $filters = []): int
    {
        $sql = "SELECT COUNT(DISTINCT ma.id) as count
                FROM miscellaneous_applications ma
                LEFT JOIN service s ON ma.service_id = s.service_id
                LEFT JOIN user u ON ma.user_id = u.user_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND ma.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['service_id'])) {
            $sql .= " AND ma.service_id = ?";
            $params[] = $filters['service_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(ma.submitted_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(ma.submitted_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND ma.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (
                ma.application_id LIKE ? OR
                JSON_EXTRACT(ma.form_data, '$.full_name') LIKE ? OR
                JSON_EXTRACT(ma.form_data, '$.email_address') LIKE ? OR
                JSON_EXTRACT(ma.form_data, '$.phone_number') LIKE ? OR
                JSON_EXTRACT(ma.form_data, '$.passport_number') LIKE ? OR
                u.email LIKE ? OR
                s.title LIKE ?
            )";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Get application with all related data
     */
    public function getApplicationWithDetails(string $applicationId): ?array
    {
        $sql = "SELECT 
                    ma.*,
                    s.title as service_title,
                    s.category as service_category,
                    s.description as service_description,
                    u.first_name as user_first_name,
                    u.last_name as user_last_name,
                    u.email as user_email,
                    u.phone_no as user_phone
                FROM miscellaneous_applications ma
                LEFT JOIN service s ON ma.service_id = s.service_id
                LEFT JOIN user u ON ma.user_id = u.user_id
                WHERE ma.application_id = ?";
        
        $stmt = $this->query($sql, [$applicationId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Update application status
     */
    public function updateStatus(string $applicationId, string $status, ?string $adminNotes = null): bool
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($adminNotes !== null) {
            $data['admin_notes'] = $adminNotes;
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
        $stmt = $this->query("SELECT COUNT(*) as total FROM miscellaneous_applications");
        $stats['total'] = (int) $stmt->fetch()['total'];

        // Applications by status
        $stmt = $this->query("SELECT status, COUNT(*) as count FROM miscellaneous_applications GROUP BY status");
        $statusCounts = $stmt->fetchAll();
        $stats['by_status'] = [];
        foreach ($statusCounts as $status) {
            $stats['by_status'][$status['status']] = (int) $status['count'];
        }

        // Applications this month
        $stmt = $this->query("SELECT COUNT(*) as count FROM miscellaneous_applications WHERE submitted_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
        $stats['this_month'] = (int) $stmt->fetch()['count'];

        // Applications today
        $stmt = $this->query("SELECT COUNT(*) as count FROM miscellaneous_applications WHERE DATE(submitted_at) = CURDATE()");
        $stats['today'] = (int) $stmt->fetch()['count'];

        // Applications by service
        $stmt = $this->query("
            SELECT 
                s.service_id,
                s.title,
                COUNT(ma.id) as count
            FROM miscellaneous_applications ma
            LEFT JOIN service s ON ma.service_id = s.service_id
            GROUP BY s.service_id, s.title
            ORDER BY count DESC
            LIMIT 10
        ");
        $stats['by_service'] = $stmt->fetchAll();

        return $stats;
    }
}

