<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class AppointmentSlot extends BaseModel
{
    protected string $table = 'appointment_slots';
    protected string $primaryKey = 'id';

    /**
     * Find slot by slot_id
     */
    public function findBySlotId(string $slotId): ?array
    {
        return $this->findBy('slot_id', $slotId);
    }

    /**
     * Get available slots for a counter on a specific date
     */
    public function getAvailableSlots(string $counterId, string $date): array
    {
        $sql = "SELECT 
                    slot_id,
                    slot_date,
                    start_time,
                    end_time,
                    duration_minutes,
                    max_appointments,
                    current_bookings,
                    (max_appointments - current_bookings) as available_capacity
                FROM appointment_slots
                WHERE counter_id = ?
                AND slot_date = ?
                AND is_available = 1
                AND current_bookings < max_appointments
                ORDER BY start_time ASC";

        $stmt = $this->query($sql, [$counterId, $date]);
        return $stmt->fetchAll();
    }

    /**
     * Get available slots for multiple counters (for a service at a center)
     */
    public function getAvailableSlotsForService(string $centerId, string $serviceId, string $dateFrom, string $dateTo): array
    {
        $sql = "SELECT 
                    s.slot_id,
                    s.counter_id,
                    c.counter_number,
                    c.counter_name,
                    s.slot_date,
                    s.start_time,
                    s.end_time,
                    s.duration_minutes,
                    s.max_appointments,
                    s.current_bookings,
                    (s.max_appointments - s.current_bookings) as available_capacity
                FROM appointment_slots s
                INNER JOIN service_counters c ON s.counter_id = c.counter_id
                WHERE c.center_id = ?
                AND c.is_active = 1
                AND JSON_CONTAINS(c.services_handled, ?, '$')
                AND s.slot_date BETWEEN ? AND ?
                AND s.is_available = 1
                AND s.current_bookings < s.max_appointments
                ORDER BY s.slot_date ASC, s.start_time ASC, c.counter_number ASC";

        $stmt = $this->query($sql, [$centerId, json_encode($serviceId), $dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }

    /**
     * Check if slot has capacity
     */
    public function hasCapacity(string $slotId): bool
    {
        $sql = "SELECT (max_appointments - current_bookings) as available 
                FROM appointment_slots 
                WHERE slot_id = ? AND is_available = 1";

        $stmt = $this->query($sql, [$slotId]);
        $result = $stmt->fetch();

        return $result && $result['available'] > 0;
    }

    /**
     * Increment booking count (when appointment is booked)
     */
    public function incrementBooking(string $slotId): bool
    {
        $sql = "UPDATE appointment_slots 
                SET current_bookings = current_bookings + 1,
                    updated_at = NOW()
                WHERE slot_id = ? 
                AND current_bookings < max_appointments";

        $stmt = $this->query($sql, [$slotId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Decrement booking count (when appointment is cancelled)
     */
    public function decrementBooking(string $slotId): bool
    {
        $sql = "UPDATE appointment_slots 
                SET current_bookings = GREATEST(0, current_bookings - 1),
                    updated_at = NOW()
                WHERE slot_id = ?";

        $stmt = $this->query($sql, [$slotId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Bulk create slots for a counter
     */
    public function bulkCreateSlots(string $counterId, array $dates, array $timeSlots, int $maxAppointments = 3): int
    {
        $created = 0;

        $this->beginTransaction();
        try {
            foreach ($dates as $date) {
                foreach ($timeSlots as $timeSlot) {
                    $slotData = [
                        'slot_id' => $this->generateSlotId(),
                        'counter_id' => $counterId,
                        'slot_date' => $date,
                        'start_time' => $timeSlot['start_time'],
                        'end_time' => $timeSlot['end_time'],
                        'duration_minutes' => $timeSlot['duration_minutes'] ?? 45,
                        'max_appointments' => $maxAppointments,
                        'current_bookings' => 0,
                        'is_available' => true,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->insert($slotData);
                    $created++;
                }
            }

            $this->commit();
            return $created;

        } catch (\Exception $e) {
            $this->rollback();
            error_log("Bulk slot creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate time slots for a date range
     * Helper function to create standard time slots
     */
    public static function generateTimeSlots(string $startTime = '09:00', string $endTime = '17:00', int $slotDuration = 45): array
    {
        $slots = [];
        $current = strtotime($startTime);
        $end = strtotime($endTime);

        while ($current < $end) {
            $nextSlot = $current + ($slotDuration * 60);
            
            if ($nextSlot <= $end) {
                $slots[] = [
                    'start_time' => date('H:i:s', $current),
                    'end_time' => date('H:i:s', $nextSlot),
                    'duration_minutes' => $slotDuration
                ];
            }

            $current = $nextSlot;
        }

        return $slots;
    }

    /**
     * Get slot statistics
     */
    public function getSlotStats(string $counterId, string $dateFrom, string $dateTo): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_slots,
                    SUM(max_appointments) as total_capacity,
                    SUM(current_bookings) as total_bookings,
                    SUM(max_appointments - current_bookings) as available_capacity,
                    ROUND(AVG(current_bookings / max_appointments * 100), 2) as utilization_rate
                FROM appointment_slots
                WHERE counter_id = ?
                AND slot_date BETWEEN ? AND ?";

        $stmt = $this->query($sql, [$counterId, $dateFrom, $dateTo]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Generate unique slot ID
     */
    private function generateSlotId(): string
    {
        do {
            $slotId = 'SLT' . strtoupper(bin2hex(random_bytes(4)));
        } while ($this->findBySlotId($slotId));

        return $slotId;
    }
}