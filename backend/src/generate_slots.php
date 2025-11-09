<?php
require_once 'vendor/autoload.php';

use IndianConsular\Models\AppointmentSlot;

// Initialize database
IndianConsular\Database\Connection::initialize();

$slotModel = new AppointmentSlot();

// Generate dates for next 30 days
$dates = [];
$start = new DateTime('2025-11-10');
for ($i = 0; $i < 30; $i++) {
    $dates[] = $start->format('Y-m-d');
    $start->modify('+1 day');
}

// Generate time slots (9 AM to 5 PM, 45 min each)
$timeSlots = AppointmentSlot::generateTimeSlots('09:00', '17:00', 45);

// Create slots for Counter A1
$created = $slotModel->bulkCreateSlots('CNT001', $dates, $timeSlots, 3);
echo "Created {$created} slots for Counter A1\n";

// Create slots for Counter A2
$created = $slotModel->bulkCreateSlots('CNT002', $dates, $timeSlots, 3);
echo "Created {$created} slots for Counter A2\n";

// Repeat for other counters...