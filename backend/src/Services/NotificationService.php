<?php

declare(strict_types=1);

namespace IndianConsular\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
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
        $content = "Dear {$name},\n\nYour application ({$applicationId}) for {$serviceType} has been submitted successfully.\n\nWe will process your application and notify you of any updates.\n\nBest regards,\nIndian Consular Services";

        return $this->sendEmail($email, $subject, $content, 'app_submitted', $applicationId);
    }

    /**
     * Send appointment confirmation
     */
    public function sendAppointmentConfirmed(string $appointmentId, string $email, string $name, string $date, string $time, string $serviceType): bool
    {
        $subject = 'Appointment Confirmation';
        $content = "Dear {$name},\n\nYour appointment ({$appointmentId}) has been confirmed:\n\nService: {$serviceType}\nDate: {$date}\nTime: {$time}\n\nPlease arrive 15 minutes before your appointment time.\n\nBest regards,\nIndian Consular Services";

        return $this->sendEmail($email, $subject, $content, 'appointment_confirmed', null, $appointmentId);
    }

    /**
     * Send email notification
     */
    public function sendEmail(string $email, string $subject, string $content, string $templateId = '', string $applicationId = null, string $appointmentId = null): bool
    {
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
            // Skip email sending if not configured
            error_log("Email not configured, skipping send to: {$email}");
            return true; // Return true to not fail the application flow
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
