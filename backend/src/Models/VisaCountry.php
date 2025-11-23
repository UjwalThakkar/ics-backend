<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class VisaCountry extends BaseModel
{
    protected string $table      = 'visa_countries';
    protected string $primaryKey = 'id';

    public function getActiveCountries(): array
    {
        return $this->findAll(['is_active' => 1], 'display_order ASC, name ASC');
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    // Optional: for admin panel
    public function getAllWithVisaCount(): array
    {
        $sql = "SELECT vc.*, COUNT(vt.id) as visa_types_count
                FROM visa_countries vc
                LEFT JOIN visa_types vt ON vc.id = vt.country_id AND vt.is_active = 1
                WHERE vc.is_active = 1
                GROUP BY vc.id
                ORDER BY vc.display_order ASC, vc.name ASC";

        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
}