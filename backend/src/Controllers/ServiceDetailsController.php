<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\ServiceDetails;

class ServiceDetailsController extends BaseController
{
    private ServiceDetails $serviceDetailsModel;

    public function __construct()
    {
        parent::__construct();
        $this->serviceDetailsModel = new ServiceDetails();
    }

    // =============================================
    // PUBLIC ENDPOINTS (For End-Users)
    // =============================================

    /**
     * Get details for a specific service (Public)
     * GET /service-details/{serviceId}
     */
    public function get(array $data, array $params): array
    {
        $serviceId = $params['serviceId'] ?? '';

        if (empty($serviceId)) {
            return $this->error('Service ID is required', 400);
        }

        try {
            $details = $this->serviceDetailsModel->findByServiceId((int)$serviceId);

            if (!$details) {
                return $this->error('Service details not found', 404);
            }

            error_log("Fetched service details: " . print_r($details, true));

            return $this->success(['details' => $details]);
        } catch (\Exception $e) {
            error_log("Get service details error: " . $e->getMessage());
            return $this->error('Failed to load service details', 500);
        }
    }

    // =============================================
    // ADMIN ENDPOINTS (Require Admin Authentication)
    // =============================================

    /**
     * List all service details (Admin)
     * GET /admin/service-details
     */
    public function adminList(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $details = $this->serviceDetailsModel->getAllDetails();

            return $this->success([
                'details' => $details,
                'count' => count($details)
            ]);
        } catch (\Exception $e) {
            error_log("Admin list service details error: " . $e->getMessage());
            return $this->error('Failed to load service details', 500);
        }
    }

    /**
     * Create new service details (Admin)
     * POST /admin/service-details
     * Body: { "serviceId": 1, "overview": "...", "visaFees": [...], ... }
     */
    public function adminCreate(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $required = ['serviceId', 'overview']; // Add more as needed
        $missing = $this->validateRequired($data, $required);
        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            $insertData = [
                'service_id' => (int)$data['serviceId'],
                'overview' => $data['overview'] ?? '',
                'visa_fees' => $data['visaFees'] ?? '', // Array for JSON
                'documents_required' => $data['documentsRequired'] ?? '', // Array for JSON
                'photo_specifications' => $data['photoSpecifications'] ?? '',
                'processing_time' => $data['processingTime'] ?? '',
                'downloads_form' => $data['downloadsForm'] ?? '' // Array for JSON
            ];

            $this->serviceDetailsModel->createDetails($insertData);

            // Log admin activity (assuming LogService has this method)
            $this->logService->logAdminActivity(
                $admin['id'],
                'CREATE_SERVICE_DETAILS',
                ['service_id' => $insertData['service_id']],
                $this->getClientIp(),
                $this->getUserAgent(),
                'service_details',
                (string)$insertData['service_id']
            );

            return $this->success(['message' => 'Service details created successfully'], 201);
        } catch (\Exception $e) {
            error_log("Create service details error: " . $e->getMessage());
            return $this->error('Failed to create service details', 500);
        }
    }

    /**
     * Update service details (Admin)
     * PUT /admin/service-details/{serviceId}
     * Body: { "overview": "...", "visaFees": [...], ... }
     */
    public function adminUpdate(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $serviceId = $params['serviceId'] ?? '';
        if (empty($serviceId)) {
            return $this->error('Service ID is required', 400);
        }

        try {
            $existing = $this->serviceDetailsModel->findByServiceId((int)$serviceId);
            if (!$existing) {
                return $this->error('Service details not found', 404);
            }

            $updateData = [];
            if (isset($data['overview'])) {
                $updateData['overview'] = $data['overview'];
            }
            if (isset($data['visaFees'])) {
                $updateData['visa_fees'] = $data['visaFees']; // Array for JSON
            }
            if (isset($data['documentsRequired'])) {
                $updateData['documents_required'] = $data['documentsRequired']; // Array for JSON
            }
            if (isset($data['photoSpecifications'])) {
                $updateData['photo_specifications'] = $data['photoSpecifications'];
            }
            if (isset($data['processingTime'])) {
                $updateData['processing_time'] = $data['processingTime'];
            }
            if (isset($data['downloadsForm'])) {
                $updateData['downloads_form'] = $data['downloadsForm']; // Array for JSON
            }

            if (empty($updateData)) {
                return $this->error('No fields to update', 400);
            }

            $this->serviceDetailsModel->updateDetails((int)$serviceId, $updateData);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'UPDATE_SERVICE_DETAILS',
                ['service_id' => $serviceId, 'updates' => array_keys($updateData)],
                $this->getClientIp(),
                $this->getUserAgent(),
                'service_details',
                $serviceId
            );

            return $this->success(['message' => 'Service details updated successfully']);
        } catch (\Exception $e) {
            error_log("Update service details error: " . $e->getMessage());
            return $this->error('Failed to update service details', 500);
        }
    }

    /**
     * Delete service details (Admin)
     * DELETE /admin/service-details/{serviceId}
     */
    public function adminDelete(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $serviceId = $params['serviceId'] ?? '';
        if (empty($serviceId)) {
            return $this->error('Service ID is required', 400);
        }

        try {
            $existing = $this->serviceDetailsModel->findByServiceId((int)$serviceId);
            if (!$existing) {
                return $this->error('Service details not found', 404);
            }

            $this->serviceDetailsModel->deleteDetails((int)$serviceId);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'DELETE_SERVICE_DETAILS',
                ['service_id' => $serviceId],
                $this->getClientIp(),
                $this->getUserAgent(),
                'service_details',
                $serviceId
            );

            return $this->success(['message' => 'Service details deleted successfully']);
        } catch (\Exception $e) {
            error_log("Delete service details error: " . $e->getMessage());
            return $this->error('Failed to delete service details', 500);
        }
    }
}
