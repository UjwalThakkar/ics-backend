<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\VerificationCenter;

class VerificationCenterController extends BaseController
{
    private VerificationCenter $centerModel;

    public function __construct()
    {
        parent::__construct();
        $this->centerModel = new VerificationCenter();
    }

    /**
     * Get all active centers (Public)
     * GET /centers
     */
    public function list(array $data, array $params): array
    {
        try {
            $centers = $this->centerModel->getActiveCenters();

            // Decode JSON fields for each center
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
     * Get single center by ID (Public)
     * GET /centers/{id}
     */
    public function get(array $data, array $params): array
    {
        $centerId = $params['id'] ?? '';

        if (empty($centerId)) {
            return $this->error('Center ID is required', 400);
        }

        try {
            $center = $this->centerModel->getCenterWithCounters((int)$centerId);

            if (!$center) {
                return $this->error('Center not found', 404);
            }

            return $this->success(['center' => $center]);

        } catch (\Exception $e) {
            error_log("Get center error: " . $e->getMessage());
            return $this->error('Failed to load center', 500);
        }
    }

    /**
     * Get center with services (Public)
     * GET /centers/{id}/services
     */
    public function getCenterServices(array $data, array $params): array
    {
        $centerId = $params['id'] ?? '';

        if (empty($centerId)) {
            return $this->error('Center ID is required', 400);
        }

        try {
            $center = $this->centerModel->getCenterWithServices((int)$centerId);

            if (!$center) {
                return $this->error('Center not found', 404);
            }

            return $this->success(['center' => $center]);

        } catch (\Exception $e) {
            error_log("Get center services error: " . $e->getMessage());
            return $this->error('Failed to load center services', 500);
        }
    }

    /**
     * Get centers by city (Public)
     * GET /centers/city/{city}
     */
    public function getByCity(array $data, array $params): array
    {
        $city = $params['city'] ?? '';

        if (empty($city)) {
            return $this->error('City is required', 400);
        }

        try {
            $centers = $this->centerModel->getCentersByCity($city);

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

            return $this->success([
                'city' => $city,
                'centers' => $centers,
                'count' => count($centers)
            ]);

        } catch (\Exception $e) {
            error_log("Get centers by city error: " . $e->getMessage());
            return $this->error('Failed to load centers', 500);
        }
    }

    /**
     * Get centers by country (Public)
     * GET /centers/country/{country}
     */
    public function getByCountry(array $data, array $params): array
    {
        $country = $params['country'] ?? '';

        if (empty($country)) {
            return $this->error('Country is required', 400);
        }

        try {
            $centers = $this->centerModel->getCentersByCountry($country);

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

            return $this->success([
                'country' => $country,
                'centers' => $centers,
                'count' => count($centers)
            ]);

        } catch (\Exception $e) {
            error_log("Get centers by country error: " . $e->getMessage());
            return $this->error('Failed to load centers', 500);
        }
    }

    /**
     * Search nearby centers (Public)
     * GET /centers/nearby?lat={latitude}&lng={longitude}&radius={radiusKm}
     */
    public function searchNearby(array $data, array $params): array
    {
        $latitude = $data['lat'] ?? '';
        $longitude = $data['lng'] ?? '';
        $radius = $data['radius'] ?? 50;

        if (empty($latitude) || empty($longitude)) {
            return $this->error('Latitude and longitude are required', 400);
        }

        try {
            $centers = $this->centerModel->searchNearby(
                (float)$latitude,
                (float)$longitude,
                (float)$radius
            );

            return $this->success([
                'location' => [
                    'latitude' => (float)$latitude,
                    'longitude' => (float)$longitude,
                    'radius_km' => (float)$radius
                ],
                'centers' => $centers,
                'count' => count($centers)
            ]);

        } catch (\Exception $e) {
            error_log("Search nearby centers error: " . $e->getMessage());
            return $this->error('Failed to search nearby centers', 500);
        }
    }

    /**
     * Get available time slots for center on specific date (Public)
     * GET /centers/{id}/available-slots?date={date}
     */
    public function getAvailableSlots(array $data, array $params): array
    {
        $centerId = $params['id'] ?? '';
        $date = $data['date'] ?? '';

        if (empty($centerId) || empty($date)) {
            return $this->error('Center ID and date are required', 400);
        }

        try {
            $slots = $this->centerModel->getAvailableSlots((int)$centerId, $date);

            return $this->success([
                'center_id' => (int)$centerId,
                'date' => $date,
                'slots' => $slots,
                'count' => count($slots)
            ]);

        } catch (\Exception $e) {
            error_log("Get available slots error: " . $e->getMessage());
            return $this->error('Failed to load available slots', 500);
        }
    }

    // =============================================
    // ADMIN ENDPOINTS (Require Admin Authentication)
    // =============================================

    /**
     * Get all centers (including inactive) (Admin)
     * GET /admin/centers
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

            $centers = $this->centerModel->getAllCentersWithCounterCount();

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

            // Pagination
            $total = count($centers);
            $centers = array_slice($centers, ($page - 1) * $limit, $limit);

            return $this->success([
                'centers' => $centers,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => ceil($total / $limit)
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Admin get centers error: " . $e->getMessage());
            return $this->error('Failed to load centers', 500);
        }
    }

    /**
     * Create new center (Admin)
     * POST /admin/centers
     */
    public function create(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $data = $this->sanitize($data);

        $missing = $this->validateRequired($data, [
            'name', 'address', 'city', 'country'
        ]);

        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            $centerData = [
                'name' => $data['name'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'] ?? null,
                'country' => $data['country'],
                'postal_code' => $data['postalCode'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'operating_hours' => json_encode($data['operatingHours'] ?? []),
                'provides_services' => json_encode($data['providesServices'] ?? []),
                'has_counters' => json_encode($data['hasCounters'] ?? []),
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'is_active' => $data['isActive'] ?? 1,
                'display_order' => $data['displayOrder'] ?? 0
            ];

            $centerId = $this->centerModel->insert($centerData);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'CREATE_CENTER',
                ['center_id' => $centerId, 'name' => $data['name']],
                $this->getClientIp(),
                $this->getUserAgent(),
                'verification_center',
                (string)$centerId
            );

            return $this->success([
                'message' => 'Center created successfully',
                'centerId' => $centerId
            ], 201);

        } catch (\Exception $e) {
            error_log("Create center error: " . $e->getMessage());
            return $this->error('Failed to create center', 500);
        }
    }

    /**
     * Update center (Admin)
     * PUT /admin/centers/{id}
     */
    public function update(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $centerId = $params['id'] ?? '';
        if (empty($centerId)) {
            return $this->error('Center ID is required', 400);
        }

        try {
            $center = $this->centerModel->findByCenterId((int)$centerId);
            if (!$center) {
                return $this->error('Center not found', 404);
            }

            $updateData = [];
            $allowedFields = [
                'name', 'address', 'city', 'state', 'country', 'postal_code',
                'phone', 'email', 'latitude', 'longitude', 'is_active', 'display_order'
            ];

            foreach ($allowedFields as $field) {
                $dataKey = lcfirst(str_replace('_', '', ucwords($field, '_')));
                if (isset($data[$dataKey])) {
                    $updateData[$field] = $data[$dataKey];
                }
            }

            // Handle JSON fields
            if (isset($data['operatingHours'])) {
                $this->centerModel->updateOperatingHours((int)$centerId, $data['operatingHours']);
            }

            if (isset($data['providesServices'])) {
                $this->centerModel->updateProvidedServices((int)$centerId, $data['providesServices']);
            }

            if (!empty($updateData)) {
                $sql = "UPDATE verification_center SET " . 
                       implode(', ', array_map(fn($k) => "$k = ?", array_keys($updateData))) . 
                       ", updated_at = NOW() WHERE center_id = ?";
                
                $params = array_merge(array_values($updateData), [(int)$centerId]);
                $this->centerModel->query($sql, $params);
            }

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                'UPDATE_CENTER',
                ['center_id' => $centerId, 'updates' => array_keys($updateData)],
                $this->getClientIp(),
                $this->getUserAgent(),
                'verification_center',
                $centerId
            );

            return $this->success(['message' => 'Center updated successfully']);

        } catch (\Exception $e) {
            error_log("Update center error: " . $e->getMessage());
            return $this->error('Failed to update center', 500);
        }
    }

    /**
     * Toggle center active status (Admin)
     * POST /admin/centers/{id}/toggle
     */
    public function toggleActive(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $centerId = $params['id'] ?? '';
        if (empty($centerId)) {
            return $this->error('Center ID is required', 400);
        }

        try {
            $center = $this->centerModel->findByCenterId((int)$centerId);
            if (!$center) {
                return $this->error('Center not found', 404);
            }

            $newStatus = !$center['is_active'];
            $this->centerModel->toggleActive((int)$centerId, (bool)$newStatus);

            // Log admin activity
            $this->logService->logAdminActivity(
                $admin['id'],
                $newStatus ? 'ACTIVATE_CENTER' : 'DEACTIVATE_CENTER',
                ['center_id' => $centerId],
                $this->getClientIp(),
                $this->getUserAgent(),
                'verification_center',
                $centerId
            );

            return $this->success([
                'message' => 'Center ' . ($newStatus ? 'activated' : 'deactivated') . ' successfully',
                'isActive' => $newStatus
            ]);

        } catch (\Exception $e) {
            error_log("Toggle center error: " . $e->getMessage());
            return $this->error('Failed to toggle center status', 500);
        }
    }
}