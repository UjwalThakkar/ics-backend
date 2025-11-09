<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class Service extends BaseModel
{
    protected string $table = 'service';
    protected string $primaryKey = 'service_id';

    /**
     * Find service by service_id
     */
    public function findByServiceId(int $serviceId): ?array
    {
        $service = $this->find($serviceId);
        
        if ($service) {
            // Decode JSON fields
            if (isset($service['fees'])) {
                $service['fees'] = json_decode($service['fees'], true);
            }
            if (isset($service['required_documents'])) {
                $service['required_documents'] = json_decode($service['required_documents'], true);
            }
            if (isset($service['eligibility_requirements'])) {
                $service['eligibility_requirements'] = json_decode($service['eligibility_requirements'], true);
            }
        }

        return $service;
    }

    /**
     * Get all active services
     */
    public function getActiveServices(): array
    {
        $services = $this->findAll(['is_active' => 1], 'display_order ASC, category ASC, title ASC');
        
        // Decode JSON fields
        foreach ($services as &$service) {
            if (isset($service['fees'])) {
                $service['fees'] = json_decode($service['fees'], true);
            }
            if (isset($service['required_documents'])) {
                $service['required_documents'] = json_decode($service['required_documents'], true);
            }
            if (isset($service['eligibility_requirements'])) {
                $service['eligibility_requirements'] = json_decode($service['eligibility_requirements'], true);
            }
        }

        return $services;
    }

    /**
     * Get services by category
     */
    public function getServicesByCategory(string $category): array
    {
        $services = $this->findAll([
            'category' => $category,
            'is_active' => 1
        ], 'display_order ASC, title ASC');

        // Decode JSON fields
        foreach ($services as &$service) {
            if (isset($service['fees'])) {
                $service['fees'] = json_decode($service['fees'], true);
            }
            if (isset($service['required_documents'])) {
                $service['required_documents'] = json_decode($service['required_documents'], true);
            }
            if (isset($service['eligibility_requirements'])) {
                $service['eligibility_requirements'] = json_decode($service['eligibility_requirements'], true);
            }
        }

        return $services;
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        $stmt = $this->query("SELECT DISTINCT category FROM service WHERE is_active = 1 ORDER BY category");
        return array_column($stmt->fetchAll(), 'category');
    }

    /**
     * Get services grouped by category
     */
    public function getServicesGroupedByCategory(): array
    {
        $services = $this->getActiveServices();
        $grouped = [];

        foreach ($services as $service) {
            $category = $service['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $service;
        }

        return $grouped;
    }

    /**
     * Create new service
     */
    public function createService(array $data): int
    {
        // Encode JSON fields
        if (isset($data['fees']) && is_array($data['fees'])) {
            $data['fees'] = json_encode($data['fees']);
        }
        if (isset($data['required_documents']) && is_array($data['required_documents'])) {
            $data['required_documents'] = json_encode($data['required_documents']);
        }
        if (isset($data['eligibility_requirements']) && is_array($data['eligibility_requirements'])) {
            $data['eligibility_requirements'] = json_encode($data['eligibility_requirements']);
        }

        return $this->insert($data);
    }

    /**
     * Update service
     */
    public function updateService(int $serviceId, array $data): bool
    {
        // Encode JSON fields if they are arrays
        if (isset($data['fees']) && is_array($data['fees'])) {
            $data['fees'] = json_encode($data['fees']);
        }
        if (isset($data['required_documents']) && is_array($data['required_documents'])) {
            $data['required_documents'] = json_encode($data['required_documents']);
        }
        if (isset($data['eligibility_requirements']) && is_array($data['eligibility_requirements'])) {
            $data['eligibility_requirements'] = json_encode($data['eligibility_requirements']);
        }

        $updateFields = [];
        $params = [];

        $allowedFields = [
            'category', 'title', 'description', 'processing_time',
            'fees', 'required_documents', 'eligibility_requirements',
            'is_active', 'display_order'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return true;
        }

        $params[] = $serviceId;
        $sql = "UPDATE service SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE service_id = ?";
        $stmt = $this->query($sql, $params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Activate/Deactivate service
     */
    public function toggleActive(int $serviceId, bool $isActive): bool
    {
        $sql = "UPDATE service SET is_active = ?, updated_at = NOW() WHERE service_id = ?";
        $stmt = $this->query($sql, [$isActive ? 1 : 0, $serviceId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update display order
     */
    public function updateDisplayOrder(int $serviceId, int $displayOrder): bool
    {
        $sql = "UPDATE service SET display_order = ?, updated_at = NOW() WHERE service_id = ?";
        $stmt = $this->query($sql, [$displayOrder, $serviceId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get service statistics
     */
    public function getServiceStats(): array
    {
        $stats = [];

        // Total services
        $stmt = $this->query("SELECT COUNT(*) as total FROM service");
        $stats['total'] = (int)$stmt->fetch()['total'];

        // Active services
        $stmt = $this->query("SELECT COUNT(*) as count FROM service WHERE is_active = 1");
        $stats['active'] = (int)$stmt->fetch()['count'];

        // By category
        $stmt = $this->query("SELECT category, COUNT(*) as count FROM service WHERE is_active = 1 GROUP BY category ORDER BY count DESC");
        $stats['by_category'] = $stmt->fetchAll();

        // Most booked services (based on appointments)
        $stmt = $this->query("
            SELECT 
                s.service_id,
                s.title,
                s.category,
                COUNT(a.appointment_id) as booking_count
            FROM service s
            LEFT JOIN appointment a ON s.service_id = a.booked_for_service
            WHERE s.is_active = 1
            GROUP BY s.service_id
            ORDER BY booking_count DESC
            LIMIT 10
        ");
        $stats['most_booked'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * Get service with appointment count
     */
    public function getServiceWithBookingCount(int $serviceId): ?array
    {
        $sql = "SELECT 
                    s.*,
                    COUNT(a.appointment_id) as total_bookings,
                    SUM(CASE WHEN a.appointment_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN a.appointment_status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_bookings
                FROM service s
                LEFT JOIN appointment a ON s.service_id = a.booked_for_service
                WHERE s.service_id = ?
                GROUP BY s.service_id";
        
        $stmt = $this->query($sql, [$serviceId]);
        $service = $stmt->fetch();

        if ($service) {
            // Decode JSON fields
            if (isset($service['fees'])) {
                $service['fees'] = json_decode($service['fees'], true);
            }
            if (isset($service['required_documents'])) {
                $service['required_documents'] = json_decode($service['required_documents'], true);
            }
            if (isset($service['eligibility_requirements'])) {
                $service['eligibility_requirements'] = json_decode($service['eligibility_requirements'], true);
            }
        }

        return $service ?: null;
    }

    /**
     * Get centers offering a specific service
     */
    public function getCentersOfferingService(int $serviceId): array
    {
        $sql = "SELECT 
                    vc.*,
                    COUNT(c.counter_id) as counter_count
                FROM verification_center vc
                LEFT JOIN counter c ON vc.center_id = c.center_id AND c.is_active = 1
                WHERE vc.is_active = 1
                AND JSON_CONTAINS(vc.provides_services, ?, '$')
                GROUP BY vc.center_id
                ORDER BY vc.display_order ASC, vc.name ASC";

        $stmt = $this->query($sql, [json_encode($serviceId)]);
        $centers = $stmt->fetchAll();

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

        return $centers;
    }

    /**
     * Search services
     */
    public function searchServices(string $searchTerm): array
    {
        $searchPattern = '%' . $searchTerm . '%';
        $sql = "SELECT * FROM service 
                WHERE is_active = 1
                AND (title LIKE ? OR description LIKE ? OR category LIKE ?)
                ORDER BY display_order ASC, title ASC";
        
        $stmt = $this->query($sql, [$searchPattern, $searchPattern, $searchPattern]);
        $services = $stmt->fetchAll();

        // Decode JSON fields
        foreach ($services as &$service) {
            if (isset($service['fees'])) {
                $service['fees'] = json_decode($service['fees'], true);
            }
            if (isset($service['required_documents'])) {
                $service['required_documents'] = json_decode($service['required_documents'], true);
            }
            if (isset($service['eligibility_requirements'])) {
                $service['eligibility_requirements'] = json_decode($service['eligibility_requirements'], true);
            }
        }

        return $services;
    }

    /**
     * Calculate service fees
     */
    public function calculateFees(int $serviceId, ?string $feeType = 'standard'): ?array
    {
        $service = $this->findByServiceId($serviceId);
        
        if (!$service || !isset($service['fees'])) {
            return null;
        }

        $fees = $service['fees'];
        
        // If fees is a simple object with amounts
        if (isset($fees[$feeType])) {
            return [
                'type' => $feeType,
                'amount' => $fees[$feeType],
                'currency' => 'USD'
            ];
        }

        // If fees is an array of fee objects
        if (is_array($fees) && isset($fees[0])) {
            return $fees[0];
        }

        return null;
    }
}