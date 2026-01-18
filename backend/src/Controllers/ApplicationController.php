<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\Application;
use IndianConsular\Models\MiscellaneousApplication;
use IndianConsular\Models\ApplicationFile;
use IndianConsular\Models\Service;
use IndianConsular\Models\User;
use IndianConsular\Services\NotificationService;
use IndianConsular\Services\PdfFormService;

class ApplicationController extends BaseController
{
    private Application $applicationModel;
    private MiscellaneousApplication $miscApplicationModel;
    private ApplicationFile $fileModel;
    private Service $serviceModel;
    private User $userModel;
    private NotificationService $notificationService;
    private PdfFormService $pdfService;

    public function __construct()
    {
        parent::__construct();
        $this->applicationModel = new Application();
        $this->miscApplicationModel = new MiscellaneousApplication();
        $this->fileModel = new ApplicationFile();
        $this->serviceModel = new Service();
        $this->userModel = new User();
        $this->notificationService = new NotificationService();
        $this->pdfService = new PdfFormService();
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

            // Generate filled PDF form
            $filledPdfPath = null;
            try {
                $filledPdfPath = $this->pdfService->fillPdfForm($formData, $applicationId);
                if ($filledPdfPath) {
                    // Update application with filled PDF path
                    $this->miscApplicationModel->updateBy('application_id', $applicationId, [
                        'filled_pdf_path' => $filledPdfPath,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the submission
                error_log("PDF generation failed for application {$applicationId}: " . $e->getMessage());
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
                'service_title' => $service['title'],
                'filled_pdf_path' => $filledPdfPath,
                'filled_pdf_url' => $filledPdfPath ? $this->getFilledPdfUrl($filledPdfPath) : null
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
            // IMPORTANT: Preserve direct columns (like status) and don't let form_data overwrite them
            foreach ($applications as &$app) {
                if (!empty($app['form_data'])) {
                    $formData = json_decode($app['form_data'], true);
                    if ($formData) {
                        // Preserve direct columns before merging
                        $directColumns = ['id', 'application_id', 'user_id', 'service_id', 'status', 'submitted_at', 'updated_at', 'admin_notes', 'filled_pdf_path', 'service_title', 'service_category', 'user_first_name', 'user_last_name', 'user_email', 'file_count'];
                        $preservedValues = [];
                        foreach ($directColumns as $col) {
                            if (isset($app[$col])) {
                                $preservedValues[$col] = $app[$col];
                            }
                        }
                        // Merge form_data fields into application object for easier access
                        $app = array_merge($app, $formData);
                        // Restore preserved values (form_data should not overwrite direct columns)
                        foreach ($preservedValues as $col => $value) {
                            $app[$col] = $value;
                        }
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
            // IMPORTANT: Preserve direct columns (like status) and don't let form_data overwrite them
            if (!empty($application['form_data'])) {
                $formData = json_decode($application['form_data'], true);
                if ($formData) {
                    // Merge form_data but preserve direct columns
                    $directColumns = ['id', 'application_id', 'user_id', 'service_id', 'status', 'submitted_at', 'updated_at', 'admin_notes', 'filled_pdf_path'];
                    $preservedValues = [];
                    foreach ($directColumns as $col) {
                        if (isset($application[$col])) {
                            $preservedValues[$col] = $application[$col];
                        }
                    }
                    $application = array_merge($application, $formData);
                    // Restore preserved values (form_data should not overwrite direct columns)
                    foreach ($preservedValues as $col => $value) {
                        $application[$col] = $value;
                    }
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

            // Debug logging for status updates and validation
            $oldStatus = $application['status'] ?? null;
            $newStatus = null;
            
            if (isset($data['status'])) {
                $newStatus = $data['status'];
                error_log("Admin updating application {$applicationId} status from '{$oldStatus}' to '{$newStatus}'");
                
                // Validate status matches database enum: 'submitted','in-progress','approved','rejected','completed'
                $validStatuses = ['submitted', 'in-progress', 'approved', 'rejected', 'completed'];
                if (!in_array($newStatus, $validStatuses)) {
                    error_log("WARNING: Invalid status '{$newStatus}' for application {$applicationId}. Valid statuses: " . implode(', ', $validStatuses));
                    // Don't reject, but log the warning - the database enum will enforce it anyway
                }
                
                // If status is being changed to 'rejected', admin_notes is mandatory
                if ($newStatus === 'rejected') {
                    $adminNotes = $data['admin_notes'] ?? $updateData['admin_notes'] ?? '';
                    if (empty(trim($adminNotes))) {
                        return $this->error('Admin notes are required when rejecting an application', 400);
                    }
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

            // Perform the update
            $rowsAffected = $this->miscApplicationModel->updateBy('application_id', $applicationId, $updateData);
            
            // Verify the update
            $updatedApplication = $this->miscApplicationModel->findByApplicationId($applicationId);
            if ($updatedApplication) {
                error_log("Application {$applicationId} status after update: '{$updatedApplication['status']}'");
            } else {
                error_log("WARNING: Could not verify update for application {$applicationId}");
            }

            // Send email notification if status changed to approved or rejected
            if ($newStatus && $newStatus !== $oldStatus && in_array($newStatus, ['approved', 'rejected'])) {
                try {
                    // Get user email from form_data or user table
                    $userEmail = null;
                    $applicantName = null;
                    
                    // Try to get email from form_data
                    if (!empty($currentFormData['email_address'])) {
                        $userEmail = $currentFormData['email_address'];
                    }
                    
                    // Try to get name from form_data
                    if (!empty($currentFormData['full_name'])) {
                        $applicantName = $currentFormData['full_name'];
                    }
                    
                    // If no email in form_data, try to get from user table
                    if (empty($userEmail) && !empty($application['user_id'])) {
                        $user = $this->userModel->find((int)$application['user_id']);
                        if ($user && !empty($user['email'])) {
                            $userEmail = $user['email'];
                        }
                        if (empty($applicantName) && $user) {
                            $applicantName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                        }
                    }
                    
                    // Send email if we have an email address
                    if (!empty($userEmail)) {
                        if ($newStatus === 'approved') {
                            $this->notificationService->sendApplicationApproved(
                                $applicationId,
                                $userEmail,
                                $applicantName ?: 'Applicant'
                            );
                        } elseif ($newStatus === 'rejected') {
                            // Get admin notes from updated application (after the update)
                            $adminNotes = $updatedApplication['admin_notes'] ?? $updateData['admin_notes'] ?? $application['admin_notes'] ?? '';
                            $this->notificationService->sendApplicationRejected(
                                $applicationId,
                                $userEmail,
                                $applicantName ?: 'Applicant',
                                $adminNotes
                            );
                        }
                    } else {
                        error_log("Cannot send status email for application {$applicationId}: No email address found");
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the update
                    error_log("Failed to send status email for application {$applicationId}: " . $e->getMessage());
                }
            }

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
     * Download filled PDF form
     * GET /applications/miscellaneous/{applicationId}/filled-pdf
     * 
     * Note: This method sends file directly and exits, bypassing normal JSON response
     */
    public function downloadFilledPdf(array $data, array $params): ?array
    {
        $user = $this->requireUserAuth($data);
        if (!$user) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }

        $applicationId = $params['applicationId'] ?? $params['id'] ?? '';
        if (empty($applicationId)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Application ID is required']);
            exit;
        }

        try {
            $application = $this->miscApplicationModel->findByApplicationId($applicationId);
            if (!$application) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Application not found']);
                exit;
            }

            // Verify user owns this application
            if ($application['user_id'] != $user['id']) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Unauthorized access to this application']);
                exit;
            }

            $filledPdfPath = $application['filled_pdf_path'] ?? null;
            if (empty($filledPdfPath)) {
                // Try to generate it if it doesn't exist
                $formData = json_decode($application['form_data'] ?? '{}', true);
                if ($formData) {
                    $filledPdfPath = $this->pdfService->fillPdfForm($formData, $applicationId);
                    if ($filledPdfPath) {
                        $this->miscApplicationModel->updateBy('application_id', $applicationId, [
                            'filled_pdf_path' => $filledPdfPath
                        ]);
                    }
                }
            }

            if (empty($filledPdfPath)) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Filled PDF not available. Please contact support.']);
                exit;
            }

            // Get full file path
            $fullPath = __DIR__ . '/../../public' . $filledPdfPath;
            if (!file_exists($fullPath)) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'PDF file not found']);
                exit;
            }

            // Send file download response
            // Clear any previous output
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filledPdfPath) . '"');
            header('Content-Length: ' . filesize($fullPath));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            readfile($fullPath);
            exit;

        } catch (\Exception $e) {
            error_log("Download filled PDF error: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to download PDF']);
            exit;
        }
    }

    /**
     * Download filled PDF form (Admin)
     * GET /admin/applications/miscellaneous/{id}/filled-pdf
     */
    public function adminDownloadFilledPdf(array $data, array $params): ?array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $applicationId = $params['id'] ?? '';
        if (empty($applicationId)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Application ID is required']);
            exit;
        }

        try {
            $application = $this->miscApplicationModel->findByApplicationId($applicationId);
            if (!$application) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Application not found']);
                exit;
            }

            $filledPdfPath = $application['filled_pdf_path'] ?? null;
            if (empty($filledPdfPath)) {
                // Try to generate it if it doesn't exist
                $formData = json_decode($application['form_data'] ?? '{}', true);
                if ($formData) {
                    $filledPdfPath = $this->pdfService->fillPdfForm($formData, $applicationId);
                    if ($filledPdfPath) {
                        $this->miscApplicationModel->updateBy('application_id', $applicationId, [
                            'filled_pdf_path' => $filledPdfPath
                        ]);
                    }
                }
            }

            if (empty($filledPdfPath)) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Filled PDF not available']);
                exit;
            }

            // Get full file path
            $fullPath = __DIR__ . '/../../public' . $filledPdfPath;
            if (!file_exists($fullPath)) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'PDF file not found']);
                exit;
            }

            // Send file download response
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filledPdfPath) . '"');
            header('Content-Length: ' . filesize($fullPath));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            readfile($fullPath);
            exit;

        } catch (\Exception $e) {
            error_log("Admin download filled PDF error: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to download PDF']);
            exit;
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
     * Upload file for application (Admin)
     * POST /admin/applications/miscellaneous/{id}/files
     */
    public function adminUploadFile(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $applicationId = $params['id'] ?? '';
        if (empty($applicationId)) {
            return $this->error('Application ID is required', 400);
        }

        // Verify application exists
        $application = $this->miscApplicationModel->findByApplicationId($applicationId);
        if (!$application) {
            return $this->error('Application not found', 404);
        }

        try {
            // Check if file was uploaded
            if (empty($_FILES) || !isset($_FILES['file'])) {
                return $this->error('No file uploaded', 400);
            }

            $file = $_FILES['file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return $this->error('File upload error', 400);
            }

            // Validate file
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($file['size'] > $maxSize) {
                return $this->error('File size exceeds 10MB limit', 400);
            }

            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file['type'], $allowedTypes)) {
                return $this->error('Invalid file type. Only PDF, JPG, and PNG are allowed', 400);
            }

            // Get document type from request (optional, defaults to 'admin_upload')
            $documentType = $data['document_type'] ?? 'admin_upload';
            $description = $data['description'] ?? null;

            // Setup upload directory
            $uploadDir = $_ENV['UPLOAD_DIR'] ?? __DIR__ . '/../../public/uploads/applications/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('admin_', true) . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return $this->error('Failed to save file', 500);
            }

            // Generate file ID
            $fileId = 'FILE' . strtoupper(bin2hex(random_bytes(6)));

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
                'uploaded_by' => null, // Admin uploads don't have a user_id
                'uploaded_at' => date('Y-m-d H:i:s')
            ];

            $this->fileModel->insert($fileData);

            // Log admin activity
            $this->logService->logAdminActivity(
                (string)$admin['id'],
                'ADMIN_UPLOADED_FILE',
                [
                    'application_id' => $applicationId,
                    'file_id' => $fileId,
                    'file_name' => $file['name'],
                    'document_type' => $documentType
                ],
                $this->getClientIp(),
                $this->getUserAgent(),
                'application_file',
                $fileId
            );

            return $this->success([
                'file_id' => $fileId,
                'file_name' => $file['name'],
                'message' => 'File uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            error_log("Admin upload file error: " . $e->getMessage());
            return $this->error('Failed to upload file: ' . $e->getMessage(), 500);
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

    /**
     * Track miscellaneous application (User)
     * GET /applications/miscellaneous/{applicationId}/track
     */
    public function trackMiscellaneous(array $data, array $params): array
    {
        // Debug logging
        error_log("trackMiscellaneous called with params: " . json_encode($params));
        error_log("Request data: " . json_encode($data));
        
        $applicationId = $params['applicationId'] ?? $params['id'] ?? '';
        
        if (empty($applicationId)) {
            error_log("trackMiscellaneous: Application ID is empty");
            return $this->error('Application ID is required', 400);
        }

        error_log("trackMiscellaneous: Looking for application ID: " . $applicationId);

        try {
            $application = $this->miscApplicationModel->findByApplicationId($applicationId);
            
            if (!$application) {
                error_log("trackMiscellaneous: Application not found for ID: " . $applicationId);
                return $this->error('Application not found', 404);
            }
            
            error_log("trackMiscellaneous: Application found: " . json_encode($application));

            // Get service details for processing time
            $service = $this->serviceModel->findByServiceId($application['service_id']);
            
            // Check if service exists (might have been deleted)
            if (!$service) {
                error_log("trackMiscellaneous: Service not found for service_id: " . $application['service_id']);
                // Still return application data, but with a generic service name
                $serviceTitle = 'Service Information Unavailable';
            } else {
                $serviceTitle = $service['title'] ?? 'Miscellaneous Service';
            }
            
            // Get the current status from database (ensure we have the latest)
            // Handle NULL status (database enum allows NULL, default to 'submitted')
            $currentStatus = $application['status'] ?? 'submitted';
            if (empty($currentStatus) || $currentStatus === 'NULL' || $currentStatus === null) {
                $currentStatus = 'submitted';
            }
            
            // Debug logging
            error_log("trackMiscellaneous: Application ID: {$applicationId}, Status: {$currentStatus}, Updated At: " . ($application['updated_at'] ?? 'N/A'));
            
            // Calculate expected completion date
            $submittedDate = new \DateTime($application['submitted_at']);
            $processingTime = $service['processing_time'] ?? null;
            $expectedCompletion = $this->calculateExpectedCompletion($submittedDate, $processingTime);
            
            // Generate processing timeline based on status
            $timeline = $this->generateProcessingTimeline($currentStatus, $application['submitted_at'], $application['updated_at']);

            return $this->success([
                'application_id' => $application['application_id'],
                'status' => $currentStatus, // Use the explicitly retrieved status
                'service_type' => $serviceTitle,
                'submitted_at' => $application['submitted_at'],
                'expected_completion' => $expectedCompletion,
                'timeline' => $timeline,
                'admin_notes' => $application['admin_notes'] ?? null // Include admin notes if any
            ]);

        } catch (\Exception $e) {
            error_log("Track miscellaneous application error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->error('Service temporarily unavailable. Please try again later or contact support.', 500);
        }
    }

    /**
     * Calculate expected completion date based on processing time
     */
    private function calculateExpectedCompletion(\DateTime $submittedDate, ?string $processingTime): string
    {
        // Default to 1 week if processing time not available
        $daysToAdd = 7;
        
        if ($processingTime) {
            // Try to extract days from processing time string (e.g., "5-7 working days", "2 weeks", "10 days")
            if (preg_match('/(\d+)\s*(?:-|\s*to\s*)?(\d+)?\s*(?:working\s*)?days?/i', $processingTime, $matches)) {
                $daysToAdd = isset($matches[2]) && !empty($matches[2]) 
                    ? (int)max($matches[1], $matches[2]) // Use the higher number if range
                    : (int)$matches[1];
            } elseif (preg_match('/(\d+)\s*weeks?/i', $processingTime, $matches)) {
                $daysToAdd = (int)$matches[1] * 7;
            } elseif (preg_match('/(\d+)\s*months?/i', $processingTime, $matches)) {
                $daysToAdd = (int)$matches[1] * 30;
            }
        }
        
        $expectedDate = clone $submittedDate;
        $expectedDate->modify("+{$daysToAdd} days");
        
        return $expectedDate->format('Y-m-d');
    }

    /**
     * Generate processing timeline based on status
     */
    private function generateProcessingTimeline(string $status, string $submittedAt, ?string $updatedAt): array
    {
        // Simplified timeline: Application Submitted, In Process, Accepted/Rejected, Completed
        $stages = [
            [
                'name' => 'Application Submitted',
                'completed' => true,
                'current' => false,
                'date' => $submittedAt
            ],
            [
                'name' => 'In Process',
                'completed' => false,
                'current' => false
            ],
            [
                'name' => 'Accepted/Rejected',
                'completed' => false,
                'current' => false
            ],
            [
                'name' => 'Completed',
                'completed' => false,
                'current' => false
            ]
        ];

        // Map status to timeline stages
        // Database enum values: 'submitted','in-progress','approved','rejected','completed'
        $statusMap = [
            'submitted' => ['current' => 1], // In Process (current stage)
            'in-progress' => ['current' => 1], // In Process (current stage)
            'in_progress' => ['current' => 1], // Support underscore variant
            'approved' => ['completed' => [1], 'current' => 2, 'label' => 'Accepted'], // Accepted/Rejected (current stage) - show as "Accepted"
            'rejected' => ['completed' => [1], 'current' => 2, 'label' => 'Rejected'], // Accepted/Rejected (current stage) - show as "Rejected"
            'completed' => ['completed' => [0, 1, 2, 3], 'current' => null], // All completed
            // Legacy/alternative status names for backward compatibility
            'under_review' => ['current' => 1], // Maps to in-progress
            'processing' => ['current' => 1], // Maps to in-progress
            'ready_for_collection' => ['completed' => [1], 'current' => 2, 'label' => 'Accepted'], // Maps to approved
            'cancelled' => ['completed' => [1], 'current' => 2, 'label' => 'Rejected'] // Maps to rejected
        ];

        $statusConfig = $statusMap[$status] ?? $statusMap['submitted'];
        
        // Mark completed stages
        if (isset($statusConfig['completed'])) {
            foreach ($statusConfig['completed'] as $index) {
                if (isset($stages[$index])) {
                    $stages[$index]['completed'] = true;
                    $stages[$index]['current'] = false;
                    if ($updatedAt && $index > 0) {
                        $stages[$index]['date'] = $updatedAt;
                    }
                }
            }
        }
        
        // Mark current stage
        if (isset($statusConfig['current']) && $statusConfig['current'] !== null) {
            $currentIndex = $statusConfig['current'];
            if (isset($stages[$currentIndex])) {
                $stages[$currentIndex]['current'] = true;
                $stages[$currentIndex]['completed'] = false;
                
                // Update stage name if label is provided (for Accepted/Rejected)
                if (isset($statusConfig['label'])) {
                    $stages[$currentIndex]['name'] = $statusConfig['label'];
                }
                
                // Add date to current stage if updated_at is available
                if ($updatedAt && $currentIndex > 0) {
                    $stages[$currentIndex]['date'] = $updatedAt;
                }
            }
        }

        return $stages;
    }

    /**
     * Get filled PDF URL
     */
    private function getFilledPdfUrl(string $path): string
    {
        $baseUrl = $_ENV['API_BASE_URL'] ?? 'http://localhost';
        return rtrim($baseUrl, '/') . $path;
    }
}
