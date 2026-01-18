<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\NotificationTemplate;

class NotificationTemplateController extends BaseController
{
    private NotificationTemplate $templateModel;

    public function __construct()
    {
        parent::__construct();
        $this->templateModel = new NotificationTemplate();
    }

    /**
     * List all notification templates (Admin)
     * GET /admin/notification-templates
     */
    public function adminList(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        try {
            $type = $data['type'] ?? null;
            $category = $data['category'] ?? null;

            if ($type) {
                $templates = $this->templateModel->getByType($type);
            } else {
                $templates = $this->templateModel->getAll();
            }

            // Filter by category if provided
            if ($category) {
                $templates = array_filter($templates, function($template) use ($category) {
                    return $template['category'] === $category;
                });
                $templates = array_values($templates); // Re-index array
            }

            // Parse variables JSON if present
            foreach ($templates as &$template) {
                if (!empty($template['variables'])) {
                    $template['variables'] = json_decode($template['variables'], true);
                }
            }

            return $this->success(['templates' => $templates]);
        } catch (\Exception $e) {
            error_log("Admin list templates error: " . $e->getMessage());
            return $this->error('Failed to load templates', 500);
        }
    }

    /**
     * Get single template (Admin)
     * GET /admin/notification-templates/{id}
     */
    public function adminGet(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            return $this->error('Template ID is required', 400);
        }

        try {
            $template = $this->templateModel->findById($id);
            if (!$template) {
                return $this->error('Template not found', 404);
            }

            // Parse variables JSON if present
            if (!empty($template['variables'])) {
                $template['variables'] = json_decode($template['variables'], true);
            }

            return $this->success(['template' => $template]);
        } catch (\Exception $e) {
            error_log("Admin get template error: " . $e->getMessage());
            return $this->error('Failed to load template', 500);
        }
    }

    /**
     * Update template (Admin)
     * PUT /admin/notification-templates/{id}
     */
    public function adminUpdate(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            return $this->error('Template ID is required', 400);
        }

        try {
            $template = $this->templateModel->findById($id);
            if (!$template) {
                return $this->error('Template not found', 404);
            }

            $data = $this->sanitize($data);

            // Validate required fields
            $updateData = [];
            
            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            
            if (isset($data['subject'])) {
                $updateData['subject'] = $data['subject'];
            }
            
            if (isset($data['content'])) {
                $updateData['content'] = $data['content'];
            }
            
            if (isset($data['category'])) {
                $updateData['category'] = $data['category'];
            }
            
            if (isset($data['is_active'])) {
                $updateData['is_active'] = (bool)$data['is_active'] ? 1 : 0;
            }
            
            if (isset($data['variables'])) {
                $updateData['variables'] = $data['variables'];
            }

            if (empty($updateData)) {
                return $this->error('No valid fields to update', 400);
            }

            $success = $this->templateModel->updateTemplate($id, $updateData);
            
            if (!$success) {
                return $this->error('Failed to update template', 500);
            }

            // Get updated template
            $updatedTemplate = $this->templateModel->findById($id);
            if (!empty($updatedTemplate['variables'])) {
                $updatedTemplate['variables'] = json_decode($updatedTemplate['variables'], true);
            }

            return $this->success([
                'message' => 'Template updated successfully',
                'template' => $updatedTemplate
            ]);
        } catch (\Exception $e) {
            error_log("Admin update template error: " . $e->getMessage());
            return $this->error('Failed to update template', 500);
        }
    }

    /**
     * Create new template (Admin)
     * POST /admin/notification-templates
     */
    public function adminCreate(array $data, array $params): array
    {
        $admin = $this->requireAdminAuth($data);
        if (!$admin) {
            return $this->error('Unauthorized', 401);
        }

        try {
            $data = $this->sanitize($data);

            // Validate required fields
            $missing = $this->validateRequired($data, ['template_id', 'name', 'type', 'content']);
            if (!empty($missing)) {
                return $this->error('Missing required fields: ' . implode(', ', $missing), 400);
            }

            // Check if template_id already exists
            $existing = $this->templateModel->findByTemplateId($data['template_id']);
            if ($existing) {
                return $this->error('Template ID already exists', 400);
            }

            // Validate type
            $validTypes = ['email', 'sms', 'push'];
            if (!in_array($data['type'], $validTypes)) {
                return $this->error('Invalid type. Must be one of: ' . implode(', ', $validTypes), 400);
            }

            $templateId = $this->templateModel->createTemplate($data);
            
            if (!$templateId) {
                return $this->error('Failed to create template', 500);
            }

            $template = $this->templateModel->findById($templateId);
            if (!empty($template['variables'])) {
                $template['variables'] = json_decode($template['variables'], true);
            }

            return $this->success([
                'message' => 'Template created successfully',
                'template' => $template
            ], 201);
        } catch (\Exception $e) {
            error_log("Admin create template error: " . $e->getMessage());
            return $this->error('Failed to create template', 500);
        }
    }
}

