<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\Service;

class ServiceController extends BaseController
{
    private Service $serviceModel;

    public function __construct()
    {
        parent::__construct();
        $this->serviceModel = new Service();
    }

    /**
     * Helper method to safely decode JSON fields
     */
    private function decodeServiceJsonFields(array &$service): void
    {
        $service['fees'] = $service['fees'] ? json_decode($service['fees'], true) : [];
        $service['required_documents'] = $service['required_documents'] ? json_decode($service['required_documents'], true) : [];
        $service['eligibility_requirements'] = $service['eligibility_requirements'] ? json_decode($service['eligibility_requirements'], true) : [];
    }

    /**
     * Get all services (Public)
     * GET /services
     * Optional filters: ?category=Passport Services&active=1
     */
    public function list(array $data, array $params): array
    {
        try {
            $category = $data['category'] ?? '';
            $activeOnly = isset($data['active']) ? (bool)$data['active'] : true;

            if ($category) {
                $services = $this->serviceModel->getServicesByCategory($category);
            } elseif ($activeOnly) {
                $services = $this->serviceModel->getActiveServices();
            } else {
                $services = $this->serviceModel->findAll([], 'category ASC, display_order ASC, title ASC');
            }

            // Decode JSON fields
            foreach ($services as &$service) {
                $this->decodeServiceJsonFields($service);
            }

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
     * Get single service by ID (Public)
     * GET /services/{id}
     */
    public function get(array $data, array $params): array
    {
        $serviceId = $params['id'] ?? '';

        if (empty($serviceId)) {
            return $this->error('Service ID is required', 400);
        }

        try {
            $service = $this->serviceModel->findByServiceId($serviceId);

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Decode JSON fields
            $this->decodeServiceJsonFields($service);

            return $this->success(['service' => $service]);

        } catch (\Exception $e) {
            error_log("Get service error: " . $e->getMessage());
            return $this->error('Failed to load service', 500);
        }
    }

    /**
     * Get all service categories (Public)
     * GET /services/categories
     */
    public function categories(array $data, array $params): array
    {
        try {
            $categories = $this->serviceModel->getCategories();

            return $this->success([
                'categories' => $categories,
                'count' => count($categories)
            ]);

        } catch (\Exception $e) {
            error_log("Get categories error: " . $e->getMessage());
            return $this->error('Failed to load categories', 500);
        }
    }

    /**
     * Get services by category (Public)
     * GET /services/category/{categoryName}
     */
    public function byCategory(array $data, array $params): array
    {
        $category = $params['category'] ?? '';

        if (empty($category)) {
            return $this->error('Category is required', 400);
        }

        try {
            $services = $this->serviceModel->getServicesByCategory($category);

            // Decode JSON fields
            foreach ($services as &$service) {
                $this->decodeServiceJsonFields($service);
            }

            return $this->success([
                'category' => $category,
                'services' => $services,
                'count' => count($services)
            ]);

        } catch (\Exception $e) {
            error_log("Get services by category error: " . $e->getMessage());
            return $this->error('Failed to load services', 500);
        }
    }

    /**
     * Search services (Public)
     * GET /services/search?q=passport
     */
    public function search(array $data, array $params): array
    {
        $query = $data['q'] ?? '';

        if (empty($query)) {
            return $this->error('Search query is required', 400);
        }

        try {
            $sql = "SELECT * FROM services 
                    WHERE is_active = 1 
                    AND (
                        title LIKE ? 
                        OR description LIKE ? 
                        OR category LIKE ?
                    )
                    ORDER BY display_order ASC, title ASC";

            $searchTerm = '%' . $query . '%';
            $stmt = $this->serviceModel->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
            $services = $stmt->fetchAll();

            // Decode JSON fields
            foreach ($services as &$service) {
                $this->decodeServiceJsonFields($service);
            }

            return $this->success([
                'query' => $query,
                'services' => $services,
                'count' => count($services)
            ]);

        } catch (\Exception $e) {
            error_log("Search services error: " . $e->getMessage());
            return $this->error('Failed to search services', 500);
        }
    }

    // =============================================
    // ADMIN ENDPOINTS (Require Admin Authentication)
    // =============================================

    /**
     * Get all services for admin (Admin)
     * GET /admin/services
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

            $filters = [];
            if (isset($data['active'])) {
                $filters['is_active'] = (int)$data['active'];
            }
            if (!empty($data['category'])) {
                $filters['category'] = $data['category'];
            }

            $services = $this->serviceModel->findAll(
                $filters,
                'category ASC, display_order ASC, title ASC',
                $limit,
                $offset
            );

            $total = $this->serviceModel->count($filters);

            // Decode JSON fields
            foreach ($services as &$service) {
                $this->decodeServiceJsonFields($service);
            }

            return $this->success([
                'services' => $services,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => ceil($total / $limit)
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Admin get services error: " . $e->getMessage());
            return $this->error('Failed to load services', 500);
        }
    }

    /**
     * Create new service (Admin)
     * POST /admin/services
     */
    public function create(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $data = $this->sanitize($data);

        $missing = $this->validateRequired($data, [
            'serviceId', 'category', 'title', 'fees', 'requiredDocuments'
        ]);

        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            // Check if service ID already exists
            if ($this->serviceModel->findByServiceId($data['serviceId'])) {
                return $this->error('Service ID already exists', 409);
            }

            $serviceData = [
                'service_id' => $data['serviceId'],
                'category' => $data['category'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'processing_time' => $data['processingTime'] ?? '',
                'fees' => json_encode($data['fees']),
                'required_documents' => json_encode($data['requiredDocuments']),
                'eligibility_requirements' => json_encode($data['eligibilityRequirements'] ?? []),
                'is_active' => $data['isActive'] ?? true,
                'display_order' => $data['displayOrder'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->serviceModel->insert($serviceData);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'CREATE_SERVICE',
                ['service_id' => $data['serviceId'], 'title' => $data['title']],
                $this->getClientIp(),
                $this->getUserAgent(),
                'service',
                $data['serviceId']
            );

            return $this->success([
                'message' => 'Service created successfully',
                'serviceId' => $data['serviceId']
            ], 201);

        } catch (\Exception $e) {
            error_log("Create service error: " . $e->getMessage());
            return $this->error('Failed to create service', 500);
        }
    }

    /**
     * Update service (Admin)
     * PUT /admin/services/{id}
     */
    public function update(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $serviceId = $params['id'] ?? '';
        if (empty($serviceId)) {
            return $this->error('Service ID is required', 400);
        }

        try {
            $service = $this->serviceModel->findByServiceId($serviceId);
            if (!$service) {
                return $this->error('Service not found', 404);
            }

            $updateData = [];
            $allowedFields = [
                'category', 'title', 'description', 'processing_time',
                'fees', 'required_documents', 'eligibility_requirements',
                'is_active', 'display_order'
            ];

            foreach ($allowedFields as $field) {
                $dataKey = lcfirst(str_replace('_', '', ucwords($field, '_')));
                if (isset($data[$dataKey])) {
                    // JSON fields need encoding
                    if (in_array($field, ['fees', 'required_documents', 'eligibility_requirements'])) {
                        $updateData[$field] = json_encode($data[$dataKey]);
                    } else {
                        $updateData[$field] = $data[$dataKey];
                    }
                }
            }

            if (empty($updateData)) {
                return $this->error('No valid fields to update', 400);
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $this->serviceModel->updateBy('service_id', $serviceId, $updateData);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'UPDATE_SERVICE',
                ['service_id' => $serviceId, 'updates' => array_keys($updateData)],
                $this->getClientIp(),
                $this->getUserAgent(),
                'service',
                $serviceId
            );

            return $this->success(['message' => 'Service updated successfully']);

        } catch (\Exception $e) {
            error_log("Update service error: " . $e->getMessage());
            return $this->error('Failed to update service', 500);
        }
    }

    /**
     * Delete service (Admin)
     * DELETE /admin/services/{id}
     */
    public function delete(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $serviceId = $params['id'] ?? '';
        if (empty($serviceId)) {
            return $this->error('Service ID is required', 400);
        }

        try {
            $service = $this->serviceModel->findByServiceId($serviceId);
            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Check if service is being used in applications
            $sql = "SELECT COUNT(*) as count FROM applications WHERE service_id = ?";
            $stmt = $this->serviceModel->query($sql, [$serviceId]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                return $this->error(
                    'Cannot delete service that has existing applications. Deactivate it instead.',
                    400
                );
            }

            $this->serviceModel->deleteBy('service_id', $serviceId);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'DELETE_SERVICE',
                ['service_id' => $serviceId, 'title' => $service['title']],
                $this->getClientIp(),
                $this->getUserAgent(),
                'service',
                $serviceId
            );

            return $this->success(['message' => 'Service deleted successfully']);

        } catch (\Exception $e) {
            error_log("Delete service error: " . $e->getMessage());
            return $this->error('Failed to delete service', 500);
        }
    }

    /**
     * Toggle service active status (Admin)
     * POST /admin/services/{id}/toggle
     */
    public function toggleActive(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $serviceId = $params['id'] ?? '';
        if (empty($serviceId)) {
            return $this->error('Service ID is required', 400);
        }

        try {
            $service = $this->serviceModel->findByServiceId($serviceId);
            if (!$service) {
                return $this->error('Service not found', 404);
            }

            $newStatus = !$service['is_active'];

            $this->serviceModel->updateBy('service_id', $serviceId, [
                'is_active' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                $newStatus ? 'ACTIVATE_SERVICE' : 'DEACTIVATE_SERVICE',
                ['service_id' => $serviceId],
                $this->getClientIp(),
                $this->getUserAgent(),
                'service',
                $serviceId
            );

            return $this->success([
                'message' => 'Service ' . ($newStatus ? 'activated' : 'deactivated') . ' successfully',
                'isActive' => $newStatus
            ]);

        } catch (\Exception $e) {
            error_log("Toggle service error: " . $e->getMessage());
            return $this->error('Failed to toggle service status', 500);
        }
    }
}