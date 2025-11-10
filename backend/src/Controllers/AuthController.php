<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\User;
use IndianConsular\Models\AdminUser;

class AuthController extends BaseController
{
    private User $userModel;
    private AdminUser $adminUserModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->adminUserModel = new AdminUser();
    }

    /**
     * Register a new user
     * POST /auth/register
     */
    public function register(array $data, array $params): array
    {
        $data = $this->sanitize($data);

        // Validate required fields
        $requiredFields = ['email', 'password', 'firstName', 'lastName'];
        $missing = $this->validateRequired($data, $requiredFields);
        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            // Check if email already exists
            if ($this->userModel->emailExists($data['email'])) {
                return $this->error('Email already registered', 409);
            }

            // Check if passport number already exists (if provided)
            if (!empty($data['passportNo']) && $this->userModel->passportNumberExists($data['passportNo'])) {
                return $this->error('Passport number already registered', 409);
            }

            // Prepare user data for creation
            $userData = [
                'email' => $data['email'],
                'password' => $data['password'],
                'first_name' => $data['firstName'],
                'last_name' => $data['lastName'],
                'phone_no' => $data['phoneNo'] ?? null,
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['dateOfBirth'] ?? null,
                'nationality' => $data['nationality'] ?? null,
                'passport_no' => $data['passportNo'] ?? null,
                'passport_expiry' => $data['passportExpiry'] ?? null,
                'account_status' => 'active',
                'email_validated' => 0
            ];

            // Create the user
            $userId = $this->userModel->createUser($userData);

            // Fetch the newly created user
            $user = $this->userModel->findByUserId($userId);

            // Log successful registration
            $this->logService->logUserActivity(
                (string)$userId,
                'USER_REGISTER_SUCCESS',
                ['email' => $data['email']],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->success([
                'userId' => $user['user_id'],
                'email' => $user['email'],
                'firstName' => $user['first_name'],
                'lastName' => $user['last_name'],
                'phoneNo' => $user['phone_no'],
                'accountStatus' => $user['account_status'],
                'emailValidated' => (bool)$user['email_validated'],
                'message' => 'User registered successfully. Please verify your email to complete registration.'
            ], 201);
        } catch (\Exception $e) {
            // Log failed registration attempt
            $this->logService->logUserActivity(
                'UNKNOWN',
                'USER_REGISTER_FAILED',
                ['email' => $data['email'] ?? 'unknown', 'reason' => $e->getMessage()],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            error_log("Registration error: " . $e->getMessage());
            return $this->error('Registration failed', 500);
        }
    }

    /**
     * User login
     * POST /auth/login
     */
    public function login(array $data, array $params): array
    {
        $data = $this->sanitize($data);

        // Validate required fields
        $missing = $this->validateRequired($data, ['email', 'password', 'type']);
        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        $type = strtolower($data['type']);
        if (!in_array($type, ['admin', 'user'])) {
            return $this->error('Invalid login type. Must be "admin" or "user".', 400);
        }


        try {

            if ($type === 'admin') {
                return $this->adminLogin($data);
            } else {
                return $this->userLogin($data);
            }
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return $this->error('Login failed', 500);
        }
    }

    private function adminLogin(array $data): array
    {
        $admin = $this->adminUserModel->findByUsername($data['email']);

        if (!$admin) {
            $this->logService->logAdminActivity(
                'UNKNOWN',
                'LOGIN_FAILED',
                ['username' => $data['username'], 'reason' => 'user_not_found'],
                $this->getClientIp(),
                $this->getUserAgent()
            );
            return $this->error('Invalid credentials', 401);
        }

        if (!password_verify($data['password'], $admin['password_hash'])) {
            // Log failed login attempt
            $this->logService->logAdminActivity(
                $admin['admin_id'],
                'LOGIN_FAILED',
                ['reason' => 'invalid_password'],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->error('Invalid credentials', 401);
        }

        // Check if account is active
        if (!$admin['is_active']) {
            return $this->error('Account is deactivated', 403);
        }

        // For demo purposes, skip 2FA if OTP is provided (any 6 digits)
        $skipTwoFactor = isset($data['otp']) && strlen($data['otp']) === 6;

        if (!$skipTwoFactor) {
            // Return 2FA required response
            return $this->success([
                'requiresTwoFactor' => true,
                'message' => 'Please provide 2FA code'
            ]);
        }

        // Generate JWT token
        $tokenData = [
            'id' => $admin['admin_id'],
            'type' => 'admin',
            'username' => $admin['username'],
            'email' => $admin['email'],
            'role' => $admin['role'],
            'permissions' => json_decode($admin['permissions'], true)
        ];

        $token = $this->authService->generateToken($tokenData);

        // Update last login
        $this->adminUserModel->updateLastLogin($admin['admin_id']);

        // Log successful login
        $this->logService->logAdminActivity(
            $admin['admin_id'],
            'LOGIN_SUCCESS',
            ['method' => $skipTwoFactor ? '2FA_completed' : 'password_only'],
            $this->getClientIp(),
            $this->getUserAgent()
        );

        return $this->success([
            'token' => $token,
            'type' => 'admin',
            'user' => [
                'admin_id' => $admin['admin_id'],
                'username' => $admin['username'],
                'email' => $admin['email'],
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name'],
                'role' => $admin['role'],
                'permissions' => json_decode($admin['permissions'], true)
            ]
        ]);
    }

    /**
     * User login logic
     */
    private function userLogin(array $data): array
    {
        // Find user by email (assuming username is email for users)
        $user = $this->userModel->findByEmail($data['username']);

        if (!$user) {
            // Log failed login attempt
            $this->logService->logUserActivity(
                'UNKNOWN',
                'USER_LOGIN_FAILED',
                ['email' => $data['username'], 'reason' => 'user_not_found'],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->error('Invalid credentials', 401);
        }

        // Verify password
        if (!password_verify($data['password'], $user['password_hash'])) {
            // Log failed login attempt
            $this->logService->logUserActivity(
                $user['user_id'],
                'USER_LOGIN_FAILED',
                ['reason' => 'invalid_password'],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->error('Invalid credentials', 401);
        }

        // Check if account is active
        if ($user['account_status'] !== 'active') {
            return $this->error('Account is not active', 403);
        }

        // Check if email is verified
        if (!$user['email_verified']) {
            return $this->error('Email not verified', 403);
        }

        // Generate JWT token
        $tokenData = [
            'id' => $user['user_id'],
            'type' => 'user',
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name']
        ];

        $token = $this->authService->generateToken($tokenData);


        // Log successful login
        $this->logService->logUserActivity(
            $user['user_id'],
            'USER_LOGIN_SUCCESS',
            ['method' => 'password'],
            $this->getClientIp(),
            $this->getUserAgent()
        );

        return $this->success([
            'token' => $token,
            'type' => 'user',
            'user' => [
                'user_id' => $user['user_id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'phone' => $user['phone'],
                'date_of_birth' => $user['date_of_birth'],
                'nationality' => $user['nationality'],
                'passport_number' => $user['passport_number'],
                'account_status' => $user['account_status']
            ]
        ]);
    }



    /**
     * Get current authenticated user info
     * GET /auth/me
     */
    public function me(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);

        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $user = $this->userModel->findByUserId((int)$auth['id']);
            if (!$user) {
                return $this->error('User not found', 404);
            }

            // Remove sensitive data
            unset($user['password_hash']);

            return $this->success([
                'type' => 'user',
                'user' => [
                    'userId' => $user['user_id'],
                    'email' => $user['email'],
                    'firstName' => $user['first_name'],
                    'lastName' => $user['last_name'],
                    'phoneNo' => $user['phone_no'],
                    'gender' => $user['gender'],
                    'dateOfBirth' => $user['date_of_birth'],
                    'nationality' => $user['nationality'],
                    'passportNo' => $user['passport_no'],
                    'passportExpiry' => $user['passport_expiry'],
                    'accountStatus' => $user['account_status'],
                    'emailValidated' => (bool)$user['email_validated']
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Get user info error: " . $e->getMessage());
            return $this->error('Failed to get user info', 500);
        }
    }

    /**
     * Update user profile
     * PUT /auth/profile
     */
    public function updateProfile(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);

        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        $data = $this->sanitize($data);

        try {
            $updateData = [];
            $allowedFields = [
                'firstName' => 'first_name',
                'lastName' => 'last_name',
                'phoneNo' => 'phone_no',
                'gender' => 'gender',
                'dateOfBirth' => 'date_of_birth',
                'nationality' => 'nationality',
                'passportNo' => 'passport_no',
                'passportExpiry' => 'passport_expiry'
            ];

            foreach ($allowedFields as $dataKey => $dbField) {
                if (isset($data[$dataKey])) {
                    $updateData[$dbField] = $data[$dataKey];
                }
            }

            if (empty($updateData)) {
                return $this->error('No valid fields to update', 400);
            }

            // Check for passport number uniqueness if updating
            if (
                isset($updateData['passport_no']) &&
                $this->userModel->passportNumberExists($updateData['passport_no'], (int)$auth['id'])
            ) {
                return $this->error('Passport number already in use', 409);
            }

            $success = $this->userModel->updateUser((int)$auth['id'], $updateData);

            if ($success) {
                // Log user activity
                $this->logService->logUserActivity(
                    (string)$auth['id'],
                    'PROFILE_UPDATE',
                    ['fields' => array_keys($updateData)],
                    $this->getClientIp(),
                    $this->getUserAgent()
                );

                return $this->success(['message' => 'Profile updated successfully']);
            }

            return $this->error('Failed to update profile', 500);
        } catch (\Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return $this->error('Failed to update profile', 500);
        }
    }

    /**
     * Change password
     * POST /auth/change-password
     */
    public function changePassword(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);

        if (!$auth || $auth['type'] !== 'user') {
            return $this->error('Unauthorized', 401);
        }

        $data = $this->sanitize($data);

        $missing = $this->validateRequired($data, ['currentPassword', 'newPassword']);
        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            $user = $this->userModel->findByUserId((int)$auth['id']);
            if (!$user) {
                return $this->error('User not found', 404);
            }

            // Verify current password
            if (!password_verify($data['currentPassword'], $user['password_hash'])) {
                return $this->error('Current password is incorrect', 401);
            }

            // Validate new password strength
            if (strlen($data['newPassword']) < 8) {
                return $this->error('New password must be at least 8 characters long', 400);
            }

            // Update password
            $success = $this->userModel->updatePassword((int)$auth['id'], $data['newPassword']);

            if ($success) {
                // Log user activity
                $this->logService->logUserActivity(
                    (string)$auth['id'],
                    'PASSWORD_CHANGE',
                    [],
                    $this->getClientIp(),
                    $this->getUserAgent()
                );

                return $this->success(['message' => 'Password changed successfully']);
            }

            return $this->error('Failed to change password', 500);
        } catch (\Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return $this->error('Failed to change password', 500);
        }
    }

    /**
     * Verify email
     * POST /auth/verify-email
     */
    public function verifyEmail(array $data, array $params): array
    {
        $data = $this->sanitize($data);

        $missing = $this->validateRequired($data, ['userId', 'token']);
        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            // In production, verify the token properly
            // For now, just verify the email
            $success = $this->userModel->verifyEmail((int)$data['userId']);

            if ($success) {
                // Log user activity
                $this->logService->logUserActivity(
                    $data['userId'],
                    'EMAIL_VERIFIED',
                    [],
                    $this->getClientIp(),
                    $this->getUserAgent()
                );

                return $this->success(['message' => 'Email verified successfully']);
            }

            return $this->error('Failed to verify email', 500);
        } catch (\Exception $e) {
            error_log("Verify email error: " . $e->getMessage());
            return $this->error('Failed to verify email', 500);
        }
    }

    /**
     * Logout (client-side token removal)
     * POST /auth/logout
     */
    public function logout(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);

        if ($auth && $auth['type'] === 'user') {
            $this->logService->logUserActivity(
                (string)$auth['id'],
                'USER_LOGOUT',
                [],
                $this->getClientIp(),
                $this->getUserAgent()
            );
        }

        return $this->success([
            'message' => 'Logged out successfully'
        ]);
    }
}
