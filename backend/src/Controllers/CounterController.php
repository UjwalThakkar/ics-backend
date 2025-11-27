<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\ServiceCounter;

class CounterController extends BaseController
{
    private ServiceCounter $counterModel;

    public function __construct()
    {
        parent::__construct();
        $this->counterModel = new ServiceCounter();
    }

    /**
     * List all counters (Admin) – with flexible filtering
     * GET /admin/counters
     * 
     * Query Params:
     *   ?center_id=1           → filter by center
     *   ?include_inactive=1    → show inactive counters too
     *   ?all=1                 → shortcut for include_inactive=1
     */
    public function adminList(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $centerId        = !empty($data['center_id']) ? (int)$data['center_id'] : null;
        $includeInactive = !empty($data['include_inactive']) || !empty($data['all']);

        try {
            $counters = $this->counterModel->getCounters(
                $centerId,           // null = all centers
                $includeInactive = true    // true = show inactive too
            );

            // Optional: Add human-readable status (great for frontend)
            foreach ($counters as &$counter) {
                $counter['status_text']  = $counter['is_active'] ? 'Active' : 'Inactive';
                $counter['status_color'] = $counter['is_active'] ? 'success' : 'danger';
            }
            unset($counter);

            return $this->success([
                'counters' => $counters,
                'total'    => count($counters),
                'filtered_by_center' => $centerId ? true : false,
                'showing_inactive'   => $includeInactive
            ]);
        } catch (\Exception $e) {
            error_log("List counters error: " . $e->getMessage());
            return $this->error('Failed to list counters', 500);
        }
    }

    /**
     * FULL Counter Update – including service_handled (with validation)
     * PUT /admin/counters/{id}
     */
    public function update(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $counterId = $params['id'] ?? '';
        if (empty($counterId)) {
            return $this->error('Counter ID required', 400);
        }

        try {
            // Load current counter + its parent center
            $counter = $this->counterModel->findByCounterId((int)$counterId);
            if (!$counter) {
                return $this->error('Counter not found', 404);
            }

            $centerModel = new \IndianConsular\Models\VerificationCenter();
            $center = $centerModel->findByCenterId($counter['center_id']);
            if (!$center) {
                return $this->error('Parent center not found', 404);
            }

            $updateData = [];
            $updatedFields = [];

            // 1. Update name
            if (isset($data['counter_name'])) {
                $updateData['counter_name'] = trim($data['counter_name']);
                $updatedFields[] = 'counter_name';
            }

            // 2. Update is_active
            if (isset($data['is_active'])) {
                $updateData['is_active'] = (int)(bool)$data['is_active'];
                $updatedFields[] = 'is_active';
            }

            // 3. Update service_handled (with validation!)
            if (isset($data['service_handled']) || array_key_exists('service_handled', $data)) {
                $serviceIds = $data['service_handled'] ?? [];

                if (!is_array($serviceIds)) {
                    return $this->error('service_handled must be an array', 400);
                }

                // Validate: all services must be offered by the parent Verification Center
                $vcServices = json_decode($center['provides_services'] ?? '[]', true);
                foreach ($serviceIds as $sid) {
                    if (!in_array((int)$sid, $vcServices, true)) {
                        return $this->error("Service ID {$sid} is not offered by this center", 400);
                    }
                }

                $updateData['service_handled'] = json_encode(array_map('intval', $serviceIds));
                $updatedFields[] = 'service_handled';
            }

            if (empty($updateData)) {
                return $this->error('No valid fields to update', 400);
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $this->counterModel->update((int)$counterId, $updateData);

            // Log activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'UPDATE_COUNTER_FULL',
                ['counter_id' => $counterId, 'updated_fields' => $updatedFields],
                $this->getClientIp(),
                $this->getUserAgent(),
                'counter',
                (string)$counterId
            );

            return $this->success([
                'message' => 'Counter updated successfully',
                'updated_fields' => $updatedFields
            ]);
        } catch (\Exception $e) {
            error_log("Counter update error: " . $e->getMessage());
            return $this->error('Failed to update counter', 500);
        }
    }

    /**
     * Get single counter details (Admin)
     * GET /admin/counters/{id}
     */
    public function get(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $counterId = $params['id'] ?? '';
        if (empty($counterId)) {
            return $this->error('Counter ID required', 400);
        }

        try {
            $counter = $this->counterModel->findByCounterId((int)$counterId);
            if (!$counter) {
                return $this->error('Counter not found', 404);
            }

            return $this->success(['counter' => $counter]);
        } catch (\Exception $e) {
            error_log("Get counter error: " . $e->getMessage());
            return $this->error('Failed to load counter', 500);
        }
    }

