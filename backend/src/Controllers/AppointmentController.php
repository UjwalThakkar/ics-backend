<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\Appointment;
use IndianConsular\Models\Service;
use IndianConsular\Models\User;

class AppointmentController extends BaseController
{
    private Appointment $appointmentModel;
    private Service $serviceModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->appointmentModel = new Appointment();
        $this->serviceModel = new Service();
        $this->userModel = new User();
    }

    /**
     * Get user's appointments
     * GET /appointments
     */
    public function getMyAppointments(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);
        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $status = $data['status'] ?? null;
            $appointments = $this->appointmentModel->getUserAppointments((int)$auth['id'], $status);

            return $this->success([
                'appointments' => $appointments,
                'count' => count($appointments)
            ]);

        } catch (\Exception $e) {
            error_log("Get appointments error: " . $e->getMessage());
            return $this->error('Failed to load appointments', 500);
        }
    }

    /**
     * Get single appointment details
     * GET /appointments/{id}
     */
    public function getAppointment(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);
        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        $appointmentId = $params['id'] ?? '';
        if (empty($appointmentId)) {
            return $this->error('Appointment ID is required', 400);
        }

        try {
            $appointment = $this->appointmentModel->getAppointmentDetails((int)$appointmentId);

            if (!$appointment) {
                return $this->error('Appointment not found', 404);
            }

            // Verify appointment belongs to user
            if ($appointment['user_id'] != $auth['id']) {
                return $this->error('Unauthorized access to appointment', 403);
            }

            return $this->success(['appointment' => $appointment]);

        } catch (\Exception $e) {
            error_log("Get appointment details error: " . $e->getMessage());
            return $this->error('Failed to load appointment', 500);
        }
    }

    /**
     * Cancel appointment
     * POST /appointments/{id}/cancel
     */
    public function cancelAppointment(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);
        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        $appointmentId = $params['id'] ?? '';
        if (empty($appointmentId)) {
            return $this->error('Appointment ID is required', 400);
        }

        try {
            $appointment = $this->appointmentModel->getAppointmentDetails((int)$appointmentId);

            if (!$appointment) {
                return $this->error('Appointment not found', 404);
            }

            // Verify appointment belongs to user
            if ($appointment['user_id'] != $auth['id']) {
                return $this->error('Unauthorized', 403);
            }

            // Check if already cancelled
            if ($appointment['appointment_status'] === 'cancelled') {
                return $this->error('Appointment already cancelled', 400);
            }

            // Check if appointment is in the past
            $appointmentDateTime = new \DateTime($appointment['appointment_date'] . ' ' . $appointment['start_time']);
            $now = new \DateTime();
            
            if ($appointmentDateTime < $now) {
                return $this->error('Cannot cancel past appointments', 400);
            }

            // Cancel the appointment
            $success = $this->appointmentModel->cancelAppointment((int)$appointmentId);

            if ($success) {
                // Log user activity
                $this->logService->logUserActivity(
                    (string)$auth['id'],
                    'APPOINTMENT_CANCELLED',
                    ['appointment_id' => $appointmentId],
                    $this->getClientIp(),
                    $this->getUserAgent()
                );

                return $this->success(['message' => 'Appointment cancelled successfully']);
            }

            return $this->error('Failed to cancel appointment', 500);

        } catch (\Exception $e) {
            error_log("Cancel appointment error: " . $e->getMessage());
            return $this->error('Failed to cancel appointment', 500);
        }
    }

    /**
     * Get appointment statistics for user
     * GET /appointments/stats
     */
    public function getStats(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);
        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $user = $this->userModel->getUserWithAppointmentCount((int)$auth['id']);

            if (!$user) {
                return $this->error('User not found', 404);
            }

            return $this->success([
                'stats' => [
                    'total' => (int)$user['total_appointments'],
                    'completed' => (int)$user['completed_appointments'],
                    'upcoming' => (int)$user['upcoming_appointments']
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Get appointment stats error: " . $e->getMessage());
            return $this->error('Failed to load statistics', 500);
        }
    }

    // =============================================
    // ADMIN ENDPOINTS (Require Admin Authentication)
    // =============================================

    /**
     * Get all appointments with filters (Admin)
     * GET /admin/appointments
     */
    public function adminList(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $page = (int) ($data['page'] ?? 1);
            $limit = (int) ($data['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            $filters = [
                'status' => $data['status'] ?? '',
                'center_id' => $data['centerId'] ?? '',
                'counter_id' => $data['counterId'] ?? '',
                'service_id' => $data['serviceId'] ?? '',
                'user_id' => $data['userId'] ?? '',
                'date_from' => $data['dateFrom'] ?? '',
                'date_to' => $data['dateTo'] ?? '',
                'search' => $data['search'] ?? ''
            ];

            $appointments = $this->appointmentModel->getAppointmentsWithFilters($filters, $limit, $offset);

            // Get total count for pagination
            $countSql = "SELECT COUNT(*) as count FROM appointment a
                        INNER JOIN counter c ON a.at_counter = c.counter_id
                        INNER JOIN verification_center vc ON c.center_id = vc.center_id
                        WHERE 1=1";
            $countParams = [];

            if (!empty($filters['status'])) {
                $countSql .= " AND a.appointment_status = ?";
                $countParams[] = $filters['status'];
            }
            if (!empty($filters['center_id'])) {
                $countSql .= " AND vc.center_id = ?";
                $countParams[] = $filters['center_id'];
            }

            $countStmt = $this->appointmentModel->query($countSql, $countParams);
            $total = $countStmt->fetch()['count'];

            return $this->success([
                'appointments' => $appointments,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => ceil($total / $limit)
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Admin get appointments error: " . $e->getMessage());
            return $this->error('Failed to load appointments', 500);
        }
    }

    /**
     * Get appointment by ID (Admin)
     * GET /admin/appointments/{id}
     */
    public function adminGetAppointment(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $appointmentId = $params['id'] ?? '';
        if (empty($appointmentId)) {
            return $this->error('Appointment ID is required', 400);
        }

        try {
            $appointment = $this->appointmentModel->getAppointmentDetails((int)$appointmentId);

            if (!$appointment) {
                return $this->error('Appointment not found', 404);
            }

            return $this->success(['appointment' => $appointment]);

        } catch (\Exception $e) {
            error_log("Admin get appointment error: " . $e->getMessage());
            return $this->error('Failed to load appointment', 500);
        }
    }

    /**
     * Update appointment status (Admin)
     * PUT /admin/appointments/{id}/status
     */
    public function updateStatus(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $appointmentId = $params['id'] ?? '';
        if (empty($appointmentId)) {
            return $this->error('Appointment ID is required', 400);
        }

        $data = $this->sanitize($data);

        $missing = $this->validateRequired($data, ['status']);
        if (!empty($missing)) {
            return $this->error('Status is required', 400);
        }

        try {
            $validStatuses = ['scheduled', 'completed', 'cancelled', 'no-show'];
            if (!in_array($data['status'], $validStatuses)) {
                return $this->error('Invalid status. Must be one of: ' . implode(', ', $validStatuses), 400);
            }

            $success = $this->appointmentModel->updateStatus((int)$appointmentId, $data['status']);

            if ($success) {
                // Log admin activity
                $this->logService->logAdminActivity(
                    $admin['id'],
                    'APPOINTMENT_STATUS_UPDATE',
                    ['appointment_id' => $appointmentId, 'new_status' => $data['status']],
                    $this->getClientIp(),
                    $this->getUserAgent(),
                    'appointment',
                    $appointmentId
                );

                return $this->success(['message' => 'Appointment status updated successfully']);
            }

            return $this->error('Failed to update appointment status', 500);

        } catch (\Exception $e) {
            error_log("Update appointment status error: " . $e->getMessage());
            return $this->error('Failed to update appointment status', 500);
        }
    }

    /**
     * Get appointment statistics (Admin)
     * GET /admin/appointments/stats
     */
    public function adminGetStats(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $centerId = !empty($data['centerId']) ? (int)$data['centerId'] : null;
            $dateFrom = $data['dateFrom'] ?? null;
            $dateTo = $data['dateTo'] ?? null;

            $stats = $this->appointmentModel->getStats($centerId, $dateFrom, $dateTo);

            return $this->success(['stats' => $stats]);

        } catch (\Exception $e) {
            error_log("Admin get stats error: " . $e->getMessage());
            return $this->error('Failed to load statistics', 500);
        }
    }

    /**
     * Get upcoming appointments (Admin)
     * GET /admin/appointments/upcoming
     */
    public function getUpcoming(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $centerId = !empty($data['centerId']) ? (int)$data['centerId'] : null;
            $limit = (int) ($data['limit'] ?? 50);

            $appointments = $this->appointmentModel->getUpcomingAppointments($centerId, $limit);

            return $this->success([
                'appointments' => $appointments,
                'count' => count($appointments)
            ]);

        } catch (\Exception $e) {
            error_log("Get upcoming appointments error: " . $e->getMessage());
            return $this->error('Failed to load upcoming appointments', 500);
        }
    }

    /**
     * Get appointments by date range (Admin)
     * GET /admin/appointments/date-range
     */
    public function getByDateRange(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $missing = $this->validateRequired($data, ['dateFrom', 'dateTo']);
        if (!empty($missing)) {
            return $this->error('Date range is required', 400);
        }

        try {
            $centerId = !empty($data['centerId']) ? (int)$data['centerId'] : null;

            $appointments = $this->appointmentModel->getAppointmentsByDateRange(
                $data['dateFrom'],
                $data['dateTo'],
                $centerId
            );

            return $this->success([
                'appointments' => $appointments,
                'count' => count($appointments),
                'dateRange' => [
                    'from' => $data['dateFrom'],
                    'to' => $data['dateTo']
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Get appointments by date range error: " . $e->getMessage());
            return $this->error('Failed to load appointments', 500);
        }
    }
}