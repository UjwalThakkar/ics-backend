<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class Booking extends BaseModel
{
    protected string $table = 'booking';
    protected string $primaryKey = 'booking_id';

    /**
     * Step 1: Get all active services
     */
    public function getAvailableServices(): array
    {
        $sql = "SELECT * FROM service WHERE is_active = 1 ORDER BY display_order ASC, category ASC, title ASC";
        $stmt = $this->query($sql);
        $services = $stmt->fetchAll();

        // Decode JSON fields
        foreach ($services as &$service) {
            if (isset($service['fees'])) {
                $service['fees'] = json_decode($service['fees'], true);
            }
            if (isset($service['required_documents'])) {
                $service['required_documents'] = json_decode($service['required_documents'], true);
            }
            if (isset($service['eligibility_requirements'])) {
                $service['eligibility_requirements'] = json_decode($service['eligibility_requirements'], true);
            }
        }

        return $services;
    }

    /**
     * Step 2: Get verification centers that provide a specific service
     */
    public function getCentersForService(int $serviceId): array
    {
        $sql = "SELECT 
                    vc.*,
                    COUNT(c.counter_id) as counter_count
                FROM verification_center vc
                LEFT JOIN counter c ON vc.center_id = c.center_id AND c.is_active = 1
                WHERE vc.is_active = 1
                AND JSON_CONTAINS(vc.provides_services, ?, '$')
                GROUP BY vc.center_id
                ORDER BY vc.display_order ASC, vc.name ASC";

        $stmt = $this->query($sql, [json_encode($serviceId)]);
        $centers = $stmt->fetchAll();

        // Decode JSON fields
        foreach ($centers as &$center) {
            if (isset($center['operating_hours'])) {
                $center['operating_hours'] = json_decode($center['operating_hours'], true);
            }
            if (isset($center['provides_services'])) {
                $center['provides_services'] = json_decode($center['provides_services'], true);
            }
            if (isset($center['has_counters'])) {
                $center['has_counters'] = json_decode($center['has_counters'], true);
            }
        }

        return $centers;
    }

    /**
     * Step 3: Get user details for pre-filling the form
     */
    public function getUserDetails(int $userId): ?array
    {
        $sql = "SELECT * FROM user WHERE user_id = ?";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Step 4: Get booking settings (advance booking days, etc.)
     */
    public function getBookingSettings(): array
    {
        $sql = "SELECT config_value FROM system_config WHERE config_key = 'appointment_settings'";
        $stmt = $this->query($sql);
        $config = $stmt->fetch();
        
        if ($config) {
            return json_decode($config['config_value'], true);
        }

        // Default settings if not found in config
        return [
            'slot_duration_minutes' => 45,
            'max_appointments_per_slot' => 1,
            'advance_booking_days' => 7,
            'cancellation_hours' => 24
        ];
    }

    /**
     * Step 4: Check if a date has available slots
     */
    public function getAvailableSlotsForDate(int $centerId, int $serviceId, string $date): array
    {
        // Get all active time slots
        $sql = "SELECT * FROM time_slots WHERE is_active = 1 ORDER BY start_time ASC";
        $stmt = $this->query($sql);
        $allSlots = $stmt->fetchAll();

        // Get counters that handle this service at this center
        $counterSql = "SELECT counter_id 
                       FROM counter 
                       WHERE center_id = ? 
                       AND is_active = 1
                       AND JSON_CONTAINS(service_handled, ?, '$')";
        $counterStmt = $this->query($counterSql, [$centerId, json_encode($serviceId)]);
        $counters = $counterStmt->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($counters)) {
            return []; // No counters available for this service
        }

        // Get bookings for this date and these counters
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
        $bookedSlots = $bookingStmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Get max appointments per slot
        $settings = $this->getBookingSettings();
        $maxPerSlot = $settings['max_appointments_per_slot'] ?? 1;
        $totalCounters = count($counters);
        $maxTotalAppointments = $maxPerSlot * $totalCounters;

        // Calculate availability for each slot
        foreach ($allSlots as &$slot) {
            $bookingCount = $bookedSlots[$slot['slot_id']] ?? 0;
            $slot['booked_count'] = $bookingCount;
            $slot['available_count'] = max(0, $maxTotalAppointments - $bookingCount);
            $slot['is_available'] = $slot['available_count'] > 0;
            $slot['total_capacity'] = $maxTotalAppointments;
        }

        return $allSlots;
    }

    /**
     * Step 4: Get available dates within booking window
     */
    public function getAvailableDates(int $centerId, int $serviceId): array
    {
        $settings = $this->getBookingSettings();
        $advanceDays = $settings['advance_booking_days'] ?? 7;
        
        $startDate = new \DateTime();
        $startDate->modify('+1 day'); // Start from tomorrow
        
        $endDate = new \DateTime();
        $endDate->modify("+{$advanceDays} days");

        $availableDates = [];
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayOfWeek = $currentDate->format('w'); // 0 (Sunday) to 6 (Saturday)
            
            // Skip Sundays (you can customize this based on center's operating hours)
            if ($dayOfWeek != 0) {
                $slots = $this->getAvailableSlotsForDate($centerId, $serviceId, $dateStr);
                $hasAvailability = false;
                
                foreach ($slots as $slot) {
                    if ($slot['is_available']) {
                        $hasAvailability = true;
                        break;
                    }
                }

                $availableDates[] = [
                    'date' => $dateStr,
                    'day_of_week' => $currentDate->format('l'),
                    'has_availability' => $hasAvailability,
                    'formatted_date' => $currentDate->format('F j, Y')
                ];
            }

            $currentDate->modify('+1 day');
        }

        return $availableDates;
    }

    /**
     * Step 5: Get available counters for a specific date and slot
     */
    private function getAvailableCounter(int $centerId, int $serviceId, string $date, int $slotId): ?int
    {
        // Get counters that handle this service at this center
        $sql = "SELECT c.counter_id 
                FROM counter c
                WHERE c.center_id = ? 
                AND c.is_active = 1
                AND JSON_CONTAINS(c.service_handled, ?, '$')";
        
        $stmt = $this->query($sql, [$centerId, json_encode($serviceId)]);
        $counters = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($counters)) {
            return null;
        }

        // Get max appointments per slot
        $settings = $this->getBookingSettings();
        $maxPerSlot = $settings['max_appointments_per_slot'] ?? 1;

        // Find a counter with availability
        foreach ($counters as $counterId) {
            $checkSql = "SELECT COUNT(*) as count
                        FROM appointment a
                        INNER JOIN booking b ON a.appointment_id = b.appointment
                        WHERE a.at_counter = ?
                        AND b.booked_date = ?
                        AND b.booked_slot = ?
                        AND a.appointment_status = 'scheduled'";
            
            $checkStmt = $this->query($checkSql, [$counterId, $date, $slotId]);
            $result = $checkStmt->fetch();
            
            if ($result['count'] < $maxPerSlot) {
                return $counterId;
            }
        }

        return null; // No available counter found
    }

    /**
     * Step 6: Update user details (personal information)
     */
    public function updateUserDetails(int $userId, array $details): bool
    {
        $updates = [];
        $params = [];
        
        $allowedFields = ['gender', 'date_of_birth', 'nationality', 'passport_no', 'passport_expiry'];
        
        foreach ($allowedFields as $field) {
            if (isset($details[$field])) {
                $updates[] = "$field = ?";
                $params[] = $details[$field];
            }
        }

        if (empty($updates)) {
            return true; // Nothing to update
        }

        $params[] = $userId;
        $sql = "UPDATE user SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Step 6: Create complete booking (appointment + booking entry)
     */
    public function createBooking(array $bookingData): array
    {
        try {
            // Start transaction
            $this->beginTransaction();

            // 1. Update user details if provided
            if (!empty($bookingData['user_details'])) {
                $this->updateUserDetails($bookingData['user_id'], $bookingData['user_details']);
            }

            // 2. Find available counter
            $counterId = $this->getAvailableCounter(
                $bookingData['center_id'],
                $bookingData['service_id'],
                $bookingData['date'],
                $bookingData['slot_id']
            );

            if (!$counterId) {
                throw new \Exception('No available counter found for the selected date and time');
            }

            // 3. Create appointment record
            $appointmentSql = "INSERT INTO appointment 
                              (booked_by, booked_for_service, at_counter, appointment_date, slot, appointment_status, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, ?, 'scheduled', NOW(), NOW())";
            
            $appointmentStmt = $this->query($appointmentSql, [
                $bookingData['user_id'],
                $bookingData['service_id'],
                $counterId,
                $bookingData['date'],
                $bookingData['slot_id']
            ]);

            $appointmentId = (int) $this->lastInsertId();

            // 4. Create booking record
            $bookingSql = "INSERT INTO booking 
                          (booked_date, booked_slot, appointment, created_at) 
                          VALUES (?, ?, ?, NOW())";
            
            $bookingStmt = $this->query($bookingSql, [
                $bookingData['date'],
                $bookingData['slot_id'],
                $appointmentId
            ]);

            $bookingId = (int) $this->lastInsertId();

            // 5. Get complete booking details for confirmation
            $confirmationData = $this->getBookingConfirmation($bookingId);

            // 6. Create notification (optional - if you have notification system)
            $this->createBookingNotification($appointmentId, $bookingData['user_id']);

            // Commit transaction
            $this->commit();

            return [
                'success' => true,
                'booking_id' => $bookingId,
                'appointment_id' => $appointmentId,
                'confirmation' => $confirmationData
            ];

        } catch (\Exception $e) {
            // Rollback on error
            $this->rollback();
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get complete booking confirmation details
     */
    public function getBookingConfirmation(int $bookingId): ?array
    {
        $sql = "SELECT 
                    b.booking_id,
                    b.booked_date,
                    b.created_at as booking_created_at,
                    a.appointment_id,
                    a.appointment_status,
                    ts.slot_id,
                    ts.start_time,
                    ts.end_time,
                    ts.duration,
                    s.service_id,
                    s.title as service_title,
                    s.category as service_category,
                    s.processing_time,
                    s.fees,
                    s.required_documents,
                    c.counter_id,
                    c.counter_name,
                    vc.center_id,
                    vc.name as center_name,
                    vc.address as center_address,
                    vc.city as center_city,
                    vc.state as center_state,
                    vc.country as center_country,
                    vc.phone as center_phone,
                    vc.email as center_email,
                    u.user_id,
                    u.first_name,
                    u.last_name,
                    u.email as user_email,
                    u.phone_no,
                    u.passport_no
                FROM booking b
                INNER JOIN appointment a ON b.appointment = a.appointment_id
                INNER JOIN time_slots ts ON b.booked_slot = ts.slot_id
                INNER JOIN service s ON a.booked_for_service = s.service_id
                INNER JOIN counter c ON a.at_counter = c.counter_id
                INNER JOIN verification_center vc ON c.center_id = vc.center_id
                INNER JOIN user u ON a.booked_by = u.user_id
                WHERE b.booking_id = ?";

        $stmt = $this->query($sql, [$bookingId]);
        $confirmation = $stmt->fetch();

        if ($confirmation) {
            // Decode JSON fields
            if (isset($confirmation['fees'])) {
                $confirmation['fees'] = json_decode($confirmation['fees'], true);
            }
            if (isset($confirmation['required_documents'])) {
                $confirmation['required_documents'] = json_decode($confirmation['required_documents'], true);
            }
        }

        return $confirmation ?: null;
    }

    /**
     * Get user's bookings
     */
    public function getUserBookings(int $userId, ?string $status = null): array
    {
        $sql = "SELECT 
                    b.booking_id,
                    b.booked_date,
                    b.created_at,
                    a.appointment_id,
                    a.appointment_status,
                    ts.start_time,
                    ts.end_time,
                    s.title as service_title,
                    s.category as service_category,
                    c.counter_name,
                    vc.name as center_name,
                    vc.city as center_city
                FROM booking b
                INNER JOIN appointment a ON b.appointment = a.appointment_id
                INNER JOIN time_slots ts ON b.booked_slot = ts.slot_id
                INNER JOIN service s ON a.booked_for_service = s.service_id
                INNER JOIN counter c ON a.at_counter = c.counter_id
                INNER JOIN verification_center vc ON c.center_id = vc.center_id
                WHERE a.booked_by = ?";

        $params = [$userId];

        if ($status) {
            $sql .= " AND a.appointment_status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY b.booked_date DESC, ts.start_time DESC";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(int $bookingId, int $userId): array
    {
        try {
            // Get booking details
            $confirmation = $this->getBookingConfirmation($bookingId);
            
            if (!$confirmation) {
                return ['success' => false, 'error' => 'Booking not found'];
            }

            // Verify the booking belongs to the user
            if ($confirmation['user_id'] != $userId) {
                return ['success' => false, 'error' => 'Unauthorized'];
            }

            // Check if booking can be cancelled (based on cancellation hours policy)
            $settings = $this->getBookingSettings();
            $cancellationHours = $settings['cancellation_hours'] ?? 24;
            
            $appointmentDateTime = new \DateTime($confirmation['booked_date'] . ' ' . $confirmation['start_time']);
            $now = new \DateTime();
            $hoursDifference = ($appointmentDateTime->getTimestamp() - $now->getTimestamp()) / 3600;

            if ($hoursDifference < $cancellationHours) {
                return [
                    'success' => false, 
                    'error' => "Cancellation must be done at least {$cancellationHours} hours before the appointment"
                ];
            }

            // Update appointment status
            $sql = "UPDATE appointment SET appointment_status = 'cancelled', updated_at = NOW() WHERE appointment_id = ?";
            $stmt = $this->query($sql, [$confirmation['appointment_id']]);

            return [
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create booking notification
     */
    private function createBookingNotification(int $appointmentId, int $userId): void
    {
        // Get user details
        $userSql = "SELECT email, first_name, last_name FROM user WHERE user_id = ?";
        $userStmt = $this->query($userSql, [$userId]);
        $user = $userStmt->fetch();

        if (!$user) {
            return;
        }

        // Generate notification ID
        $notificationId = 'NOTIF' . strtoupper(bin2hex(random_bytes(6)));

        // Create notification record
        $sql = "INSERT INTO notifications 
                (notification_id, type, recipient_email, subject, content, template_id, appointment_id, user_id, status, created_at)
                VALUES (?, 'email', ?, 'Appointment Confirmation', ?, 'appointment_confirmed', ?, ?, 'pending', NOW())";

        $content = "Dear {$user['first_name']} {$user['last_name']}, your appointment has been confirmed.";

        $this->query($sql, [
            $notificationId,
            $user['email'],
            $content,
            $appointmentId,
            $userId
        ]);
    }

    /**
     * Validate booking data before creating
     */
    public function validateBookingData(array $data): array
    {
        $errors = [];

        // Required fields
        $required = ['user_id', 'service_id', 'center_id', 'date', 'slot_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Validate date is within booking window
        $settings = $this->getBookingSettings();
        $advanceDays = $settings['advance_booking_days'] ?? 7;
        
        if (!empty($data['date'])) {
            $selectedDate = new \DateTime($data['date']);
            $today = new \DateTime();
            $maxDate = new \DateTime();
            $maxDate->modify("+{$advanceDays} days");

            if ($selectedDate <= $today) {
                $errors[] = "Selected date must be in the future";
            }

            if ($selectedDate > $maxDate) {
                $errors[] = "Selected date exceeds the maximum booking window of {$advanceDays} days";
            }
        }

        return $errors;
    }


}