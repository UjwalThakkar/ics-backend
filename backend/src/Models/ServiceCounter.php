<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class ServiceCounter extends BaseModel
{
    protected string $table = 'counter';
    protected string $primaryKey = 'counter_id';

    /**
     * Find counter by counter_id (integer primary key)
     */
    public function findByCounterId(int $counterId): ?array
    {
        $counter = $this->find($counterId);
        if ($counter && isset($counter['service_handled'])) {
            $counter['service_handled'] = json_decode($counter['service_handled'], true);
        }
        return $counter;
    }

    /**
     * Get counters by center
     */
    public function getCountersByCenter(int $centerId): array
    {
        $counters = $this->findAll([
            'center_id' => $centerId,
            'is_active' => 1
        ], 'counter_name ASC');

        // Decode JSON fields
        foreach ($counters as &$counter) {
            if (isset($counter['service_handled'])) {
                $counter['service_handled'] = json_decode($counter['service_handled'], true);
            }
        }

        return $counters;
    }

    /**
     * Get all active counters
     */
    public function getActiveCounters(): array
    {
        $counters = $this->findAll(['is_active' => 1], 'counter_name ASC');

        // Decode JSON fields
        foreach ($counters as &$counter) {
            if (isset($counter['service_handled'])) {
                $counter['service_handled'] = json_decode($counter['service_handled'], true);
            }
        }

        return $counters;
    }

    /**
     * Get counters that handle a specific service
     */
    public function getCountersByService(int $centerId, int $serviceId): array
    {
        $sql = "SELECT * FROM counter 
                WHERE center_id = ? 
                AND is_active = 1
                AND JSON_CONTAINS(service_handled, ?, '$')
                ORDER BY counter_name ASC";

        $stmt = $this->query($sql, [$centerId, json_encode($serviceId)]);
        $counters = $stmt->fetchAll();

        // Decode JSON fields
        foreach ($counters as &$counter) {
            if (isset($counter['service_handled'])) {
                $counter['service_handled'] = json_decode($counter['service_handled'], true);
            }
        }

        return $counters;
    }

    /**
     * Check if counter handles a specific service
     */
    public function handlesService(int $counterId, int $serviceId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM counter 
                WHERE counter_id = ? 
                AND JSON_CONTAINS(service_handled, ?, '$')";

        $stmt = $this->query($sql, [$counterId, json_encode($serviceId)]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Get counter with its appointments for a specific date
     */
    public function getCounterWithAppointments(int $counterId, string $date): ?array
    {
        $counter = $this->findByCounterId($counterId);
        if (!$counter) {
            return null;
        }

        // Get appointments for this counter on the specified date
        $sql = "SELECT 
                    a.*,
                    ts.start_time,
                    ts.end_time,
                    ts.duration,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone_no,
                    s.title as service_title,
                    s.category as service_category
                FROM appointment a
                INNER JOIN time_slots ts ON a.slot = ts.slot_id
                INNER JOIN user u ON a.booked_by = u.user_id
                INNER JOIN service s ON a.booked_for_service = s.service_id
                WHERE a.at_counter = ?
                AND a.appointment_date = ?
                AND a.appointment_status = 'scheduled'
                ORDER BY ts.start_time ASC";

        $stmt = $this->query($sql, [$counterId, $date]);
        $counter['appointments'] = $stmt->fetchAll();

        return $counter;
    }

    /**
     * Get counter availability for a date range
     */
    public function getCounterAvailability(int $counterId, string $dateFrom, string $dateTo): array
    {
        // Get all time slots
        $slotsSql = "SELECT * FROM time_slots WHERE is_active = 1 ORDER BY start_time ASC";
        $slotsStmt = $this->query($slotsSql);
        $timeSlots = $slotsStmt->fetchAll();

        // Get appointment counts per date and slot
        $sql = "SELECT 
                    a.appointment_date,
                    a.slot,
                    COUNT(*) as booking_count
                FROM appointment a
                WHERE a.at_counter = ?
                AND a.appointment_date BETWEEN ? AND ?
                AND a.appointment_status = 'scheduled'
                GROUP BY a.appointment_date, a.slot";

        $stmt = $this->query($sql, [$counterId, $dateFrom, $dateTo]);
        $bookings = $stmt->fetchAll();

        // Organize bookings by date and slot
        $bookingsByDate = [];
        foreach ($bookings as $booking) {
            if (!isset($bookingsByDate[$booking['appointment_date']])) {
                $bookingsByDate[$booking['appointment_date']] = [];
            }
            $bookingsByDate[$booking['appointment_date']][$booking['slot']] = $booking['booking_count'];
        }

        // Get max appointments per slot from config
        $configSql = "SELECT config_value FROM system_config WHERE config_key = 'appointment_settings'";
        $configStmt = $this->query($configSql);
        $config = $configStmt->fetch();
        $appointmentSettings = json_decode($config['config_value'], true);
        $maxPerSlot = $appointmentSettings['max_appointments_per_slot'] ?? 3;

        // Build availability calendar
        $availability = [];
        $currentDate = new \DateTime($dateFrom);
        $endDate = new \DateTime($dateTo);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $availability[$dateStr] = [];

            foreach ($timeSlots as $slot) {
                $bookingCount = $bookingsByDate[$dateStr][$slot['slot_id']] ?? 0;
                $availableSpots = max(0, $maxPerSlot - $bookingCount);

                $availability[$dateStr][] = [
                    'slot_id' => $slot['slot_id'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'duration' => $slot['duration'],
                    'available_spots' => $availableSpots,
                    'is_available' => $availableSpots > 0
                ];
            }

            $currentDate->modify('+1 day');
        }

        return $availability;
    }

    /**
     * Get counters with their current load (appointment count)
     */
    public function getCountersWithLoad(int $centerId, string $date): array
    {
        $sql = "SELECT 
                    c.*,
                    COUNT(a.appointment_id) as appointment_count,
                    (SELECT COUNT(*) 
                     FROM appointment a2 
                     WHERE a2.at_counter = c.counter_id 
                     AND a2.appointment_date = ?
                     AND a2.appointment_status = 'scheduled') as today_appointments
                FROM counter c
                LEFT JOIN appointment a ON c.counter_id = a.at_counter 
                    AND a.appointment_date = ?
                    AND a.appointment_status = 'scheduled'
                WHERE c.center_id = ?
                AND c.is_active = 1
                GROUP BY c.counter_id
                ORDER BY c.counter_name ASC";

        $stmt = $this->query($sql, [$date, $date, $centerId]);
        $counters = $stmt->fetchAll();

        // Decode JSON fields
        foreach ($counters as &$counter) {
            if (isset($counter['service_handled'])) {
                $counter['service_handled'] = json_decode($counter['service_handled'], true);
            }
        }

        return $counters;
    }

    /**
     * Get counter statistics
     */
    public function getCounterStatistics(int $counterId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $counter = $this->findByCounterId($counterId);
        if (!$counter) {
            return [];
        }

        // Default to last 30 days if dates not provided
        if (!$dateFrom) {
            $dateFrom = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$dateTo) {
            $dateTo = date('Y-m-d');
        }

        // Get appointment statistics
        $sql = "SELECT 
                    COUNT(*) as total_appointments,
                    SUM(CASE WHEN appointment_status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN appointment_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN appointment_status = 'no-show' THEN 1 ELSE 0 END) as no_shows,
                    SUM(CASE WHEN appointment_status = 'scheduled' THEN 1 ELSE 0 END) as scheduled
                FROM appointment
                WHERE at_counter = ?
                AND appointment_date BETWEEN ? AND ?";

        $stmt = $this->query($sql, [$counterId, $dateFrom, $dateTo]);
        $stats = $stmt->fetch();

        // Get service breakdown
        $serviceSql = "SELECT 
                        s.service_id,
                        s.title,
                        s.category,
                        COUNT(a.appointment_id) as count
                    FROM appointment a
                    INNER JOIN service s ON a.booked_for_service = s.service_id
                    WHERE a.at_counter = ?
                    AND a.appointment_date BETWEEN ? AND ?
                    GROUP BY s.service_id
                    ORDER BY count DESC";

        $serviceStmt = $this->query($serviceSql, [$counterId, $dateFrom, $dateTo]);
        $serviceBreakdown = $serviceStmt->fetchAll();

        return [
            'counter' => $counter,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ],
            'statistics' => $stats,
            'service_breakdown' => $serviceBreakdown
        ];
    }

    /**
     * Update counter's services handled
     */
    public function updateServicesHandled(int $counterId, array $serviceIds): bool
    {
        $json = json_encode($serviceIds);
        $sql = "UPDATE counter SET service_handled = ? WHERE counter_id = ?";
        $stmt = $this->query($sql, [$json, $counterId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Activate/Deactivate a counter
     */
    public function toggleActive(int $counterId, bool $isActive): bool
    {
        $sql = "UPDATE counter SET is_active = ? WHERE counter_id = ?";
        $stmt = $this->query($sql, [$isActive ? 1 : 0, $counterId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if counter has any upcoming appointments
     */
    public function hasUpcomingAppointments(int $counterId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM appointment 
                WHERE at_counter = ? 
                AND appointment_date >= CURDATE()
                AND appointment_status = 'scheduled'";

        $stmt = $this->query($sql, [$counterId]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Get next available slot for a counter
     */
    public function getNextAvailableSlot(int $counterId, int $serviceId): ?array
    {
        // Check if counter handles this service
        if (!$this->handlesService($counterId, $serviceId)) {
            return null;
        }

        // Get max appointments per slot from config
        $configSql = "SELECT config_value FROM system_config WHERE config_key = 'appointment_settings'";
        $configStmt = $this->query($configSql);
        $config = $configStmt->fetch();
        $appointmentSettings = json_decode($config['config_value'], true);
        $maxPerSlot = $appointmentSettings['max_appointments_per_slot'] ?? 3;
        $advanceBookingDays = $appointmentSettings['advance_booking_days'] ?? 30;

        // Find next available slot within the advance booking window
        $sql = "SELECT 
                    DATE_ADD(CURDATE(), INTERVAL day_offset DAY) as appointment_date,
                    ts.slot_id,
                    ts.start_time,
                    ts.end_time,
                    ts.duration,
                    COALESCE(booking_counts.count, 0) as current_bookings,
                    (? - COALESCE(booking_counts.count, 0)) as available_spots
                FROM 
                    (SELECT 0 as day_offset UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
                     UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 
                     UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
                     UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
                     UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
                     UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
                     UNION SELECT 30) as days
                CROSS JOIN time_slots ts
                LEFT JOIN (
                    SELECT appointment_date, slot, COUNT(*) as count
                    FROM appointment
                    WHERE at_counter = ?
                    AND appointment_status = 'scheduled'
                    GROUP BY appointment_date, slot
                ) as booking_counts 
                ON DATE_ADD(CURDATE(), INTERVAL day_offset DAY) = booking_counts.appointment_date
                AND ts.slot_id = booking_counts.slot
                WHERE ts.is_active = 1
                AND day_offset <= ?
                HAVING available_spots > 0
                ORDER BY appointment_date ASC, ts.start_time ASC
                LIMIT 1";

        $stmt = $this->query($sql, [$maxPerSlot, $counterId, $advanceBookingDays]);
        return $stmt->fetch() ?: null;
    }
}