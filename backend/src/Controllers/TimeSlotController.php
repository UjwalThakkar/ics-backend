<?php

declare(strict_types=1);

namespace IndianConsular\Controllers;

use IndianConsular\Models\TimeSlot;
use IndianConsular\Models\SystemConfig;

class TimeSlotController extends BaseController
{
    private TimeSlot $timeSlotModel;
    private SystemConfig $configModel;

    public function __construct()
    {
        parent::__construct();
        $this->timeSlotModel = new TimeSlot();
        $this->configModel = new SystemConfig();
    }

    // =============================================
    // ADMIN ENDPOINTS (Require Admin Auth)
    // =============================================

    /**
     * List all time slots + settings
     * GET /admin/time-slots
     */
    public function adminList(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        try {
            $page = (int)($data['page'] ?? 1);
            $limit = (int)($data['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            $slots = $this->timeSlotModel->findAll([], 'start_time ASC', $limit, $offset);
            $total = $this->timeSlotModel->count();

            $settings = $this->getAppointmentSettings();

            return $this->success([
                'slots' => $slots,
                'settings' => $settings,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Admin time slots list error: " . $e->getMessage());
            return $this->error('Failed to load time slots', 500);
        }
    }

    /**
     * Update appointment settings
     * PUT /admin/time-slots/settings
     */
    public function updateSettings(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $required = ['slot_duration_minutes', 'max_appointments_per_slot', 'advance_booking_days', 'cancellation_hours'];
        $missing = $this->validateRequired($data, $required);
        if (!empty($missing)) {
            return $this->error("Missing fields: " . implode(', ', $missing), 400);
        }

        $settings = [
            'slot_duration_minutes' => (int)$data['slot_duration_minutes'],
            'max_appointments_per_slot' => (int)$data['max_appointments_per_slot'],
            'advance_booking_days' => (int)$data['advance_booking_days'],
            'cancellation_hours' => (int)$data['cancellation_hours']
        ];

        try {
            $success = $this->configModel->updateConfig('appointment_settings', $settings);
            if ($success) {
                return $this->success(['message' => 'Settings updated successfully']);
            }
            return $this->error('Failed to update settings', 500);
        } catch (\Exception $e) {
            error_log("Update settings error: " . $e->getMessage());
            return $this->error('Failed to update settings', 500);
        }
    }

    /**
     * Toggle slot active status (single)
     * PUT /admin/time-slots/{id}/toggle
     */
    public function toggleSlot(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $slotId = (int)($params['id'] ?? 0);
        if (!$slotId) {
            return $this->error('Slot ID required', 400);
        }

        $slot = $this->timeSlotModel->find($slotId);
        if (!$slot) {
            return $this->error('Slot not found', 404);
        }

        $newStatus = !$slot['is_active'];
        $success = $this->timeSlotModel->toggleActive($slotId, $newStatus);

        if ($success) {
            return $this->success([
                'message' => $newStatus ? 'Slot activated' : 'Slot deactivated',
                'is_active' => $newStatus
            ]);
        }

        return $this->error('Failed to update slot', 500);
    }

    /**
     * Bulk toggle multiple slots
     * POST /admin/time-slots/bulk-toggle
     */
    public function bulkToggle(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $slotIds = $data['slot_ids'] ?? [];
        $activate = $data['activate'] ?? null;

        if (!is_array($slotIds) || empty($slotIds) || !is_bool($activate)) {
            return $this->error('Invalid request: slot_ids array and activate (bool) required', 400);
        }

        $this->timeSlotModel->beginTransaction();
        try {
            foreach ($slotIds as $id) {
                $this->timeSlotModel->toggleActive((int)$id, $activate);
            }
            $this->timeSlotModel->commit();
            return $this->success(['message' => count($slotIds) . ' slots updated']);
        } catch (\Exception $e) {
            $this->timeSlotModel->rollback();
            return $this->error('Bulk update failed', 500);
        }
    }

    /**
     * Create new time slot
     * POST /admin/time-slots
     */
    public function createSlot(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $required = ['start_time', 'end_time'];
        $missing = $this->validateRequired($data, $required);
        if (!empty($missing)) {
            return $this->error("Missing: " . implode(', ', $missing), 400);
        }

        if ($this->timeSlotModel->hasConflict($data['start_time'], $data['end_time'])) {
            return $this->error('Time slot conflicts with existing slot', 400);
        }

        $slotId = $this->timeSlotModel->createTimeSlot($data);
        if ($slotId) {
            return $this->success(['slot_id' => $slotId, 'message' => 'Slot created']);
        }

        return $this->error('Failed to create slot', 500);
    }

    /**
     * Update existing slot
     * PUT /admin/time-slots/{id}
     */
    public function updateSlot(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $slotId = (int)($params['id'] ?? 0);
        if (!$slotId) {
            return $this->error('Slot ID required', 400);
        }

        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            if ($this->timeSlotModel->hasConflict($data['start_time'], $data['end_time'], $slotId)) {
                return $this->error('Time conflict with another slot', 400);
            }
        }

        $success = $this->timeSlotModel->updateTimeSlot($slotId, $data);
        if ($success) {
            return $this->success(['message' => 'Slot updated']);
        }

        return $this->error('Failed to update slot', 500);
    }

    /**
     * Delete slot (only if no appointments)
     * DELETE /admin/time-slots/{id}
     */
    public function deleteSlot(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $slotId = (int)($params['id'] ?? 0);
        if (!$slotId) {
            return $this->error('Slot ID required', 400);
        }

        $result = $this->timeSlotModel->deleteTimeSlot($slotId);
        return $result['success']
            ? $this->success(['message' => $result['message']])
            : $this->error($result['message'], 400);
    }

    /**
     * Bulk generate time slots for a day
     * POST /admin/time-slots/bulk-create
     */
    public function bulkCreate(array $data, array $params): array
    {
        $admin = $this->requireAuth($data);
        if (!$admin || $admin['type'] !== 'admin') {
            return $this->error('Unauthorized', 401);
        }

        $required = ['start_time', 'end_time'];
        $missing = $this->validateRequired($data, $required);
        if (!empty($missing)) {
            return $this->error("Missing: " . implode(', ', $missing), 400);
        }

        // Use default duration if not provided
        $settings = $this->getAppointmentSettings();
        $duration = $data['duration'] ?? $settings['slot_duration_minutes'];

        if ($duration <= 0 || $duration > 240) {
            return $this->error('Duration must be 1-240 minutes', 400);
        }

        $start = $data['start_time'];
        $end = $data['end_time'];

        // Validate time format
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $end)) {
            return $this->error('Invalid time format. Use HH:MM:SS', 400);
        }

        if (strtotime($end) <= strtotime($start)) {
            return $this->error('End time must be after start time', 400);
        }

        try {
            $created = $this->timeSlotModel->bulkCreateSlots($start, $end, $duration);

            return $this->success([
                'message' => count($created) . ' slots created',
                'slots' => $created,
                'duration_used' => $duration
            ]);
        } catch (\Exception $e) {
            error_log("Bulk create error: " . $e->getMessage());
            return $this->error('Failed to create slots', 500);
        }
    }

    // =============================================
    // HELPER
    // =============================================
    private function getAppointmentSettings(): array
    {
        $config = $this->configModel->getConfig('appointment_settings');
        return $config ? json_decode($config['config_value'], true) : [
            'slot_duration_minutes' => 30,
            'max_appointments_per_slot' => 1,
            'advance_booking_days' => 30,
            'cancellation_hours' => 24
        ];
    }
}
