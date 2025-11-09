<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class TimeSlot extends BaseModel
{
    protected string $table = 'time_slots';
    protected string $primaryKey = 'slot_id';

    /**
     * Find time slot by slot_id
     */
    public function findBySlotId(int $slotId): ?array
    {
        return $this->find($slotId);
    }

    /**
     * Get all active time slots
     */
    public function getActiveTimeSlots(): array
    {
        return $this->findAll(['is_active' => 1], 'start_time ASC');
    }

    /**
     * Get all time slots (including inactive)
     */
    public function getAllTimeSlots(): array
    {
        $sql = "SELECT * FROM time_slots ORDER BY start_time ASC";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get time slots within a time range
     */
    public function getSlotsByTimeRange(string $startTime, string $endTime): array
    {
        $sql = "SELECT * FROM time_slots 
                WHERE is_active = 1 
                AND start_time >= ? 
                AND end_time <= ?
                ORDER BY start_time ASC";
        
        $stmt = $this->query($sql, [$startTime, $endTime]);
        return $stmt->fetchAll();
    }

    /**
     * Get morning slots (before 12:00)
     */
    public function getMorningSlots(): array
    {
        return $this->getSlotsByTimeRange('00:00:00', '12:00:00');
    }

    /**
     * Get afternoon slots (after 12:00)
     */
    public function getAfternoonSlots(): array
    {
        return $this->getSlotsByTimeRange('12:00:00', '23:59:59');
    }

    /**
     * Create a new time slot
     */
    public function createTimeSlot(array $data): int
    {
        // Calculate duration if not provided
        if (!isset($data['duration']) && isset($data['start_time']) && isset($data['end_time'])) {
            $start = new \DateTime($data['start_time']);
            $end = new \DateTime($data['end_time']);
            $data['duration'] = ($end->getTimestamp() - $start->getTimestamp()) / 60; // in minutes
        }

        $slotData = [
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'duration' => $data['duration'],
            'is_active' => $data['is_active'] ?? 1
        ];

        return $this->insert($slotData);
    }

    /**
     * Update time slot
     */
    public function updateTimeSlot(int $slotId, array $data): bool
    {
        $updateFields = [];
        $params = [];

        $allowedFields = ['start_time', 'end_time', 'duration', 'is_active'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        // Recalculate duration if start_time or end_time changed
        if (isset($data['start_time']) && isset($data['end_time']) && !isset($data['duration'])) {
            $start = new \DateTime($data['start_time']);
            $end = new \DateTime($data['end_time']);
            $duration = ($end->getTimestamp() - $start->getTimestamp()) / 60;
            $updateFields[] = "duration = ?";
            $params[] = $duration;
        }

        if (empty($updateFields)) {
            return true;
        }

        $params[] = $slotId;
        $sql = "UPDATE time_slots SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE slot_id = ?";
        $stmt = $this->query($sql, $params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Activate/Deactivate time slot
     */
    public function toggleActive(int $slotId, bool $isActive): bool
    {
        $sql = "UPDATE time_slots SET is_active = ?, updated_at = NOW() WHERE slot_id = ?";
        $stmt = $this->query($sql, [$isActive ? 1 : 0, $slotId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete time slot (only if no appointments exist)
     */
    public function deleteTimeSlot(int $slotId): array
    {
        // Check if slot has any appointments
        $sql = "SELECT COUNT(*) as count FROM appointment WHERE slot = ?";
        $stmt = $this->query($sql, [$slotId]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            return [
                'success' => false,
                'message' => 'Cannot delete time slot with existing appointments. Deactivate it instead.'
            ];
        }

        $sql = "DELETE FROM time_slots WHERE slot_id = ?";
        $stmt = $this->query($sql, [$slotId]);

        return [
            'success' => $stmt->rowCount() > 0,
            'message' => $stmt->rowCount() > 0 ? 'Time slot deleted successfully' : 'Time slot not found'
        ];
    }

    /**
     * Get slot availability for a specific date and counter
     */
    public function getSlotAvailability(int $counterId, string $date): array
    {
        $slots = $this->getActiveTimeSlots();

        // Get booking settings
        $configSql = "SELECT config_value FROM system_config WHERE config_key = 'appointment_settings'";
        $configStmt = $this->query($configSql);
        $config = $configStmt->fetch();
        $appointmentSettings = json_decode($config['config_value'], true);
        $maxPerSlot = $appointmentSettings['max_appointments_per_slot'] ?? 3;

        // Get current bookings for this counter and date
        $bookingSql = "SELECT 
                        a.slot,
                        COUNT(*) as booking_count
                    FROM appointment a
                    WHERE a.at_counter = ?
                    AND a.appointment_date = ?
                    AND a.appointment_status = 'scheduled'
                    GROUP BY a.slot";
        
        $bookingStmt = $this->query($bookingSql, [$counterId, $date]);
        $bookings = $bookingStmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Add availability info to each slot
        foreach ($slots as &$slot) {
            $bookingCount = $bookings[$slot['slot_id']] ?? 0;
            $slot['booked_count'] = $bookingCount;
            $slot['available_count'] = max(0, $maxPerSlot - $bookingCount);
            $slot['is_available'] = $slot['available_count'] > 0;
            $slot['max_capacity'] = $maxPerSlot;
        }

        return $slots;
    }

    /**
     * Get slot availability for a center (all counters combined)
     */
    public function getCenterSlotAvailability(int $centerId, int $serviceId, string $date): array
    {
        $slots = $this->getActiveTimeSlots();

        // Get counters that handle this service at this center
        $counterSql = "SELECT counter_id 
                       FROM counter 
                       WHERE center_id = ? 
                       AND is_active = 1
                       AND JSON_CONTAINS(service_handled, ?, '$')";
        $counterStmt = $this->query($counterSql, [$centerId, json_encode($serviceId)]);
        $counters = $counterStmt->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($counters)) {
            // No counters available
            foreach ($slots as &$slot) {
                $slot['booked_count'] = 0;
                $slot['available_count'] = 0;
                $slot['is_available'] = false;
                $slot['max_capacity'] = 0;
            }
            return $slots;
        }

        // Get booking settings
        $configSql = "SELECT config_value FROM system_config WHERE config_key = 'appointment_settings'";
        $configStmt = $this->query($configSql);
        $config = $configStmt->fetch();
        $appointmentSettings = json_decode($config['config_value'], true);
        $maxPerSlot = $appointmentSettings['max_appointments_per_slot'] ?? 3;
        $totalCapacity = $maxPerSlot * count($counters);

        // Get bookings for all counters
        $placeholders = implode(',', array_fill(0, count($counters), '?'));
        $bookingSql = "SELECT 
                        b.booked_slot,
                        COUNT(*) as booking_count
                    FROM booking b
                    INNER JOIN appointment a ON b.appointment = a.appointment_id
                    WHERE b.booked_date = ?
                    AND a.at_counter IN ($placeholders)
                    AND a.appointment_status = 'scheduled'
                    GROUP BY b.booked_slot";
        
        $params = array_merge([$date], $counters);
        $bookingStmt = $this->query($bookingSql, $params);
        $bookings = $bookingStmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Add availability info to each slot
        foreach ($slots as &$slot) {
            $bookingCount = $bookings[$slot['slot_id']] ?? 0;
            $slot['booked_count'] = $bookingCount;
            $slot['available_count'] = max(0, $totalCapacity - $bookingCount);
            $slot['is_available'] = $slot['available_count'] > 0;
            $slot['max_capacity'] = $totalCapacity;
            $slot['counter_count'] = count($counters);
        }

        return $slots;
    }

    /**
     * Get time slot statistics
     */
    public function getSlotStats(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $stats = [];

        // Total slots
        $stmt = $this->query("SELECT COUNT(*) as total FROM time_slots");
        $stats['total'] = (int)$stmt->fetch()['total'];

        // Active slots
        $stmt = $this->query("SELECT COUNT(*) as count FROM time_slots WHERE is_active = 1");
        $stats['active'] = (int)$stmt->fetch()['count'];

        // Build date filter
        $dateWhere = "WHERE 1=1";
        $params = [];
        
        if ($dateFrom) {
            $dateWhere .= " AND a.appointment_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $dateWhere .= " AND a.appointment_date <= ?";
            $params[] = $dateTo;
        }

        // Most booked time slots
        $sql = "SELECT 
                    ts.slot_id,
                    ts.start_time,
                    ts.end_time,
                    COUNT(a.appointment_id) as booking_count
                FROM time_slots ts
                LEFT JOIN appointment a ON ts.slot_id = a.slot
                $dateWhere
                GROUP BY ts.slot_id
                ORDER BY booking_count DESC";
        
        $stmt = $this->query($sql, $params);
        $stats['most_booked'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * Bulk create time slots for a day
     */
    public function bulkCreateSlots(string $startTime, string $endTime, int $slotDuration): array
    {
        $created = [];
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);

        while ($start < $end) {
            $slotEnd = clone $start;
            $slotEnd->modify("+{$slotDuration} minutes");

            if ($slotEnd > $end) {
                break;
            }

            // Check if slot already exists
            $checkSql = "SELECT COUNT(*) as count FROM time_slots WHERE start_time = ? AND end_time = ?";
            $checkStmt = $this->query($checkSql, [$start->format('H:i:s'), $slotEnd->format('H:i:s')]);
            $exists = $checkStmt->fetch()['count'] > 0;

            if (!$exists) {
                $slotId = $this->createTimeSlot([
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s'),
                    'duration' => $slotDuration
                ]);

                $created[] = [
                    'slot_id' => $slotId,
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s'),
                    'duration' => $slotDuration
                ];
            }

            $start = $slotEnd;
        }

        return $created;
    }

    /**
     * Check if a time slot has conflicts
     */
    public function hasConflict(string $startTime, string $endTime, ?int $excludeSlotId = null): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM time_slots 
                WHERE (
                    (start_time < ? AND end_time > ?) OR
                    (start_time < ? AND end_time > ?) OR
                    (start_time >= ? AND end_time <= ?)
                )";
        
        $params = [$endTime, $startTime, $endTime, $endTime, $startTime, $endTime];

        if ($excludeSlotId) {
            $sql .= " AND slot_id != ?";
            $params[] = $excludeSlotId;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetch()['count'] > 0;
    }

    /**
     * Get next available slot
     */
    public function getNextAvailableSlot(): ?array
    {
        $now = new \DateTime();
        $currentTime = $now->format('H:i:s');

        $sql = "SELECT * FROM time_slots 
                WHERE is_active = 1 
                AND start_time > ?
                ORDER BY start_time ASC 
                LIMIT 1";
        
        $stmt = $this->query($sql, [$currentTime]);
        return $stmt->fetch() ?: null;
    }
}