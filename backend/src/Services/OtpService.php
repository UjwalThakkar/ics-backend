<?php

declare(strict_types=1);

namespace IndianConsular\Services;

use IndianConsular\Models\Otp;
use IndianConsular\Services\NotificationService;

class OtpService
{
    private Otp $otpModel;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->otpModel = new Otp();
        $this->notificationService = new NotificationService();
    }

    /**
     * Generate a 6-digit OTP
     */
    private function generateOtp(): string
    {
        return str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send registration OTP to email
     */
    public function sendRegistrationOtp(string $email, string $firstName, string $lastName): array
    {
        try {
            // Normalize email (trim and lowercase)
            $email = trim(strtolower($email));

            error_log("OTP Send: Generating OTP for email: {$email}");

            // Invalidate any existing OTPs for this email
            $this->otpModel->invalidateAllForEmail($email, 'registration');

            // Generate new OTP
            $otpCode = $this->generateOtp();
            // Use UTC time for consistency with database
            $expiresAt = gmdate('Y-m-d H:i:s', time() + (10 * 60)); // 10 minutes expiry in UTC

            error_log("OTP Send: Generated OTP code: {$otpCode}, expires at: {$expiresAt} (UTC), PHP time: " . date('Y-m-d H:i:s') . ", UTC time: " . gmdate('Y-m-d H:i:s'));

            // Store OTP in database
            try {
                $otpId = $this->otpModel->insert([
                    'email' => $email,
                    'otp_code' => $otpCode,
                    'type' => 'registration',
                    'expires_at' => $expiresAt,
                    'is_used' => 0,
                    'created_at' => gmdate('Y-m-d H:i:s') // Use UTC for consistency
                ]);

                error_log("OTP Send: OTP stored in database with ID: {$otpId}");
            } catch (\Exception $dbE) {
                error_log("OTP Send: Database error - " . $dbE->getMessage());
                if (strpos($dbE->getMessage(), "doesn't exist") !== false || strpos($dbE->getMessage(), "Table") !== false) {
                    error_log("OTP Send: OTP table does not exist! Please run the migration: create_otp_table.sql");
                    return [
                        'success' => false,
                        'error' => 'Database table not found. Please contact administrator.'
                    ];
                }
                throw $dbE;
            }

            // Send OTP via email
            $subject = 'Email Verification Code - Indian Consular Services';
            $content = "Dear {$firstName} {$lastName},\n\n";
            $content .= "Thank you for registering with Indian Consular Services.\n\n";
            $content .= "Your email verification code is: {$otpCode}\n\n";
            $content .= "This code will expire in 10 minutes.\n\n";
            $content .= "If you did not request this code, please ignore this email.\n\n";
            $content .= "Best regards,\nIndian Consular Services";

            $sent = $this->notificationService->sendEmail($email, $subject, $content, 'registration_otp');

            if (!$sent) {
                error_log("Failed to send OTP email to: {$email}");
                // Still return success but log the issue
            }

            return [
                'success' => true,
                'otpId' => $otpId,
                'expiresAt' => $expiresAt,
                'emailSent' => $sent
            ];

        } catch (\Exception $e) {
            error_log("Send OTP error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP for registration
     */
    public function verifyRegistrationOtp(string $email, string $otpCode): array
    {
        try {
            // Normalize email (trim and lowercase)
            $email = trim(strtolower($email));
            $otpCode = trim($otpCode);

            error_log("OTP Verification: Looking for OTP with email: {$email}, code: {$otpCode}");

            $otp = $this->otpModel->findActiveOtp($email, 'registration');

            if (!$otp) {
                // Check if there are any OTPs for this email (for debugging)
                try {
                    $allOtps = $this->otpModel->findAll(['email' => $email, 'type' => 'registration'], 'created_at DESC', 5);
                    error_log("OTP Verification: Found " . count($allOtps) . " OTP records for email: {$email}");
                    if (!empty($allOtps)) {
                        error_log("OTP Verification: Latest OTP - is_used: " . ($allOtps[0]['is_used'] ?? 'N/A') . ", expires_at: " . ($allOtps[0]['expires_at'] ?? 'N/A'));
                    }
                } catch (\Exception $debugE) {
                    error_log("OTP Verification: Could not check existing OTPs: " . $debugE->getMessage());
                }

                return [
                    'success' => false,
                    'error' => 'No valid OTP found. Please request a new one.'
                ];
            }

            error_log("OTP Verification: Found OTP record - code: " . ($otp['otp_code'] ?? 'N/A') . ", expires_at: " . ($otp['expires_at'] ?? 'N/A'));

            // Check if OTP matches
            if ($otp['otp_code'] !== $otpCode) {
                error_log("OTP Verification: Code mismatch - expected: " . ($otp['otp_code'] ?? 'N/A') . ", received: {$otpCode}");
                return [
                    'success' => false,
                    'error' => 'Invalid OTP code. Please try again.'
                ];
            }

            // Mark OTP as used
            $this->otpModel->markAsUsed($otp['id']);

            return [
                'success' => true,
                'message' => 'OTP verified successfully'
            ];

        } catch (\Exception $e) {
            error_log("Verify OTP error: " . $e->getMessage());
            error_log("Verify OTP stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'error' => 'Failed to verify OTP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Regenerate and resend OTP
     */
    public function regenerateOtp(string $email, string $firstName, string $lastName): array
    {
        return $this->sendRegistrationOtp($email, $firstName, $lastName);
    }

    /**
     * Send password reset OTP to email
     */
    public function sendPasswordResetOtp(string $email, string $firstName, string $lastName): array
    {
        try {
            // Normalize email (trim and lowercase)
            $email = trim(strtolower($email));

            error_log("Password Reset OTP: Generating OTP for email: {$email}");

            // Invalidate any existing password reset OTPs for this email
            $this->otpModel->invalidateAllForEmail($email, 'password_reset');

            // Generate new OTP
            $otpCode = $this->generateOtp();
            // Use UTC time for consistency with database
            $expiresAt = gmdate('Y-m-d H:i:s', time() + (10 * 60)); // 10 minutes expiry in UTC

            error_log("Password Reset OTP: Generated OTP code: {$otpCode}, expires at: {$expiresAt} (UTC)");

            // Store OTP in database
            try {
                $otpId = $this->otpModel->insert([
                    'email' => $email,
                    'otp_code' => $otpCode,
                    'type' => 'password_reset',
                    'expires_at' => $expiresAt,
                    'is_used' => 0,
                    'created_at' => gmdate('Y-m-d H:i:s') // Use UTC for consistency
                ]);

                error_log("Password Reset OTP: OTP stored in database with ID: {$otpId}");
            } catch (\Exception $dbE) {
                error_log("Password Reset OTP: Database error - " . $dbE->getMessage());
                if (strpos($dbE->getMessage(), "doesn't exist") !== false || strpos($dbE->getMessage(), "Table") !== false) {
                    error_log("Password Reset OTP: OTP table does not exist!");
                    return [
                        'success' => false,
                        'error' => 'Database table not found. Please contact administrator.'
                    ];
                }
                throw $dbE;
            }

            // Send OTP via email
            $subject = 'Password Reset Code - Indian Consular Services';
            $content = "Dear {$firstName} {$lastName},\n\n";
            $content .= "You have requested to reset your password for Indian Consular Services.\n\n";
            $content .= "Your password reset code is: {$otpCode}\n\n";
            $content .= "This code will expire in 10 minutes.\n\n";
            $content .= "If you did not request this code, please ignore this email and your password will remain unchanged.\n\n";
            $content .= "Best regards,\nIndian Consular Services";

            $sent = $this->notificationService->sendEmail($email, $subject, $content, 'password_reset');

            if (!$sent) {
                error_log("Failed to send password reset OTP email to: {$email}");
                // Still return success but log the issue
            }

            return [
                'success' => true,
                'otpId' => $otpId,
                'expiresAt' => $expiresAt,
                'emailSent' => $sent
            ];

        } catch (\Exception $e) {
            error_log("Send password reset OTP error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP for password reset
     */
    public function verifyPasswordResetOtp(string $email, string $otpCode): array
    {
        try {
            // Normalize email (trim and lowercase)
            $email = trim(strtolower($email));
            $otpCode = trim($otpCode);

            error_log("Password Reset OTP Verification: Looking for OTP with email: {$email}, code: {$otpCode}");

            $otp = $this->otpModel->findActiveOtp($email, 'password_reset');

            if (!$otp) {
                return [
                    'success' => false,
                    'error' => 'No valid OTP found. Please request a new one.'
                ];
            }

            error_log("Password Reset OTP Verification: Found OTP record - code: " . ($otp['otp_code'] ?? 'N/A'));

            // Check if OTP matches
            if ($otp['otp_code'] !== $otpCode) {
                error_log("Password Reset OTP Verification: Code mismatch");
                return [
                    'success' => false,
                    'error' => 'Invalid OTP code. Please try again.'
                ];
            }

            // Mark OTP as used
            $this->otpModel->markAsUsed($otp['id']);

            return [
                'success' => true,
                'message' => 'OTP verified successfully'
            ];

        } catch (\Exception $e) {
            error_log("Verify password reset OTP error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to verify OTP: ' . $e->getMessage()
            ];
        }
    }
}

