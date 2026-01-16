<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\Application;
use IndianConsular\Models\MiscellaneousApplication;
use IndianConsular\Models\ApplicationFile;
use IndianConsular\Models\Service;
use IndianConsular\Models\User;
use IndianConsular\Services\NotificationService;

class ApplicationController extends BaseController
{
    private Application $applicationModel;
    private MiscellaneousApplication $miscApplicationModel;
    private ApplicationFile $fileModel;
    private Service $serviceModel;
    private User $userModel;
    private NotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->applicationModel = new Application();
        $this->miscApplicationModel = new MiscellaneousApplication();
        $this->fileModel = new ApplicationFile();
        $this->serviceModel = new Service();
        $this->userModel = new User();
        $this->notificationService = new NotificationService();
    }

    /**
     * Submit new application
     */
    public function submit(array $data, array $params): array
    {

        // Log the request data to the terminal
        error_log("Received request data: " . json_encode($data));
        error_log("Received request params: " . json_encode($params));

        $data = $this->sanitize($data);

        // Validate required fields
        $missing = $this->validateRequired($data, [
            'serviceType', 'applicantInfo'
        ]);

        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            // Verify service exists
            $service = $this->serviceModel->findByServiceId($data['serviceType']);
            if (!$service || !$service['is_active']) {
                return $this->error('Invalid or inactive service', 400);
            }

            // Generate application ID
            $applicationId = $this->generateId('ICS');

            // Prepare application data
            $applicationData = [
                'application_id' => $applicationId,
                'user_id' => $data['userId'] ?? null,
                'service_id' => $data['serviceType'],
                'applicant_info' => json_encode($data['applicantInfo']),
                'form_data' => json_encode($data['formData'] ?? []),
                'status' => 'submitted',
                'priority' => $data['priority'] ?? 'normal',
                'submitted_at' => date('Y-m-d H:i:s'),
                'last_updated' => date('Y-m-d H:i:s')
            ];

            // Insert application
            $id = $this->applicationModel->insert($applicationData);

            // Send confirmation email if email provided
            if (!empty($data['applicantInfo']['email'])) {
                $this->notificationService->sendApplicationSubmitted(
                    $applicationId,
                    $data['applicantInfo']['email'],
                    $data['applicantInfo']['firstName'] ?? 'Applicant',
                    $service['title']
                );
            }

            return $this->success([
                'applicationId' => $applicationId,
                'status' => 'submitted',
                'message' => 'Application submitted successfully',
                'estimatedProcessingTime' => $service['processing_time']
            ], 201);

        } catch (\Exception $e) {
            error_log("Application submission error: " . $e->getMessage());
            return $this->error('Failed to submit application', 500);
        }
    }

    /**
     * Track application status
     */
    public function track(array $data, array $params): array
    {
        $applicationId = $params['id'] ?? '';

        if (empty($applicationId)) {
            return $this->error('Application ID is required', 400);
        }

        try {
            $application = $this->applicationModel->findByApplicationId($applicationId);

            if (!$application) {
                return $this->error('Application not found', 404);
            }

            // Get service details
            $service = $this->serviceModel->findByServiceId($application['service_id']);

            return $this->success([
                'application' => [
                    'applicationId' => $application['application_id'],
                    'serviceType' => $service['title'] ?? $application['service_id'],
                    'status' => $application['status'],
                    'submittedAt' => $application['submitted_at'],
                    'lastUpdated' => $application['last_updated'],
                    'expectedCompletionDate' => $application['expected_completion_date'],
                    'processingNotes' => $application['processing_notes']
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Application tracking error: " . $e->getMessage());
            return $this->error('Failed to track application', 500);
        }
    }

    // =============================================
    // MISCELLANEOUS APPLICATIONS - USER ENDPOINTS
    // =============================================

    /**
     * Submit miscellaneous application (User)
     * POST /applications/miscellaneous/submit
     */
    public function submitMiscellaneous(array $data, array $params): array
    {
        $user = $this->requireUserAuth($data);
        if (!$user) {
            return $this->error('Authentication required', 401);
        }

        $data = $this->sanitize($data);

        // Validate required fields
        $missing = $this->validateRequired($data, [
            'service_id',
            'full_name',
            'nationality',
            'email_address',
            'phone_number'
        ]);

        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            // Verify service exists and is in Miscellaneous category
            $service = $this->serviceModel->findByServiceId((int)$data['service_id']);
            if (!$service || !$service['is_active']) {
                return $this->error('Invalid or inactive service', 400);
            }

            if ($service['category'] !== 'Miscellaneous') {
                return $this->error('This endpoint is only for Miscellaneous category services', 400);
            }

            // Generate application ID
            $applicationId = 'MISC' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));

            // Prepare form data as JSON (matching actual database structure)
            $formData = [
                'full_name' => $data['full_name'],
                'nationality' => $data['nationality'],
                'father_name' => $data['father_name'] ?? null,
                'father_nationality' => $data['father_nationality'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'mother_nationality' => $data['mother_nationality'] ?? null,
                'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                'place_of_birth' => $data['place_of_birth'] ?? null,
                'country_of_birth' => $data['country_of_birth'] ?? null,
                'spouse_name' => $data['spouse_name'] ?? null,
                'spouse_nationality' => $data['spouse_nationality'] ?? null,
                'present_address_sa' => $data['present_address_sa'] ?? null,
                'phone_number' => $data['phone_number'],
                'email_address' => $data['email_address'],
                'profession' => $data['profession'] ?? null,
                'employer_details' => $data['employer_details'] ?? null,
                'visa_immigration_status' => $data['visa_immigration_status'] ?? null,
                'permanent_address_india' => $data['permanent_address_india'] ?? null,
                'passport_number' => $data['passport_number'] ?? null,
                'passport_validity' => !empty($data['passport_validity']) ? $data['passport_validity'] : null,
                'passport_date_of_issue' => !empty($data['passport_date_of_issue']) ? $data['passport_date_of_issue'] : null,
                'passport_place_of_issue' => $data['passport_place_of_issue'] ?? null,
                'is_registered_with_mission' => isset($data['is_registered_with_mission']) ? (bool)$data['is_registered_with_mission'] : false,
                'registration_number' => $data['registration_number'] ?? null,
                'registration_date' => !empty($data['registration_date']) ? $data['registration_date'] : null,
            ];

            // Prepare application data (matching actual database structure)
            $applicationData = [
                'application_id' => $applicationId,
                'user_id' => $user['id'],
                'service_id' => (int)$data['service_id'],
                'form_data' => json_encode($formData, JSON_UNESCAPED_UNICODE),
                'status' => 'submitted',
                'submitted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert application
            $this->miscApplicationModel->insert($applicationData);

            // Handle file uploads
            $uploadedFiles = [];
            if (!empty($data['_files'])) {
                $uploadedFiles = $this->handleFileUploads($applicationId, (int)$user['id'], $data['_files']);
            }

            // Log user activity
            $this->logService->logUserActivity(
                (string)$user['id'],
                'MISCELLANEOUS_APPLICATION_SUBMITTED',
                ['application_id' => $applicationId, 'service_id' => $data['service_id']],
                $this->getClientIp(),
                $this->getUserAgent(),
                'miscellaneous_application',
                $applicationId
            );

            return $this->success([
                'application_id' => $applicationId,
                'status' => 'submitted',
                'message' => 'Application submitted successfully',
                'files_uploaded' => count($uploadedFiles),
                'service_title' => $service['title']
            ], 201);

        } catch (\Exception $e) {
            error_log("Miscellaneous application submission error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->error('Failed to submit application: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle file uploads for application
     */
    private function handleFileUploads(string $applicationId, int $userId, array $files): array
    {
        $uploadedFiles = [];
        $uploadDir = $_ENV['UPLOAD_DIR'] ?? __DIR__ . '/../../public/uploads/applications/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($files as $fieldName => $fileData) {
            // Handle single file or array of files
            $filesToProcess = isset($fileData['name']) && is_array($fileData['name']) 
                ? $this->normalizeFileArray($fileData)
                : [$fileData];

            foreach ($filesToProcess as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }

                // Validate file
                $maxSize = 10 * 1024 * 1024; // 10MB
                if ($file['size'] > $maxSize) {
                    continue;
                }

                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($file['type'], $allowedTypes)) {
                    continue;
                }

                // Generate unique filename
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('app_', true) . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Generate file ID
                    $fileId = 'FILE' . strtoupper(bin2hex(random_bytes(6)));

                    // Determine document type from field name
                    $documentType = $this->determineDocumentType($fieldName);

                    // Save file metadata
                    $fileData = [
                        'file_id' => $fileId,
                        'application_id' => $applicationId,
                        'file_name' => $fileName,
                        'original_name' => $file['name'],
                        'file_path' => '/uploads/applications/' . $fileName,
                        'file_type' => $fileExtension,
                        'file_size' => $file['size'],
                        'mime_type' => $file['type'],
                        'document_type' => $documentType,
                        'uploaded_by' => $userId,
                        'uploaded_at' => date('Y-m-d H:i:s')
                    ];

                    $this->fileModel->insert($fileData);
                    $uploadedFiles[] = $fileId;
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * Normalize file array for multiple file uploads
     */
    private function normalizeFileArray(array $fileData): array
    {
        $normalized = [];
        $count = count($fileData['name']);

        for ($i = 0; $i < $count; $i++) {
            $normalized[] = [
                'name' => $fileData['name'][$i],
                'type' => $fileData['type'][$i],
                'tmp_name' => $fileData['tmp_name'][$i],
                'error' => $fileData['error'][$i],
                'size' => $fileData['size'][$i]
            ];
        }

        return $normalized;
    }

    /**
     * Determine document type from field name
     */
    private function determineDocumentType(string $fieldName): string
    {
        $fieldName = strtolower($fieldName);
        
        if (strpos($fieldName, 'passport') !== false) {
            return 'passport';
        } elseif (strpos($fieldName, 'birth') !== false) {
            return 'birth_certificate';
        } elseif (strpos($fieldName, 'photo') !== false) {
            return 'photo';
        } elseif (strpos($fieldName, 'id') !== false) {
            return 'id_proof';
        } elseif (strpos($fieldName, 'address') !== false) {
            return 'address_proof';
        } else {
            return 'other';
        }
    }

    // =============================================
    // MISCELLANEOUS APPLICATIONS - ADMIN ENDPOINTS
    // =============================================

    /**
     * List all miscellaneous applications (Admin)
     * GET /admin/applications/miscellaneous
     */
    public function adminListMiscellaneous(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        try {
            $page = (int) ($data['page'] ?? 1);
            $limit = (int) ($data['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            $sortBy = $data['sort_by'] ?? 'submitted_at';
            $sortOrder = $data['sort_order'] ?? 'DESC';

            // Build filters
            $filters = [];
            if (!empty($data['status'])) {
                $filters['status'] = $data['status'];
            }
            if (!empty($data['service_id'])) {
                $filters['service_id'] = (int)$data['service_id'];
            }
            if (!empty($data['user_id'])) {
                $filters['user_id'] = (int)$data['user_id'];
            }
            if (!empty($data['date_from'])) {
                $filters['date_from'] = $data['date_from'];
            }
            if (!empty($data['date_to'])) {
                $filters['date_to'] = $data['date_to'];
            }
            if (!empty($data['search'])) {
                $filters['search'] = $data['search'];
            }

            $applications = $this->miscApplicationModel->getApplicationsWithFilters($filters, $limit, $offset, $sortBy, $sortOrder);
            $total = $this->miscApplicationModel->countWithFilters($filters);

            // Parse form_data JSON for each application
            foreach ($applications as &$app) {
                if (!empty($app['form_data'])) {
                    $formData = json_decode($app['form_data'], true);
                    if ($formData) {
                        // Merge form_data fields into application object for easier access
                        $app = array_merge($app, $formData);
                    }
                }
            }
            unset($app);

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
            error_log("Admin list applications error: " . $e->getMessage());
            return $this->error('Failed to load applications', 500);
        }
    }

    /**
     * Get application details (Admin)
     * GET /admin/applications/miscellaneous/{id}
     */
    public function adminGetMiscellaneous(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $applicationId = $params['id'] ?? '';
        if (empty($applicationId)) {
            return $this->error('Application ID is required', 400);
        }

        try {
            $application = $this->miscApplicationModel->getApplicationWithDetails($applicationId);
            if (!$application) {
                return $this->error('Application not found', 404);
            }

            // Parse and merge form_data JSON
            if (!empty($application['form_data'])) {
                $formData = json_decode($application['form_data'], true);
                if ($formData) {
                    $application = array_merge($application, $formData);
                }
            }

            // Get files
            $files = $this->fileModel->getFilesByApplicationId($applicationId);

            return $this->success([
                'application' => $application,
                'files' => $files
            ]);
        } catch (\Exception $e) {
            error_log("Admin get application error: " . $e->getMessage());
            return $this->error('Failed to load application', 500);
        }
    }

    /**
     * Update application (Admin)
     * PUT /admin/applications/miscellaneous/{id}
     */
    public function adminUpdateMiscellaneous(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $applicationId = $params['id'] ?? '';
        if (empty($applicationId)) {
            return $this->error('Application ID is required', 400);
        }

        try {
            $application = $this->miscApplicationModel->findByApplicationId($applicationId);
            if (!$application) {
                return $this->error('Application not found', 404);
            }

            $data = $this->sanitize($data);

            // Get current form_data
            $currentFormData = [];
            if (!empty($application['form_data'])) {
                $currentFormData = json_decode($application['form_data'], true) ?: [];
            }

            // Fields that go into form_data JSON
            $formDataFields = [
                'full_name', 'nationality', 'father_name', 'father_nationality',
                'mother_name', 'mother_nationality', 'date_of_birth', 'place_of_birth',
                'country_of_birth', 'spouse_name', 'spouse_nationality',
                'present_address_sa', 'phone_number', 'email_address', 'profession',
                'employer_details', 'visa_immigration_status', 'permanent_address_india',
                'passport_number', 'passport_validity', 'passport_date_of_issue',
                'passport_place_of_issue', 'is_registered_with_mission',
                'registration_number', 'registration_date'
            ];

            // Fields that are direct columns (not in form_data)
            $directFields = ['status', 'admin_notes'];

            // Prepare update data
            $updateData = [];
            $formDataUpdated = false;

            // Update form_data fields
            foreach ($formDataFields as $field) {
                if (isset($data[$field])) {
                    if (in_array($field, ['date_of_birth', 'passport_validity', 'passport_date_of_issue', 'registration_date'])) {
                        $currentFormData[$field] = !empty($data[$field]) ? $data[$field] : null;
                    } elseif ($field === 'is_registered_with_mission') {
                        $currentFormData[$field] = (bool)$data[$field];
                    } else {
                        $currentFormData[$field] = $data[$field];
                    }
                    $formDataUpdated = true;
                }
            }

            // Update direct fields
            foreach ($directFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            // If form_data was updated, encode and add to updateData
            if ($formDataUpdated) {
                $updateData['form_data'] = json_encode($currentFormData, JSON_UNESCAPED_UNICODE);
            }

            if (empty($updateData)) {
                return $this->error('No valid fields to update', 400);
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $this->miscApplicationModel->updateBy('application_id', $applicationId, $updateData);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'UPDATE_MISCELLANEOUS_APPLICATION',
                ['application_id' => $applicationId, 'updates' => array_keys($updateData)],
                $this->getClientIp(),
                $this->getUserAgent(),
                'miscellaneous_application',
                $applicationId
            );

            return $this->success(['message' => 'Application updated successfully']);
        } catch (\Exception $e) {
            error_log("Admin update application error: " . $e->getMessage());
            return $this->error('Failed to update application', 500);
        }
    }

    /**
     * Get application file (Admin)
     * GET /admin/applications/miscellaneous/{id}/files/{fileId}
     */
    public function adminGetFile(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $fileId = $params['fileId'] ?? '';
        if (empty($fileId)) {
            return $this->error('File ID is required', 400);
        }

        try {
            $file = $this->fileModel->findByFileId($fileId);
            if (!$file) {
                return $this->error('File not found', 404);
            }

            return $this->success(['file' => $file]);
        } catch (\Exception $e) {
            error_log("Admin get file error: " . $e->getMessage());
            return $this->error('Failed to load file', 500);
        }
    }

    /**
     * Download application file (Admin)
     * GET /admin/applications/miscellaneous/{id}/files/{fileId}/download
     */
    public function adminDownloadFile(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $fileId = $params['fileId'] ?? '';
        if (empty($fileId)) {
            return $this->error('File ID is required', 400);
        }

        try {
            $file = $this->fileModel->findByFileId($fileId);
            if (!$file) {
                return $this->error('File not found', 404);
            }

            $filePath = __DIR__ . '/../../public' . $file['file_path'];
            if (!file_exists($filePath)) {
                return $this->error('File not found on server', 404);
            }

            // Set headers for file download
            header('Content-Type: ' . $file['mime_type']);
            header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
            header('Content-Length: ' . $file['file_size']);
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            readfile($filePath);
            exit;
        } catch (\Exception $e) {
            error_log("Admin download file error: " . $e->getMessage());
            return $this->error('Failed to download file', 500);
        }
    }

    /**
     * Get application statistics (Admin)
     * GET /admin/applications/miscellaneous/stats
     */
    public function adminGetStats(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        try {
            $stats = $this->miscApplicationModel->getStats();
            return $this->success(['stats' => $stats]);
        } catch (\Exception $e) {
            error_log("Admin get stats error: " . $e->getMessage());
            return $this->error('Failed to load statistics', 500);
        }
    }
}
