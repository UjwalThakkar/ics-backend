<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\Application;
use IndianConsular\Models\Service;
use IndianConsular\Services\NotificationService;

class ApplicationController extends BaseController
{
    private Application $applicationModel;
    private Service $serviceModel;
    private NotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->applicationModel = new Application();
        $this->serviceModel = new Service();
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
}
