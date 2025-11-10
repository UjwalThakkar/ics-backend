<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class Appointment extends BaseModel
{
    protected string $table = 'appointment';
    protected string $primaryKey = 'appointment_id';

    // ──────────────────────────────────────────────────────────────
    // 1. GET SINGLE APPOINTMENT DETAILS (Admin)
    // ──────────────────────────────────────────────────────────────
    /**
     * Get full appointment details by ID
     */
    public function getAppointmentDetails(int $appointmentId): ?array
    {
        $sql = "SELECT 
                    a.*,
                    ts.start_time,
                    ts.end_time,
                    ts.duration,
                    s.title AS service_name,
                    s.category AS service_category,
                    s.processing_time,
                    c.counter_name,
                    vc.center_id,
                    vc.name AS center_name,
                    vc.address,
                    vc.city,
                    vc.state,
                    vc.country,
                    u.user_id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone_no,
                    u.passport_no,
                    u.date_of_birth,
                    u.nationality
                FROM {$this->table} a
                LEFT JOIN time_slots ts ON a.slot = ts.slot_id
                LEFT JOIN service s ON a.booked_for_service = s.service_id
                LEFT JOIN counter c ON a.at_counter = c.counter_id
                LEFT JOIN verification_center vc ON c.center_id = vc.center_id
                LEFT JOIN user u ON a.booked_by = u.user_id
                WHERE a.{$this->primaryKey} = ?";

        $stmt = $this->query($sql, [$appointmentId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    // ──────────────────────────────────────────────────────────────
    // 2. UPDATE APPOINTMENT STATUS
    // ──────────────────────────────────────────────────────────────
    /**
     * Update appointment status
     */
    public function updateStatus(int $appointmentId, string $status): bool
    {
        $sql = "UPDATE {$this->table} 
                SET appointment_status = ?, updated_at = NOW() 
                WHERE {$this->primaryKey} = ?";

        $stmt = $this->query($sql, [$status, $appointmentId]);
        return $stmt->rowCount() > 0;
    }

    // ──────────────────────────────────────────────────────────────
    // 3. GET APPOINTMENT STATISTICS
    // ──────────────────────────────────────────────────────────────
    /**
     * Get appointment statistics with optional filters
     */
    public function getStats(?int $centerId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN a.appointment_status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN a.appointment_status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN a.appointment_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN a.appointment_status = 'no-show' THEN 1 ELSE 0 END) as no_show,
                    COUNT(DISTINCT a.booked_by) as unique_users,
                    COUNT(DISTINCT a.booked_for_service) as unique_services
                FROM {$this->table} a
                LEFT JOIN counter c ON a.at_counter = c.counter_id
                LEFT JOIN verification_center vc ON c.center_id = vc.center_id
                WHERE 1=1";

        $params = [];

        if ($centerId !== null) {
            $sql .= " AND vc.center_id = ?";
            $params[] = $centerId;
        }

        if ($dateFrom !== null) {
            $sql .= " AND a.appointment_date >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== null) {
            $sql .= " AND a.appointment_date <= ?";
            $params[] = $dateTo;
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();

        // Convert to int
        return [
            'total' => (int)($result['total'] ?? 0),
            'scheduled' => (int)($result['scheduled'] ?? 0),
            'completed' => (int)($result['completed'] ?? 0),
            'cancelled' => (int)($result['cancelled'] ?? 0),
            'no_show' => (int)($result['no_show'] ?? 0),
            'unique_users' => (int)($result['unique_users'] ?? 0),
            'unique_services' => (int)($result['unique_services'] ?? 0),
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // CORE METHOD – REUSABLE FOR ALL FILTERS (unchanged)
    // ──────────────────────────────────────────────────────────────
    public function getAppointmentsWithFilters(
        array $filters = [],
        int $limit = 20,
        int $offset = 0
    ): array {
        $sql = "SELECT 
                    a.appointment_id,
                    a.appointment_date,
                    a.slot,
                    a.appointment_status,
                    a.created_at,
                    a.updated_at,
                    ts.start_time,
                    ts.end_time,
                    ts.duration,
                    s.title AS service_name,
                    s.category AS service_category,
                    c.counter_name,
                    vc.center_id,
                    vc.name AS center_name,
                    u.first_name,
                    u.last_name,
                    u.passport_no,
                    u.email
                FROM {$this->table} a
                LEFT JOIN counter c ON a.at_counter = c.counter_id
                LEFT JOIN verification_center vc ON c.center_id = vc.center_id
                LEFT JOIN service s ON a.booked_for_service = s.service_id
                LEFT JOIN user u ON a.booked_by = u.user_id
                LEFT JOIN time_slots ts ON a.slot = ts.slot_id";

        $params = [];
        $where = [];

        if (!empty($filters['status'])) {
            $where[] = "a.appointment_status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['center_id'])) {
            $where[] = "vc.center_id = ?";
            $params[] = $filters['center_id'];
        }
        if (!empty($filters['counter_id'])) {
            $where[] = "a.at_counter = ?";
            $params[] = $filters['counter_id'];
        }
        if (!empty($filters['service_id'])) {
            $where[] = "a.booked_for_service = ?";
            $params[] = $filters['service_id'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = "a.booked_by = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "a.appointment_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "a.appointment_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.passport_no LIKE ? OR s.title LIKE ?)";
            $params[] = $search; $params[] = $search; $params[] = $search; $params[] = $search; $params[] = $search;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY a.appointment_date DESC, ts.start_time DESC, a.{$this->primaryKey} DESC";

        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────────────
    // UPCOMING APPOINTMENTS (Admin Dashboard)
    // ──────────────────────────────────────────────────────────────
    public function getUpcomingAppointments(?int $centerId = null, int $limit = 50): array
    {
        $sql = "SELECT 
                    a.appointment_id,
                    a.appointment_date,
                    ts.start_time,
                    ts.end_time,
                    a.appointment_status,
                    s.title AS service_name,
                    c.counter_name,
                    vc.name AS center_name,
                    u.first_name,
                    u.last_name,
                    u.passport_no
                FROM {$this->table} a
                INNER JOIN service s ON a.booked_for_service = s.service_id
                INNER JOIN counter c ON a.at_counter = c.counter_id
                INNER JOIN verification_center vc ON c.center_id = vc.center_id
                INNER JOIN user u ON a.booked_by = u.user_id
                INNER JOIN time_slots ts ON a.slot = ts.slot_id
                WHERE a.appointment_status IN ('scheduled', 'confirmed')
                  AND a.appointment_date >= CURDATE()
                  AND a.appointment_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";

        $params = [];

        if ($centerId !== null) {
            $sql .= " AND vc.center_id = ?";
            $params[] = $centerId;
        }

        $sql .= " ORDER BY a.appointment_date ASC, ts.start_time ASC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────────────
    // COUNT METHOD
    // ──────────────────────────────────────────────────────────────
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} a
                LEFT JOIN counter c ON a.at_counter = c.counter_id
                LEFT JOIN verification_center vc ON c.center_id = vc.center_id
                LEFT JOIN service s ON a.booked_for_service = s.service_id
                LEFT JOIN user u ON a.booked_by = u.user_id
                LEFT JOIN time_slots ts ON a.slot = ts.slot_id
                WHERE 1=1";

        $params = [];

        foreach ($conditions as $field => $value) {
            if (empty($value)) continue;

            switch ($field) {
                case 'status': $sql .= " AND a.appointment_status = ?"; $params[] = $value; break;
                case 'center_id': $sql .= " AND vc.center_id = ?"; $params[] = $value; break;
                case 'counter_id': $sql .= " AND a.at_counter = ?"; $params[] = $value; break;
                case 'service_id': $sql .= " AND a.booked_for_service = ?"; $params[] = $value; break;
                case 'user_id': $sql .= " AND a.booked_by = ?"; $params[] = $value; break;
                case 'date_from': $sql .= " AND a.appointment_date >= ?"; $params[] = $value; break;
                case 'date_to': $sql .= " AND a.appointment_date <= ?"; $params[] = $value; break;
                case 'search':
                    $search = '%' . $value . '%';
                    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.passport_no LIKE ? OR s.title LIKE ?)";
                    $params[] = $search; $params[] = $search; $params[] = $search; $params[] = $search; $params[] = $search;
                    break;
            }
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }
}