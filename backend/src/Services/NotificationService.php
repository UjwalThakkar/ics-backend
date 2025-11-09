<?php

declare(strict_types=1);

namespace IndianConsular\Services;

use PHPMailer\PHPMailer\PHPMailer;
use IndianConsular\Models\Notification;

class NotificationService
{
    private Notification $notificationModel;
    private array $mailConfig;

    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->mailConfig = [
            'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
            'port' => (int) ($_ENV['MAIL_PORT'] ?? 587),
            'username' => $_ENV['MAIL_USERNAME'] ?? '',
            'password' => $_ENV['MAIL_PASSWORD'] ?? '',
            'from' => $_ENV['MAIL_FROM'] ?? 'admin@consular.gov.in',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Indian Consular Services',
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls'
        ];
    }

    /**
     * Send application submitted confirmation
     */
    public function sendApplicationSubmitted(string $applicationId, string $email, string $name, string $serviceType): bool
    {
        $subject = 'Application Submitted Successfully';
        $content = "Dear {$name},\n\n";
        $content .= "Your application ({$applicationId}) for {$serviceType} has been submitted successfully.\n\n";
        $content .= "We will process your application and notify you of any updates.\n\n";
        $content .= "You can track your application status using your application ID: {$applicationId}\n\n";
        $content .= "Best regards,\nIndian Consular Services";

        return $this->sendEmail($email, $subject, $content, 'app_submitted', $applicationId);
    }

    /**
     * Send appointment confirmation
     */
    public function sendAppointmentConfirmation(
        string $appointmentId,
        string $email,
        string $name,
        string $centerName,
        string $counterNumber,
        string $date,
        string $time,
        string $serviceType
    ): bool {
        $subject = 'Appointment Confirmation';
        
        $content = "Dear {$name},\n\n";
        $content .= "Your appointment has been confirmed!\n\n";
        $content .= "Appointment Details:\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $content .= "Appointment ID: {$appointmentId}\n";
        $content .= "Service: {$serviceType}\n";
        $content .= "Date: {$date}\n";
        $content .= "Time: {$time}\n";
        $content .= "Location: {$centerName}\n";
        $content .= "Counter: {$counterNumber}\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $content .= "Important Instructions:\n";
        $content .= "• Please arrive 15 minutes before your appointment time\n";
        $content .= "• Bring all required documents\n";
        $content .= "• Bring a printed copy of this confirmation or your appointment ID\n";
        $content .= "• Wear appropriate attire for photo/biometric services\n\n";
        $content .= "To cancel or reschedule, please login to your account.\n\n";
        $content .= "Best regards,\nIndian Consular Services";

        return $this->sendEmail($email, $subject, $content, 'appointment_confirmed', null, $appointmentId);
    }

    /**
     * Send appointment cancellation
     */
    public function sendAppointmentCancellation(
        string $appointmentId,
        string $email,
        string $name,
        string $date,
        string $time
    ): bool {
        $subject = 'Appointment Cancelled';
        
        $content = "Dear {$name},\n\n";
        $content .= "Your appointment on {$date} at {$time} has been cancelled.\n\n";
        $content .= "Appointment ID: {$appointmentId}\n\n";
        $content .= "If you wish to book a new appointment, please visit our website.\n\n";
        $content .= "Best regards,\nIndian Consular Services";

        return $this->sendEmail($email, $subject, $content, 'appointment_cancelled', null, $appointmentId);
    }

    /**
     * Send appointment rescheduled notification
     */
    public function sendAppointmentRescheduled(
        string $appointmentId,
        string $email,
        string $name,
        string $newDate,
        string $newTime,
        string $centerName
    ): bool {
        $subject = 'Appointment Rescheduled';
        
        $content = "Dear {$name},\n\n";
        $content .= "Your appointment has been rescheduled.\n\n";
        $content .= "New Appointment Details:\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $content .= "Appointment ID: {$appointmentId}\n";
        $content .= "Date: {$newDate}\n";
        $content .= "Time: {$newTime}\n";
        $content .= "Location: {$centerName}\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $content .= "Please arrive 15 minutes before your appointment time.\n\n";
        $content .= "Best regards,\nIndian Consular Services";

        return $this->sendEmail($email, $subject, $content, 'appointment_rescheduled', null, $appointmentId);
    }

    /**
     * Send appointment reminder (to be run daily for next day appointments)
     */
    public function sendAppointmentReminder(
        string $appointmentId,
        string $email,
        string $name,
        string $date,
        string $time,
        string $centerName,
        string $counterNumber
    ): bool {
        $subject = 'Appointment Reminder - Tomorrow';
        
        $content = "Dear {$name},\n\n";
        $content .= "This is a reminder for your appointment tomorrow.\n\n";
        $content .= "Appointment Details:\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $content .= "Appointment ID: {$appointmentId}\n";
        $content .= "Date: {$date}\n";
        $content .= "Time: {$time}\n";
        $content .= "Location: {$centerName}\n";
        $content .= "Counter: {$counterNumber}\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $content .= "Important Reminders:\n";
        $content .= "• Arrive 15 minutes early\n";
        $content .= "• Bring all required documents\n";
        $content .= "• Bring your appointment confirmation\n\n";
        $content .= "We look forward to seeing you tomorrow!\n\n";
        $content .= "Best regards,\nIndian Consular Services";

        return $this->sendEmail($email, $subject, $content, 'appointment_reminder', null, $appointmentId);
    }

    /**
     * Send application status update
     */
    public function sendApplicationStatusUpdate(
        string $applicationId,
        string $email,
        string $name,
        string $newStatus
    ): bool {
        $subject = 'Application Status Update';
        
        $statusMessages = [
            'under-review' => 'Your application is now under review by our officers.',
            'in-progress' => 'Your application is being processed.',
            'ready-for-collection' => 'Your documents are ready for collection! Please visit the consulate during office hours.',
            'completed' => 'Your application has been completed successfully!',
            'rejected' => 'Unfortunately, your application has been rejected. Please contact us for more details.'
        ];

        $statusMessage = $statusMessages[$newStatus] ?? 'Your application status has been updated.';

        $content = "Dear {$name},\n\n";
        $content .= "Your application status has been updated.\n\n";
        $content .= "Application ID: {$applicationId}\n";
        $content .= "New Status: " . ucwords(str_replace('-', ' ', $newStatus)) . "\n\n";
        $content .= $statusMessage . "\n\n";
        $content .= "You can track your application status anytime using your application ID.\n\n";
        $content .= "Best regards,\nIndian Consular Services";

        return $this->sendEmail($email, $subject, $content, 'app_status_update', $applicationId);
    }

    /**
     * Send email notification
     */
    private function sendEmail(
        string $email,
        string $subject,
        string $content,
        string $templateId = '',
        ?string $applicationId = null,
        ?string $appointmentId = null
    ): bool {
        try {
            // Create notification record
            $notificationId = $this->generateNotificationId();
            $this->notificationModel->insert([
                'notification_id' => $notificationId,
                'type' => 'email',
                'recipient_email' => $email,
                'subject' => $subject,
                'content' => $content,
                'template_id' => $templateId,
                'application_id' => $applicationId,
                'appointment_id' => $appointmentId,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send email
            $sent = $this->sendMailWithPHPMailer($email, $subject, $content);

            // Update notification status
            $this->notificationModel->updateBy('notification_id', $notificationId, [
                'status' => $sent ? 'sent' : 'failed',
                'sent_at' => $sent ? date('Y-m-d H:i:s') : null,
                'error_message' => $sent ? null : 'Failed to send email'
            ]);

            return $sent;

        } catch (\Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using PHPMailer
     */
    private function sendMailWithPHPMailer(string $email, string $subject, string $content): bool
    {
        if (empty($this->mailConfig['username']) || empty($this->mailConfig['password'])) {
            error_log("Email not configured, skipping send to: {$email}");
            return true;
        }

        try {
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->mailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->mailConfig['username'];
            $mail->Password = $this->mailConfig['password'];
            $mail->SMTPSecure = $this->mailConfig['encryption'];
            $mail->Port = $this->mailConfig['port'];

            // Recipients
            $mail->setFrom($this->mailConfig['from'], $this->mailConfig['from_name']);
            $mail->addAddress($email);

            // Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $content;

            $mail->send();
            return true;

        } catch (\Exception $e) {
            error_log("PHPMailer error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique notification ID
     */
    private function generateNotificationId(): string
    {
        return 'NOT' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));
    }
}