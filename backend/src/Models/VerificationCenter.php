<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class VerificationCenter extends BaseModel
{
    protected string $table = 'verification_center';
    protected string $primaryKey = 'center_id';

    /**
     * Find center by center_id (integer primary key)
     */
    public function findByCenterId(int $centerId): ?array
    {
        return $this->find($centerId);
    }

    /**
     * Get all active centers
     */
    public function getActiveCenters(): array
    {
        return $this->findAll(['is_active' => 1], 'display_order ASC, name ASC');
    }

    /**
     * Get centers by city
     */
    public function getCentersByCity(string $city): array
    {
        return $this->findAll([
            'city' => $city,
            'is_active' => 1
        ], 'name ASC');
    }

    /**
     * Get centers by country
     */
    public function getCentersByCountry(string $country): array
    {
        return $this->findAll([
            'country' => $country,
            'is_active' => 1
        ], 'city ASC, name ASC');
    }

    /**
     * Get center with counters
     */
    public function getCenterWithCounters(int $centerId): ?array
    {
        $center = $this->findByCenterId($centerId);
        if (!$center) {
            return null;
        }

        // Get counters for this center
        $sql = "SELECT * FROM counter WHERE center_id = ? AND is_active = 1 ORDER BY counter_name";
        $stmt = $this->query($sql, [$centerId]);
        $counters = $stmt->fetchAll();

        // Decode JSON fields in counters
        foreach ($counters as &$counter) {
            if (isset($counter['service_handled'])) {
                $counter['service_handled'] = json_decode($counter['service_handled'], true);
            }
        }

        // Decode JSON fields in center
        if (isset($center['operating_hours'])) {
            $center['operating_hours'] = json_decode($center['operating_hours'], true);
        }
        if (isset($center['provides_services'])) {
            $center['provides_services'] = json_decode($center['provides_services'], true);
        }
        if (isset($center['has_counters'])) {
            $center['has_counters'] = json_decode($center['has_counters'], true);
        }

        $center['counters'] = $counters;

        return $center;
    }

    /**
     * Get center with services it provides
     */
    public function getCenterWithServices(int $centerId): ?array
    {
        $center = $this->findByCenterId($centerId);
        if (!$center) {
            return null;
        }

        // Decode JSON fields
        if (isset($center['operating_hours'])) {
            $center['operating_hours'] = json_decode($center['operating_hours'], true);
        }
        if (isset($center['provides_services'])) {
            $center['provides_services'] = json_decode($center['provides_services'], true);
        }
        if (isset($center['has_counters'])) {
            $center['has_counters'] = json_decode($center['has_counters'], true);
        }

        // Get the actual service details if provides_services exists
        if (!empty($center['provides_services'])) {
            $serviceIds = $center['provides_services'];
            $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
            $sql = "SELECT * FROM service WHERE service_id IN ($placeholders) AND is_active = 1 ORDER BY display_order ASC";
            $stmt = $this->query($sql, $serviceIds);
            $center['services'] = $stmt->fetchAll();

            // Decode JSON fields in services
            foreach ($center['services'] as &$service) {
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
        }

        return $center;
    }

    /**
     * Search centers by location (using coordinates)
     */
    public function searchNearby(float $latitude, float $longitude, float $radiusKm = 50): array
    {
        // Using Haversine formula to calculate distance
        $sql = "SELECT *,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )) AS distance
                FROM verification_center
                WHERE is_active = 1
                AND latitude IS NOT NULL
                AND longitude IS NOT NULL
                HAVING distance < ?
                ORDER BY distance ASC";

        $stmt = $this->query($sql, [$latitude, $longitude, $latitude, $radiusKm]);
        $centers = $stmt->fetchAll();

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

        return $centers;
    }

    /**
     * Get centers by state
     */
    public function getCentersByState(string $state): array
    {
        return $this->findAll([
            'state' => $state,
            'is_active' => 1
        ], 'city ASC, name ASC');
    }

    /**
     * Get center's available time slots for a specific date
     */
    public function getAvailableSlots(int $centerId, string $date): array
    {
        // Get all active time slots
        $sql = "SELECT ts.* FROM time_slots ts WHERE ts.is_active = 1 ORDER BY ts.start_time";
        $stmt = $this->query($sql);
        $allSlots = $stmt->fetchAll();

        // Get booked slots for this center and date
        $sql = "SELECT 
                    a.slot,
                    COUNT(*) as booking_count
                FROM appointment a
                INNER JOIN counter c ON a.at_counter = c.counter_id
                WHERE c.center_id = ?
                AND a.appointment_date = ?
                AND a.appointment_status = 'scheduled'
                GROUP BY a.slot";
        
        $stmt = $this->query($sql, [$centerId, $date]);
        $bookedSlots = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Get the maximum appointments per slot from system config
        $configSql = "SELECT config_value FROM system_config WHERE config_key = 'appointment_settings'";
        $configStmt = $this->query($configSql);
        $config = $configStmt->fetch();
        $appointmentSettings = json_decode($config['config_value'], true);
        $maxPerSlot = $appointmentSettings['max_appointments_per_slot'] ?? 3;

        // Mark slots as available or full
        foreach ($allSlots as &$slot) {
            $bookingCount = $bookedSlots[$slot['slot_id']] ?? 0;
            $slot['available_spots'] = max(0, $maxPerSlot - $bookingCount);
            $slot['is_available'] = $slot['available_spots'] > 0;
        }

        return $allSlots;
    }

    /**
     * Check if center provides a specific service
     */
    public function providesService(int $centerId, int $serviceId): bool
    {
        $center = $this->findByCenterId($centerId);
        if (!$center || !isset($center['provides_services'])) {
            return false;
        }

        $providedServices = json_decode($center['provides_services'], true);
        return in_array($serviceId, $providedServices);
    }

    /**
     * Get all centers with their counter count
     */
    public function getAllCentersWithCounterCount(): array
    {
        $sql = "SELECT 
                    vc.*,
                    COUNT(c.counter_id) as counter_count
                FROM verification_center vc
                LEFT JOIN counter c ON vc.center_id = c.center_id AND c.is_active = 1
                WHERE vc.is_active = 1
                GROUP BY vc.center_id
                ORDER BY vc.display_order ASC, vc.name ASC";
        
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Update center's operating hours
     */
    public function updateOperatingHours(int $centerId, array $operatingHours): bool
    {
        $json = json_encode($operatingHours);
        $sql = "UPDATE verification_center SET operating_hours = ?, updated_at = NOW() WHERE center_id = ?";
        $stmt = $this->query($sql, [$json, $centerId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update center's provided services
     */
    public function updateProvidedServices(int $centerId, array $serviceIds): bool
    {
        $json = json_encode($serviceIds);
        $sql = "UPDATE verification_center SET provides_services = ?, updated_at = NOW() WHERE center_id = ?";
        $stmt = $this->query($sql, [$json, $centerId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Activate/Deactivate a center
     */
    public function toggleActive(int $centerId, bool $isActive): bool
    {
        $sql = "UPDATE verification_center SET is_active = ?, updated_at = NOW() WHERE center_id = ?";
        $stmt = $this->query($sql, [$isActive ? 1 : 0, $centerId]);
        return $stmt->rowCount() > 0;
    }
}