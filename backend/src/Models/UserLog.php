<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class UserLog extends BaseModel
{
    protected string $table = 'user_logs';
    protected string $primaryKey = 'id';

    /**
     * Get logs with filters
     */
    public function getLogsWithFilters(array $filters, int $limit = 100, int $offset = 0): array
    {
        $conditions = [];
        $params = [];

        foreach ($filters as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT * FROM {$this->table} $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
}