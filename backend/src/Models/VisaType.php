<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class VisaType extends BaseModel
{
    protected string $table      = 'visa_types';
    protected string $primaryKey = 'id';

    public function getByCountryId(int $countryId): array
    {
        $sql = "SELECT vt.*, vc.name as country_name, vc.slug as country_slug
                FROM visa_types vt
                LEFT JOIN visa_countries vc ON vt.country_id = vc.id
                WHERE vt.country_id = ? AND vt.is_active = 1
                ORDER BY vt.display_order ASC, vt.name ASC";

        $stmt = $this->query($sql, [$countryId]);
        $results = $stmt->fetchAll();

        foreach ($results as &$row) {
            $row['fees_json']     = json_decode($row['fees_json'] ?? '{}', true);
            $row['documents_json']= json_decode($row['documents_json'] ?? '[]', true);
        }

        return $results;
    }

    public function findBySlugAndCountry(string $slug, int $countryId): ?array
    {
        $sql = "SELECT vt.*, vc.name as country_name, vc.slug as country_slug
                FROM visa_types vt
                LEFT JOIN visa_countries vc ON vt.country_id = vc.id
                WHERE vt.slug = ? AND vt.country_id = ? AND vt.is_active = 1
                LIMIT 1";

        $stmt = $this->query($sql, [$slug, $countryId]);
        $row = $stmt->fetch();

        if ($row) {
            $row['fees_json']      = json_decode($row['fees_json'] ?? '{}', true);
            $row['documents_json'] = json_decode($row['documents_json'] ?? '[]', true);
        }

        return $row ?: null;
    }

    // Optional helper
    public function getAllActive(): array
    {
        $sql = "SELECT vt.*, vc.name as country_name, vc.slug as country_slug
                FROM visa_types vt
                JOIN visa_countries vc ON vt.country_id = vc.id
                WHERE vt.is_active = 1 AND vc.is_active = 1
                ORDER BY vc.name, vt.name";

        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
}