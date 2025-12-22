<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class ServiceDetails extends BaseModel
{
    protected string $table = 'service_details';
    protected string $primaryKey = 'service_id';

    /**
     * Find details by service_id
     */
    public function findByServiceId(int $serviceId): ?array
    {
        $details = $this->find($serviceId);
        
        // if ($details) {
        //     // Decode JSON fields
        //     if (isset($details['visa_fees'])) {
        //         $details['visa_fees'] = json_decode($details['visa_fees'], true);
        //     }
        //     if (isset($details['documents_required'])) {
        //         $details['documents_required'] = json_decode($details['documents_required'], true);
        //     }
        //     if (isset($details['downloads_form'])) {
        //         $details['downloads_form'] = json_decode($details['downloads_form'], true);
        //     }
        // }

        return $details;
    }

    /**
     * Get all service details (for admin)
     */
    public function getAllDetails(): array
    {
        $sql = "SELECT 
                    sd.service_id,
                    sd.overview,
                    sd.visa_fees,
                    sd.documents_required,
                    sd.photo_specifications,
                    sd.processing_time,
                    sd.downloads_form,
                    sd.created_at,
                    sd.updated_at,
                    s.title AS service_title
                FROM service_details sd
                LEFT JOIN service s ON sd.service_id = s.service_id
                ORDER BY sd.service_id ASC";
        
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Create new service details
     */
    public function createDetails(array $data): bool
    {
        // Encode JSON fields
        // if (isset($data['visa_fees']) && is_array($data['visa_fees'])) {
        //     $data['visa_fees'] = json_encode($data['visa_fees']);
        // }
        // if (isset($data['documents_required']) && is_array($data['documents_required'])) {
        //     $data['documents_required'] = json_encode($data['documents_required']);
        // }
        // if (isset($data['downloads_form']) && is_array($data['downloads_form'])) {
        //     $data['downloads_form'] = json_encode($data['downloads_form']);
        // }

        // Insert returns the last insert ID, but since PK is service_id (not auto-increment), we check rowCount
        $this->insert($data);
        return true; // Assuming success; you can check $this->db->rowCount() if needed
    }

    /**
     * Update service details
     */
    public function updateDetails(int $serviceId, array $data): bool
    {
        // Encode JSON fields if they are arrays
        if (isset($data['visa_fees']) && is_array($data['visa_fees'])) {
            $data['visa_fees'] = json_encode($data['visa_fees']);
        }
        if (isset($data['documents_required']) && is_array($data['documents_required'])) {
            $data['documents_required'] = json_encode($data['documents_required']);
        }
        if (isset($data['downloads_form']) && is_array($data['downloads_form'])) {
            $data['downloads_form'] = json_encode($data['downloads_form']);
        }

        return $this->update($serviceId, $data);
    }

    /**
     * Delete service details
     */
    public function deleteDetails(int $serviceId): bool
    {
        return $this->delete($serviceId);
    }
}