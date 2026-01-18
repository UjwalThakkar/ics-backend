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
     * Send application approved notification using template
     */
    public function sendApplicationApproved(string $applicationId, string $email, string $name): bool
    {
        // Load template from database
        $template = $this->getTemplate('app_approved');
        
        if ($template) {
            // Use template with variable replacement
            $subject = $this->renderTemplate($template['subject'], [
                'applicant_name' => $name,
                'application_id' => $applicationId
            ]);
            
            $content = $this->renderTemplate($template['content'], [
                'applicant_name' => $name,
                'application_id' => $applicationId
            ]);
        } else {
            // Fallback to hardcoded content if template not found
            $subject = 'Application Approved';
            $content = "Dear {$name},\n\n";
            $content .= "Your application {$applicationId} has been approved.\n\n";
            $content .= "Best regards,\nIndian Consular Services";
        }

        return $this->sendEmail($email, $subject, $content, 'app_approved', $applicationId);
    }

    /**
     * Send application rejected notification using template
     */
    public function sendApplicationRejected(string $applicationId, string $email, string $name, string $adminNote): bool
    {
        // Load template from database
        $template = $this->getTemplate('app_rejected');
        
        if ($template) {
            // Use template with variable replacement
            // Note: Template uses {{admin_note}} (singular) as per database
            $subject = $this->renderTemplate($template['subject'], [
                'applicant_name' => $name,
                'application_id' => $applicationId,
                'admin_note' => $adminNote
            ]);
            
            $content = $this->renderTemplate($template['content'], [
                'applicant_name' => $name,
                'application_id' => $applicationId,
                'admin_note' => $adminNote
            ]);
        } else {
            // Fallback to hardcoded content if template not found
            $subject = 'Application Rejected !';
            $content = "Dear {$name},\n\n";
            $content .= "Your application {$applicationId} has been Rejected.\n\n";
            if (!empty($adminNote)) {
                $content .= "Reason: {$adminNote}\n\n";
            }
            $content .= "Best regards,\nIndian Consular Services";
        }

        return $this->sendEmail($email, $subject, $content, 'app_rejected', $applicationId);
    }

    /**
     * Send appointment confirmation using template from database
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
        // Load template from database
        $template = $this->getTemplate('appointment_confirmed');
        
        if ($template) {
            // Use template with variable replacement
            $subject = $this->renderTemplate($template['subject'], [
                'client_name' => $name,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'center_name' => $centerName,
                'counter_number' => $counterNumber,
                'appointment_id' => $appointmentId,
                'service_type' => $serviceType
            ]);
            
            $content = $this->renderTemplate($template['content'], [
                'client_name' => $name,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'center_name' => $centerName,
                'counter_number' => $counterNumber,
                'appointment_id' => $appointmentId,
                'service_type' => $serviceType
            ]);
        } else {
            // Fallback to hardcoded content if template not found
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
        }

        return $this->sendEmail($email, $subject, $content, 'appointment_confirmed', null, $appointmentId);
    }
    
    /**
     * Get template from database by template_id
     */
    private function getTemplate(string $templateId): ?array
    {
        try {
            $sql = "SELECT template_id, name, subject, content, variables, is_active 
                    FROM notification_templates 
                    WHERE template_id = ? AND is_active = 1 
                    LIMIT 1";
            
            $stmt = $this->notificationModel->query($sql, [$templateId]);
            $template = $stmt->fetch();
            
            return $template ?: null;
        } catch (\Exception $e) {
            error_log("Error loading template {$templateId}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Render template content by replacing variables
     */
    private function renderTemplate(string $template, array $variables): string
    {
        $rendered = $template;
        
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $rendered = str_replace($placeholder, $value, $rendered);
        }
        
        // Convert \r\n to actual newlines for proper email formatting
        $rendered = str_replace('\r\n', "\n", $rendered);
        $rendered = str_replace('\n', "\n", $rendered);
        
        return $rendered;
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
     * Send email notification (public method for general use)
     */
    public function sendEmail(
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