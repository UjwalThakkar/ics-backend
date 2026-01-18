<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class Otp extends BaseModel
{
    protected string $table = 'otp';
    protected string $primaryKey = 'id';

    /**
     * Find active OTP by email and type
     */
    public function findActiveOtp(string $email, string $type = 'registration'): ?array
    {
        // Normalize email (trim and lowercase)
        $email = trim(strtolower($email));
        
        try {
            // Use UTC_TIMESTAMP() to ensure consistent timezone comparison
            $sql = "SELECT *, UTC_TIMESTAMP() as current_utc_time FROM {$this->table} 
                    WHERE email = ? AND type = ? AND is_used = 0 AND expires_at > UTC_TIMESTAMP()
                    ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->query($sql, [$email, $type]);
            $result = $stmt->fetch();
            
            if ($result) {
                // Remove the helper field we added
                unset($result['current_utc_time']);
                error_log("Otp Model: Found active OTP - expires_at: " . ($result['expires_at'] ?? 'N/A') . ", current UTC: " . date('Y-m-d H:i:s'));
            } else {
                // Check what the current time is and what OTPs exist
                $checkSql = "SELECT id, email, expires_at, is_used, UTC_TIMESTAMP() as current_utc FROM {$this->table} 
                             WHERE email = ? AND type = ? 
                             ORDER BY created_at DESC LIMIT 1";
                $checkStmt = $this->query($checkSql, [$email, $type]);
                $checkResult = $checkStmt->fetch();
                if ($checkResult) {
                    error_log("Otp Model: Found OTP but expired or used - expires_at: " . ($checkResult['expires_at'] ?? 'N/A') . 
                             ", is_used: " . ($checkResult['is_used'] ?? 'N/A') . 
                             ", current UTC: " . ($checkResult['current_utc'] ?? 'N/A'));
                }
            }
            
            return $result ?: null;
        } catch (\Exception $e) {
            error_log("Otp Model: Error finding active OTP - " . $e->getMessage());
            // Check if table exists
            if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "Table") !== false) {
                error_log("Otp Model: OTP table does not exist! Please run the migration: create_otp_table.sql");
            }
            throw $e;
        }
    }

    /**
     * Mark OTP as used
     */
    public function markAsUsed(int $otpId): bool
    {
        return $this->update($otpId, [
            'is_used' => 1,
            'used_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Invalidate all OTPs for an email and type
     */
    public function invalidateAllForEmail(string $email, string $type = 'registration'): bool
    {
        // Normalize email (trim and lowercase)
        $email = trim(strtolower($email));
        
        try {
            $sql = "UPDATE {$this->table} SET is_used = 1 
                    WHERE email = ? AND type = ? AND is_used = 0";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$email, $type]);
        } catch (\Exception $e) {
            error_log("Otp Model: Error invalidating OTPs - " . $e->getMessage());
            // If table doesn't exist, return true (no OTPs to invalidate)
            if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "Table") !== false) {
                error_log("Otp Model: OTP table does not exist! Please run the migration: create_otp_table.sql");
                return true; // Return true to allow flow to continue
            }
            throw $e;
        }
    }

    /**
     * Clean up expired OTPs (older than 24 hours)
     */
    public function cleanupExpired(): int
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE expires_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }
}

