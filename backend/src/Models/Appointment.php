<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class Appointment extends BaseModel
{
    protected string $table = 'appointments';
    protected string $primaryKey = 'id';

    /**
     * Find appointment by appointment_id (unique identifier)
     */
    public function findByAppointmentId(string $appointmentId): ?array
    {
        return $this->findBy('appointment_id', $appointmentId);
    }

    /**
     * Find appointments by application_id
     */
    public function findByApplicationId(string $applicationId): array
    {
        return $this->findAll(['application_id' => $applicationId], 'appointment_date DESC');
    }

    /**
     * Get appointments with filters (for admin panel)
     */
    public function getAppointmentsWithFilters(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT 
                    a.*,
                    s.title as service_title
                FROM appointments a
                LEFT JOIN services s ON a.service_type = s.service_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['service_type'])) {
            $sql .= " AND a.service_type = ?";
            $params[] = $filters['service_type'];
        }

        if (!empty($filters['application_id'])) {
            $sql .= " AND a.application_id = ?";
            $params[] = $filters['application_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND a.appointment_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND a.appointment_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (
                a.appointment_id LIKE ? OR
                a.client_name LIKE ? OR
                a.client_email LIKE ? OR
                a.client_phone LIKE ?
            )";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get appointment statistics for admin dashboard
     */
    public function getStats(): array
    {
        $stats = [];

        // Total appointments
        $stmt = $this->query("SELECT COUNT(*) as total FROM appointments");
        $stats['total'] = (int)$stmt->fetch()['total'];

        // By status
        $stmt = $this->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
        foreach ($stmt->fetchAll() as $row) {
            $stats['status_' . $row['status']] = (int)$row['count'];
        }

        // Today's appointments
        $stmt = $this->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()");
        $stats['today'] = (int)$stmt->fetch()['count'];

        // This week's upcoming
        $stmt = $this->query("
            SELECT COUNT(*) as count FROM appointments 
            WHERE appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            AND status IN ('pending', 'confirmed')
        ");
        $stats['upcoming_week'] = (int)$stmt->fetch()['count'];

        // By service type (top 5)
        $stmt = $this->query("
            SELECT service_type, COUNT(*) as count 
            FROM appointments 
            GROUP BY service_type 
            ORDER BY count DESC 
            LIMIT 5
        ");
        $stats['top_services'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * Check slot availability for a given date and optional service
     */
    public function getAvailability(string $date, ?string $serviceType = null): array
    {
        $sql = "SELECT appointment_time, duration_minutes, status, client_name 
                FROM appointments 
                WHERE appointment_date = ? AND status != 'cancelled'";
        $params = [$date];

        if ($serviceType) {
            $sql .= " AND service_type = ?";
            $params[] = $serviceType;
        }

        $sql .= " ORDER BY appointment_time";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Create new appointment
     */
    public function createAppointment(array $data): int
    {
        $data['appointment_id'] = $this->generateAppointmentId();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insert($data);
    }

    /**
     * Update appointment
     */
    public function updateAppointment(string $appointmentId, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->updateBy('appointment_id', $appointmentId, $data);
    }

    /**
     * Generate unique appointment_id like APP-2025-0001
     */
    private function generateAppointmentId(): string
    {
        $year = date('Y');
        $prefix = "APP-{$year}-";

        $stmt = $this->query("
            SELECT appointment_id FROM appointments 
            WHERE appointment_id LIKE ? 
            ORDER BY appointment_id DESC LIMIT 1
        ", ["{$prefix}%"]);

        $last = $stmt->fetch();
        if ($last) {
            $lastNum = (int)substr($last['appointment_id'], strlen($prefix));
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
    }
}