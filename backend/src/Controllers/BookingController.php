<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\Booking;
use IndianConsular\Models\Service;
use IndianConsular\Models\VerificationCenter;
use IndianConsular\Models\User;
use IndianConsular\Models\TimeSlot;

class BookingController extends BaseController
{
    private Booking $bookingModel;
    private Service $serviceModel;
    private VerificationCenter $centerModel;
    private User $userModel;
    private TimeSlot $timeSlotModel;

    public function __construct()
    {
        parent::__construct();
        $this->bookingModel = new Booking();
        $this->serviceModel = new Service();
        $this->centerModel = new VerificationCenter();
        $this->userModel = new User();
        $this->timeSlotModel = new TimeSlot();
    }

    /**
     * Step 1: Get available services
     * GET /booking/services
     */
    public function getServices(array $data, array $params): array
    {
        try {
            $services = $this->bookingModel->getAvailableServices();

            return $this->success([
                'services' => $services,
                'count' => count($services)
            ]);

        } catch (\Exception $e) {
            error_log("Get services error: " . $e->getMessage());
            return $this->error('Failed to load services', 500);
        }
    }

    /**
     * Step 2: Get centers for selected service
     * GET /booking/centers/{serviceId}
     */
    public function getCentersForService(array $data, array $params): array
    {
        $serviceId = $params['serviceId'] ?? '';

        if (empty($serviceId)) {
            return $this->error('Service ID is required', 400);
        }

        try {
            $centers = $this->bookingModel->getCentersForService((int)$serviceId);

            return $this->success([
                'centers' => $centers,
                'count' => count($centers)
            ]);

        } catch (\Exception $e) {
            error_log("Get centers error: " . $e->getMessage());
            return $this->error('Failed to load centers', 500);
        }
    }

    /**
     * Step 3: Get user details for pre-filling form
     * GET /booking/user-details
     */
    public function getUserDetails(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);
        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $user = $this->bookingModel->getUserDetails((int)$auth['id']);

            if (!$user) {
                return $this->error('User not found', 404);
            }

            // Remove sensitive data
            unset($user['password_hash']);

