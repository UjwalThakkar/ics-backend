<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\AdminUser;
use IndianConsular\Models\User;
use IndianConsular\Services\AuthService;

class AuthController extends BaseController
{
    private AdminUser $adminUserModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->adminUserModel = new AdminUser();
        $this->userModel = new User();
    }

    /**
     * Register a new user
     */
    public function register(array $data, array $params): array
    {
        $data = $this->sanitize($data);

        // Validate required fields
        $requiredFields = ['email', 'password', 'first_name', 'last_name', 'phone'];
        $missing = $this->validateRequired($data, $requiredFields);
        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        try {
            // Check if email already exists
            if ($this->userModel->emailExists($data['email'])) {
                return $this->error('Email already registered', 409);
            }

            // Check if passport number already exists
            if ($this->userModel->passportNumberExists($data['passport_number'])) {
                return $this->error('Passport number already registered', 409);
            }

            // Prepare user data for creation
            $userData = [
                'email' => $data['email'],
                'password' => $data['password'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'nationality' => $data['nationality'] ?? null,
                'passport_number' => $data['passport_number'] ?? null,
            ];

            // Create the user
            $this->userModel->createUser($userData);

            // Fetch the newly created user
            $user = $this->userModel->findByEmail($data['email']);

            // Log successful registration
            $this->logService->logUserActivity(
                $user['user_id'],
                'USER_REGISTER_SUCCESS',
                ['email' => $data['email']],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->success([
                'user_id' => $user['user_id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'phone' => $user['phone'],
                'date_of_birth' => $user['date_of_birth'],
                'nationality' => $user['nationality'],
                'passport_number' => $user['passport_number'],
                'account_status' => $user['account_status'],
                'email_verified' => $user['email_verified'],
                'message' => 'User registered successfully. Please verify your email to activate your account.'
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
     * Login for admins and users
     */
    public function login(array $data, array $params): array
    {
        $data = $this->sanitize($data);

        // Validate required fields
        $missing = $this->validateRequired($data, ['username', 'password', 'type']);
        if (!empty($missing)) {
            return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
        }

        // Determine user type (admin or user)
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

    /**
     * Admin login logic
     */
    private function adminLogin(array $data): array
    {
        // Find admin user
        $admin = $this->adminUserModel->findByUsername($data['username']);

        if (!$admin) {
            // Log failed login attempt
            $this->logService->logAdminActivity(
                'UNKNOWN',
                'LOGIN_FAILED',
                ['username' => $data['username'], 'reason' => 'user_not_found'],
                $this->getClientIp(),
                $this->getUserAgent()
            );

            return $this->error('Invalid credentials', 401);
        }

        // Verify password
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

        // Update last login
        $this->userModel->updateLastLogin($user['user_id']);

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
     * Get current authenticated admin or user info
     */
    public function me(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);

        if (!$auth) {
            return $this->error('Unauthorized', 401);
        }

        if ($auth['type'] === 'admin') {
            $user = $this->adminUserModel->findByAdminId($auth['id']);
            if (!$user) {
                return $this->error('User not found', 404);
            }
            return $this->success([
                'type' => 'admin',
                'user' => [
                    'admin_id' => $user['admin_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role'],
                    'permissions' => json_decode($user['permissions'], true)
                ]
            ]);
        } else {
            $user = $this->userModel->findByUserId($auth['id']);
            if (!$user) {
                return $this->error('User not found', 404);
            }
            return $this->success([
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
    }

    /**
     * Logout (client-side token removal)
     */
    public function logout(array $data, array $params): array
    {
        $auth = $this->requireAuth($data);

        if ($auth) {
            if ($auth['type'] === 'admin') {
                $this->logService->logAdminActivity(
                    $auth['id'],
                    'LOGOUT',
                    [],
                    $this->getClientIp(),
                    $this->getUserAgent()
                );
            } else {
                $this->logService->logUserActivity(
                    $auth['id'],
                    'USER_LOGOUT',
                    [],
                    $this->getClientIp(),
                    $this->getUserAgent()
                );
            }
        }

        return $this->success([
            'message' => 'Logged out successfully'
        ]);
    }
}