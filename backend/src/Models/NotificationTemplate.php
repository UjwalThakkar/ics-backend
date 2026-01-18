<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class NotificationTemplate extends BaseModel
{
    protected string $table = 'notification_templates';
    protected string $primaryKey = 'id';

    /**
     * Get all notification templates
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY category, name";
        $stmt = $this->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get templates by type
     */
    public function getByType(string $type): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND is_active = 1 ORDER BY category, name";
        $stmt = $this->query($sql, [$type]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get template by ID
     */
    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Get template by template_id
     */
    public function findByTemplateId(string $templateId): ?array
    {
        return $this->findBy('template_id', $templateId);
    }

    /**
     * Update template
     */
    public function updateTemplate(int $id, array $data): bool
    {
        $allowedFields = ['name', 'subject', 'content', 'variables', 'is_active', 'category'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'variables' && is_array($data[$field])) {
                    $updateData[$field] = json_encode($data[$field]);
                } else {
                    $updateData[$field] = $data[$field];
                }
            }
        }

        if (empty($updateData)) {
            return false;
        }

        // Add updated_at
        $updateData['updated_at'] = date('Y-m-d H:i:s');

        return $this->update($id, $updateData);
    }

    /**
     * Create new template
     */
    public function createTemplate(array $data): ?int
    {
        $insertData = [
            'template_id' => $data['template_id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'category' => $data['category'] ?? null,
            'subject' => $data['subject'] ?? null,
            'content' => $data['content'],
            'variables' => isset($data['variables']) && is_array($data['variables']) 
                ? json_encode($data['variables']) 
                : null,
            'is_active' => $data['is_active'] ?? 1
        ];

        return $this->insert($insertData);
    }

    /**
     * Delete template
     */
    public function deleteTemplate(int $id): bool
    {
        return $this->delete($id);
    }
}