            return $this->success(['user' => $user]);

        } catch (\Exception $e) {
            error_log("Get user details error: " . $e->getMessage());
            return $this->error('Failed to load user details', 500);
        }
    }

    /**
     * Step 4: Get available dates within booking window
     * GET /booking/available-dates
     * Query params: centerId, serviceId
     */
    public function getAvailableDates(array $data, array $params): array
    {
        $centerId = $data['centerId'] ?? '';
        $serviceId = $data['serviceId'] ?? '';

        if (empty($centerId) || empty($serviceId)) {
            return $this->error('Center ID and Service ID are required', 400);
        }

        try {
            $dates = $this->bookingModel->getAvailableDates((int)$centerId, (int)$serviceId);

            return $this->success([
                'dates' => $dates,
                'count' => count($dates)
            ]);

        } catch (\Exception $e) {
            error_log("Get available dates error: " . $e->getMessage());
            return $this->error('Failed to load available dates', 500);
        }
    }

    /**
     * Step 5: Get available slots for selected date
     * GET /booking/available-slots
     * Query params: centerId, serviceId, date
     */
    public function getAvailableSlots(array $data, array $params): array
    {
        $centerId = $data['centerId'] ?? '';
        $serviceId = $data['serviceId'] ?? '';
        $date = $data['date'] ?? '';

        if (empty($centerId) || empty($serviceId) || empty($date)) {
            return $this->error('Center ID, Service ID, and Date are required', 400);
        }

        try {
            $slots = $this->bookingModel->getAvailableSlotsForDate(
                (int)$centerId,
                (int)$serviceId,
                $date
            );

            return $this->success([
                'date' => $date,
                'slots' => $slots,
                'count' => count($slots)
            ]);

        } catch (\Exception $e) {
            error_log("Get available slots error: " . $e->getMessage());
            return $this->error('Failed to load available slots', 500);
        }
    }

    /**
     * Step 6: Create booking
     * POST /booking/create
     */
    public function createBooking(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);
        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        $data = $this->sanitize($data);

        // Validate required fields
        $missing = $this->validateRequired($data, [
            'serviceId', 'centerId', 'date', 'slotId'
        ]);

        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            // Validate booking data
            $errors = $this->bookingModel->validateBookingData([
                'user_id' => (int)$auth['id'],
                'service_id' => (int)$data['serviceId'],
                'center_id' => (int)$data['centerId'],
                'date' => $data['date'],
                'slot_id' => (int)$data['slotId']
            ]);

            if (!empty($errors)) {
                return $this->error('Validation failed', 400, ['errors' => $errors]);
            }

            // Prepare booking data
            $bookingData = [
                'user_id' => (int)$auth['id'],
                'service_id' => (int)$data['serviceId'],
                'center_id' => (int)$data['centerId'],
                'date' => $data['date'],
                'slot_id' => (int)$data['slotId'],
                'user_details' => []
            ];

            // Include user details if provided (for profile update)
            if (!empty($data['userDetails'])) {
                $bookingData['user_details'] = [
                    'gender' => $data['userDetails']['gender'] ?? null,
                    'phone_no' => $data['userDetails']['phoneNo'] ?? null,
                    'date_of_birth' => $data['userDetails']['dateOfBirth'] ?? null,
                    'nationality' => $data['userDetails']['nationality'] ?? null,
                    'passport_no' => $data['userDetails']['passportNo'] ?? null,
                    'passport_expiry' => $data['userDetails']['passportExpiry'] ?? null
                ];
            }

            // Create the booking
            $result = $this->bookingModel->createBooking($bookingData);

            if (!$result['success']) {
                return $this->error($result['error'] ?? 'Failed to create booking', 400);
            }

            // Log user activity
            $this->logService->logUserActivity(
                (string)$auth['id'],
                'BOOKING_CREATED',
                [
                    'booking_id' => $result['booking_id'],
                    'appointment_id' => $result['appointment_id'],
                    'service_id' => $data['serviceId'],
                    'date' => $data['date']
                ],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->success([
                'bookingId' => $result['booking_id'],
                'appointmentId' => $result['appointment_id'],
                'confirmation' => $result['confirmation'],
                'message' => 'Booking created successfully'
            ], 201);

        } catch (\Exception $e) {
            error_log("Create booking error: " . $e->getMessage());
            return $this->error('Failed to create booking', 500);
        }
    }

    /**
     * Step 7: Get booking confirmation
     * GET /booking/confirmation/{bookingId}
     */
    public function getConfirmation(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);
        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        $bookingId = $params['bookingId'] ?? '';

        if (empty($bookingId)) {
            return $this->error('Booking ID is required', 400);
        }

        try {
            $confirmation = $this->bookingModel->getBookingConfirmation((int)$bookingId);

            if (!$confirmation) {
                return $this->error('Booking not found', 404);
            }

            // Verify booking belongs to user
            if ($confirmation['user_id'] != $auth['id']) {
                return $this->error('Unauthorized access to booking', 403);
            }

            return $this->success(['booking' => $confirmation]);

        } catch (\Exception $e) {
            error_log("Get confirmation error: " . $e->getMessage());
            return $this->error('Failed to load confirmation', 500);
        }
    }

    /**
     * Get user's bookings
     * GET /booking/my-bookings
     */
    public function getMyBookings(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);
        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $status = $data['status'] ?? null;
            $bookings = $this->bookingModel->getUserBookings((int)$auth['id'], $status);

            return $this->success([
                'bookings' => $bookings,
                'count' => count($bookings)
            ]);

        } catch (\Exception $e) {
            error_log("Get user bookings error: " . $e->getMessage());
            return $this->error('Failed to load bookings', 500);
        }
    }

    /**
     * Cancel booking
     * POST /booking/cancel/{bookingId}
     */
    public function cancelBooking(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);
        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        $bookingId = $params['bookingId'] ?? '';

        if (empty($bookingId)) {
            return $this->error('Booking ID is required', 400);
        }

        try {
            $result = $this->bookingModel->cancelBooking((int)$bookingId, (int)$auth['id']);

            if (!$result['success']) {
                return $this->error($result['error'] ?? 'Failed to cancel booking', 400);
            }

            // Log user activity
            $this->logService->logUserActivity(
                (string)$auth['id'],
                'BOOKING_CANCELLED',
                ['booking_id' => $bookingId],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->success([
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            error_log("Cancel booking error: " . $e->getMessage());
            return $this->error('Failed to cancel booking', 500);
        }
    }

    /**
     * Get booking settings (for frontend configuration)
     * GET /booking/settings
     */
    public function getSettings(array $data, array $params): array
    {
        try {
            $settings = $this->bookingModel->getBookingSettings();

            return $this->success(['settings' => $settings]);

        } catch (\Exception $e) {
            error_log("Get booking settings error: " . $e->getMessage());
            return $this->error('Failed to load booking settings', 500);
        }
    }
}