    /**
     * Create new counter (Admin)
     * POST /admin/counters
     */
    public function create(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $data = $this->sanitize($data);

        $missing = $this->validateRequired($data, ['center_id', 'counter_name']);
        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            $counterData = [
                'center_id' => (int)$data['center_id'],
                'counter_name' => $data['counter_name'],
                'service_handled' => json_encode($data['service_handled'] ?? []),
                'is_active' => isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $counterId = $this->counterModel->insert($counterData);

            // Optional: Update VC's has_counters JSON if needed (add counter_id)
            $centerModel = new \IndianConsular\Models\VerificationCenter();
            $center = $centerModel->findByCenterId($counterData['center_id']);
            if ($center) {
                $hasCounters = json_decode($center['has_counters'] ?? '[]', true);
                if (!in_array($counterId, $hasCounters)) {
                    $hasCounters[] = $counterId;
                    $centerModel->query(
                        "UPDATE verification_center SET has_counters = ? WHERE center_id = ?",
                        [json_encode($hasCounters), $counterData['center_id']]
                    );
                }
            }

            // Log activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'CREATE_COUNTER',
                ['counter_id' => $counterId, 'name' => $data['counter_name']],
                $this->getClientIp(),
                $this->getUserAgent(),
                'counter',
                (string)$counterId
            );

            return $this->success([
                'message' => 'Counter created successfully',
                'counterId' => $counterId
            ], 201);
        } catch (\Exception $e) {
            error_log("Create counter error: " . $e->getMessage());
            return $this->error('Failed to create counter', 500);
        }
    }

    /**
     * Toggle counter active status (Admin)
     * POST /admin/counters/{id}/toggle
     */
    public function toggleActive(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $counterId = $params['id'] ?? '';
        if (empty($counterId)) {
            return $this->error('Counter ID is required', 400);
        }

        try {
            $counter = $this->counterModel->findByCounterId((int)$counterId);
            if (!$counter) {
                return $this->error('Counter not found', 404);
            }

            $newStatus = !$counter['is_active'];
            $this->counterModel->toggleActive((int)$counterId, (bool)$newStatus);

            // Log activity
            $this->logService->logAdminActivity(
                $admin['id'],
                $newStatus ? 'ACTIVATE_COUNTER' : 'DEACTIVATE_COUNTER',
                ['counter_id' => $counterId],
                $this->getClientIp(),
                $this->getUserAgent(),
                'counter',
                (string)$counterId
            );

            return $this->success([
                'message' => 'Counter ' . ($newStatus ? 'activated' : 'deactivated') . ' successfully',
                'isActive' => $newStatus
            ]);
        } catch (\Exception $e) {
            error_log("Toggle counter error: " . $e->getMessage());
            return $this->error('Failed to toggle counter status', 500);
        }
    }

    /**
     * Update counter services (Admin)
     * PUT /admin/counters/{id}/services
     * Body: { "service_ids": [1, 2, 3] }
     */
    public function updateServices(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $counterId = $params['id'] ?? '';
        if (empty($counterId)) {
            return $this->error('Counter ID is required', 400);
        }

        $serviceIds = $data['service_ids'] ?? [];
        if (!is_array($serviceIds)) {
            return $this->error('service_ids must be an array', 400);
        }

        try {
            $counter = $this->counterModel->findByCounterId((int)$counterId);
            if (!$counter) {
                return $this->error('Counter not found', 404);
            }

            // Validate: Services must be in the parent VC's provides_services
            $centerModel = new \IndianConsular\Models\VerificationCenter();
            $center = $centerModel->findByCenterId($counter['center_id']);
            if (!$center) {
                return $this->error('Parent center not found', 404);
            }
            $vcServices = json_decode($center['provides_services'] ?? '[]', true);
            foreach ($serviceIds as $sid) {
                if (!in_array($sid, $vcServices)) {
                    return $this->error("Service ID $sid is not provided by the parent center", 400);
                }
            }

            $this->counterModel->updateServicesHandled((int)$counterId, $serviceIds);

            // Log activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'UPDATE_COUNTER_SERVICES',
                ['counter_id' => $counterId, 'services' => $serviceIds],
                $this->getClientIp(),
                $this->getUserAgent(),
                'counter',
                (string)$counterId
            );

            return $this->success(['message' => 'Counter services updated successfully']);
        } catch (\Exception $e) {
            error_log("Update counter services error: " . $e->getMessage());
            return $this->error('Failed to update counter services', 500);
        }
    }
}
