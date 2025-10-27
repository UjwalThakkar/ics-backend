<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\Application;
use IndianConsular\Models\Appointment;
use IndianConsular\Models\AdminUser;
use IndianConsular\Models\Service;
use IndianConsular\Models\Notification;
use IndianConsular\Services\NotificationService;

class AdminController extends BaseController
{
    private Application $applicationModel;
    private Appointment $appointmentModel;
    private AdminUser $adminUserModel;
    private Service $serviceModel;
    private Notification $notificationModel;
    private NotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->applicationModel = new Application();
        $this->appointmentModel = new Appointment();
        $this->adminUserModel = new AdminUser();
        $this->serviceModel = new Service();
        $this->notificationModel = new Notification();
        $this->notificationService = new NotificationService();
    }

    /**
     * Get dashboard statistics
     */
    public function stats(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        try {
            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['admin_id'],
                'VIEW_DASHBOARD',
                [],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            $stats = [
                'applications' => $this->applicationModel->getStats(),
                'appointments' => $this->appointmentModel->getStats(),
                'notifications' => $this->notificationModel->getStats()
            ];

            return $this->success(['stats' => $stats]);

        } catch (\Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return $this->error('Failed to load dashboard stats', 500);
        }
    }

    /**
     * Get applications with pagination
     */
    public function getApplications(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        try {
            $page = (int) ($data['page'] ?? 1);
            $limit = (int) ($data['limit'] ?? 10);
            $offset = ($page - 1) * $limit;

            $filters = [
                'status' => $data['status'] ?? '',
                'service_id' => $data['service_id'] ?? '',
                'search' => $data['search'] ?? ''
            ];

            $applications = $this->applicationModel->getApplicationsWithFilters($filters, $limit, $offset);
            $total = $this->applicationModel->count($filters);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['admin_id'],
                'VIEW_APPLICATIONS',
                ['page' => $page, 'limit' => $limit, 'filters' => $filters],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->success([
                'applications' => $applications,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => ceil($total / $limit)
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Get applications error: " . $e->getMessage());
            return $this->error('Failed to load applications', 500);
        }
    }

    /**
     * Update application
     */
    public function updateApplication(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $applicationId = $params['id'] ?? '';
        if (empty($applicationId)) {
            return $this->error('Application ID is required', 400);
        }

        try {
            $application = $this->applicationModel->findByApplicationId($applicationId);
            if (!$application) {
                return $this->error('Application not found', 404);
            }

            $updateData = [];
            $allowedFields = ['status', 'assigned_officer', 'processing_notes', 'expected_completion_date'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                return $this->error('No valid fields to update', 400);
            }

            $success = $this->applicationModel->updateBy('application_id', $applicationId, $updateData);

            if ($success) {
                // Send status update notification if status changed
                if (isset($updateData['status'])) {
                    $applicantInfo = json_decode($application['applicant_info'], true);
                    if (!empty($applicantInfo['email'])) {
                        $this->notificationService->sendApplicationStatusUpdate(
                            $applicationId,
                            $applicantInfo['email'],
                            $applicantInfo['firstName'] ?? 'Applicant',
                            $updateData['status']
                        );
                    }
                }

                // Log admin activity
                $this->logService->logAdminActivity(
                    $admin['admin_id'],
                    'UPDATE_APPLICATION',
                    ['application_id' => $applicationId, 'updates' => $updateData],
                    $this->getClientIp(),
                    $this->getUserAgent(),
                    'application',
                    $applicationId
                );

                return $this->success(['message' => 'Application updated successfully']);
            }

            return $this->error('Failed to update application', 500);

        } catch (\Exception $e) {
            error_log("Update application error: " . $e->getMessage());
            return $this->error('Failed to update application', 500);
        }
    }

    /**
     * Delete application
     */
    public function deleteApplication(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $applicationId = $params['id'] ?? '';
        if (empty($applicationId)) {
            return $this->error('Application ID is required', 400);
        }

        try {
            $application = $this->applicationModel->findByApplicationId($applicationId);
            if (!$application) {
                return $this->error('Application not found', 404);
            }

            $success = $this->applicationModel->deleteBy('application_id', $applicationId);

            if ($success) {
                // Log admin activity
                $this->logService->logAdminActivity(
                    $admin['admin_id'],
                    'DELETE_APPLICATION',
                    ['application_id' => $applicationId],
                    $this->getClientIp(),
                    $this->getUserAgent(),
                    'application',
                    $applicationId
                );

                return $this->success(['message' => 'Application deleted successfully']);
            }

            return $this->error('Failed to delete application', 500);

        } catch (\Exception $e) {
            error_log("Delete application error: " . $e->getMessage());
            return $this->error('Failed to delete application', 500);
        }
    }

    /**
     * Create appointment
     */
    public function createAppointment(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $data = $this->sanitize($data);

        $missing = $this->validateRequired($data, [
            'clientName', 'serviceType', 'appointmentDate', 'appointmentTime'
        ]);

        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            $appointmentId = $this->generateId('APT');

            $appointmentData = [
                'appointment_id' => $appointmentId,
                'application_id' => $data['applicationId'] ?? null,
                'client_name' => $data['clientName'],
                'client_email' => $data['clientEmail'] ?? null,
                'client_phone' => $data['clientPhone'] ?? null,
                'service_type' => $data['serviceType'],
                'appointment_date' => $data['appointmentDate'],
                'appointment_time' => $data['appointmentTime'],
                'duration_minutes' => $data['duration'] ?? 30,
                'status' => 'confirmed',
                'notes' => $data['notes'] ?? '',
                'assigned_officer' => $data['assignedOfficer'] ?? $admin['username'],
                'created_by' => $admin['admin_id'],
                'booking_type' => 'manual',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $id = $this->appointmentModel->insert($appointmentData);

            // Send confirmation email
            if (!empty($data['clientEmail'])) {
                $this->notificationService->sendAppointmentConfirmed(
                    $appointmentId,
                    $data['clientEmail'],
                    $data['clientName'],
                    $data['appointmentDate'],
                    $data['appointmentTime'],
                    $data['serviceType']
                );
            }

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['admin_id'],
                'CREATE_APPOINTMENT',
                ['appointment_id' => $appointmentId, 'booking_type' => 'manual'],
                $this->getClientIp(),
                $this->getUserAgent(),
                'appointment',
                $appointmentId
            );

            return $this->success([
                'appointmentId' => $appointmentId,
                'message' => 'Appointment created successfully'
            ], 201);

        } catch (\Exception $e) {
            error_log("Create appointment error: " . $e->getMessage());
            return $this->error('Failed to create appointment', 500);
        }
    }

    /**
     * Get appointments
     */
    public function getAppointments(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        try {
            $page = (int) ($data['page'] ?? 1);
            $limit = (int) ($data['limit'] ?? 10);
            $offset = ($page - 1) * $limit;

            $filters = [
                'status' => $data['status'] ?? '',
                'date' => $data['date'] ?? '',
                'service_type' => $data['serviceType'] ?? ''
            ];

            $appointments = $this->appointmentModel->getAppointmentsWithFilters($filters, $limit, $offset);
            $total = $this->appointmentModel->count($filters);

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
            error_log("Get appointments error: " . $e->getMessage());
            return $this->error('Failed to load appointments', 500);
        }
    }

    /**
     * Create full backup
     */
    public function backup(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        try {
            $backupData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'created_by' => $admin['admin_id'],
                'version' => '1.0',
                'data' => [
                    'applications' => $this->applicationModel->findAll(),
                    'appointments' => $this->appointmentModel->findAll(),
                    'services' => $this->serviceModel->findAll(),
                    'admin_users' => $this->adminUserModel->findAll(),
                    'notifications' => $this->notificationModel->findAll()
                ]
            ];

            // Log backup activity
            $this->logService->logAdminActivity(
                $admin['admin_id'],
                'CREATE_BACKUP',
                ['backup_size' => count($backupData['data'], COUNT_RECURSIVE)],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->success([
                'backup' => $backupData,
                'message' => 'Backup created successfully'
            ]);

        } catch (\Exception $e) {
            error_log("Backup error: " . $e->getMessage());
            return $this->error('Failed to create backup', 500);
        }
    }

    /**
     * System status
     */
    public function systemStatus(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        try {
            $status = [
                'database' => 'connected',
                'php_version' => PHP_VERSION,
                'memory_usage' => memory_get_usage(true),
                'disk_space' => disk_free_space('.'),
                'uptime' => time(),
                'timestamp' => date('Y-m-d H:i:s')
            ];

            return $this->success(['status' => $status]);

        } catch (\Exception $e) {
            error_log("System status error: " . $e->getMessage());
            return $this->error('Failed to get system status', 500);
        }
    }
}
