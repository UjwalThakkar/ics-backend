<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class ApplicationFile extends BaseModel
{
    protected string $table = 'application_files';
    protected string $primaryKey = 'id';

    /**
     * Find file by file_id
     */
    public function findByFileId(string $fileId): ?array
    {
        return $this->findBy('file_id', $fileId);
    }

    /**
     * Get all files for an application
     */
    public function getFilesByApplicationId(string $applicationId): array
    {
        return $this->findAll(['application_id' => $applicationId], 'uploaded_at ASC');
    }

    /**
     * Get files by document type
     */
    public function getFilesByDocumentType(string $applicationId, string $documentType): array
    {
        $sql = "SELECT * FROM application_files 
                WHERE application_id = ? AND document_type = ?
                ORDER BY uploaded_at ASC";
        $stmt = $this->query($sql, [$applicationId, $documentType]);
        return $stmt->fetchAll();
    }

    /**
     * Delete file by file_id
     */
    public function deleteByFileId(string $fileId): bool
    {
        return $this->deleteBy('file_id', $fileId);
    }

    /**
     * Get total file size for an application
     */
    public function getTotalSizeForApplication(string $applicationId): int
    {
        $sql = "SELECT SUM(file_size) as total_size 
                FROM application_files 
                WHERE application_id = ?";
        $stmt = $this->query($sql, [$applicationId]);
        $result = $stmt->fetch();
        return (int) ($result['total_size'] ?? 0);
    }

    /**
     * Get file count for an application
     */
    public function getFileCountForApplication(string $applicationId): int
    {
        return $this->count(['application_id' => $applicationId]);
    }
}

