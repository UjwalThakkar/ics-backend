<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class VisaDownload extends BaseModel
{
    protected string $table      = 'visa_downloads';
    protected string $primaryKey = 'id';

    /**
     * Get all downloads for a specific visa type
     */
    public function getByVisaTypeId(int $visaTypeId): array
    {
        $sql = "SELECT 
                    id,
                    visa_type_id,
                    title,
                    file_url,
                    file_size_kb,
                    is_checklist,
                    display_order,
                    created_at,
                    updated_at
                FROM visa_downloads 
                WHERE visa_type_id = ? 
                AND is_active = 1
                ORDER BY display_order ASC, id ASC";

        $stmt = $this->query($sql, [$visaTypeId]);
        return $stmt->fetchAll();
    }

    /**
     * Admin: Get all downloads (with pagination optional)
     */
    public function getAllAdmin(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT vd.*, vt.name as visa_type_name, vc.name as country_name
                FROM visa_downloads vd
                LEFT JOIN visa_types vt ON vd.visa_type_id = vt.id
                LEFT JOIN visa_countries vc ON vt.country_id = vc.id
                ORDER BY vd.id DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->query($sql, [$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Create new download link
     */
    public function create(array $data): int
    {
        $insertData = [
            'visa_type_id'   => $data['visa_type_id'],
            'title'          => $data['title'],
            'file_url'       => $data['file_url'],
            'file_size_kb'   => $data['file_size_kb'] ?? null,
            'is_checklist'   => $data['is_checklist'] ?? 0,
            'display_order'  => $data['display_order'] ?? 0,
            'is_active'      => $data['is_active'] ?? 1
        ];

        return $this->insert($insertData);
    }

    /**
     * Update existing download
     */
    public function update(int $id, array $data): bool
    {
        $updateData = [];

        if (isset($data['title']))        $updateData['title'] = $data['title'];
        if (isset($data['file_url']))     $updateData['file_url'] = $data['file_url'];
        if (isset($data['file_size_kb'])) $updateData['file_size_kb'] = $data['file_size_kb'];
        if (isset($data['is_checklist'])) $updateData['is_checklist'] = $data['is_checklist'];
        if (isset($data['display_order'])) $updateData['display_order'] = $data['display_order'];
        if (isset($data['is_active']))    $updateData['is_active'] = $data['is_active'];

        if (empty($updateData)) {
            return true;
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $updateData);
    }

    /**
     * Soft delete (just mark inactive)
     */
    public function softDelete(int $id): bool
    {
        return $this->update($id, ['is_active' => 0]);
    }
}