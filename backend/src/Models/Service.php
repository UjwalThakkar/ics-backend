<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class Service extends BaseModel
{
    protected string $table = 'services';
    protected string $primaryKey = 'id';

    /**
     * Find service by service_id
     */
    public function findByServiceId(string $serviceId): ?array
    {
        return $this->findBy('service_id', $serviceId);
    }

    /**
     * Get all active services
     */
    public function getActiveServices(): array
    {
        return $this->findAll(['is_active' => 1], 'display_order ASC, title ASC');
    }

    /**
     * Get services by category
     */
    public function getServicesByCategory(string $category): array
    {
        return $this->findAll([
            'category' => $category,
            'is_active' => 1
        ], 'display_order ASC, title ASC');
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        $stmt = $this->query("SELECT DISTINCT category FROM services WHERE is_active = 1 ORDER BY category");
        return array_column($stmt->fetchAll(), 'category');
    }
}
