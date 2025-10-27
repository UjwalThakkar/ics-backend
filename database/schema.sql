-- Indian Consular Services Database Schema
-- MySQL 8.0+ Compatible

CREATE DATABASE IF NOT EXISTS indian_consular_services
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE indian_consular_services;

-- =============================================
-- USERS AND AUTHENTICATION
-- =============================================

-- Regular users (applicants)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    nationality VARCHAR(100),
    passport_number VARCHAR(50),
    account_status ENUM('active', 'suspended', 'pending') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email (email),
    INDEX idx_user_id (user_id),
    INDEX idx_account_status (account_status)
);

-- Admin users
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'officer', 'supervisor') DEFAULT 'officer',
    permissions JSON,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    two_factor_secret VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- =============================================
-- SERVICES AND APPLICATIONS
-- =============================================

-- Consular services
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id VARCHAR(100) UNIQUE NOT NULL,
    category VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    processing_time VARCHAR(100),
    fees JSON, -- Array of fee structures
    required_documents JSON, -- Array of required documents
    eligibility_requirements JSON, -- Array of requirements
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_service_id (service_id),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
);

-- Applications
CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id VARCHAR(50) UNIQUE NOT NULL,
    user_id VARCHAR(50),
    service_id VARCHAR(100) NOT NULL,

    -- Applicant information
    applicant_info JSON NOT NULL,

    -- Application data
    form_data JSON,
    status ENUM('submitted', 'under-review', 'in-progress', 'ready-for-collection', 'completed', 'rejected') DEFAULT 'submitted',
    priority ENUM('normal', 'urgent', 'expedite') DEFAULT 'normal',

    -- Processing information
    assigned_officer VARCHAR(100),
    processing_notes TEXT,
    internal_notes TEXT,
    expected_completion_date DATE,

    -- Timestamps
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,

    -- References
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE RESTRICT,

    INDEX idx_application_id (application_id),
    INDEX idx_user_id (user_id),
    INDEX idx_service_id (service_id),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_assigned_officer (assigned_officer)
);

-- Application documents
CREATE TABLE application_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id VARCHAR(50) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    document_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,

    INDEX idx_application_id (application_id),
    INDEX idx_document_type (document_type)
);

-- =============================================
-- APPOINTMENTS AND SCHEDULING
-- =============================================

-- Appointments
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id VARCHAR(50) UNIQUE NOT NULL,
    application_id VARCHAR(50),

    -- Client information
    client_name VARCHAR(255) NOT NULL,
    client_email VARCHAR(255),
    client_phone VARCHAR(20),

    -- Appointment details
    service_type VARCHAR(255) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration_minutes INT DEFAULT 30,

    -- Status and notes
    status ENUM('scheduled', 'confirmed', 'in-progress', 'completed', 'cancelled', 'no-show') DEFAULT 'scheduled',
    notes TEXT,
    internal_notes TEXT,

    -- Assignment
    assigned_officer VARCHAR(100),
    created_by VARCHAR(100), -- admin who created it
    booking_type ENUM('online', 'manual', 'bulk') DEFAULT 'online',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE SET NULL,

    INDEX idx_appointment_id (appointment_id),
    INDEX idx_application_id (application_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_appointment_time (appointment_time),
    INDEX idx_status (status),
    INDEX idx_assigned_officer (assigned_officer),
    INDEX idx_date_time (appointment_date, appointment_time)
);

-- Appointment slots (for bulk scheduling)
CREATE TABLE appointment_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slot_date DATE NOT NULL,
    slot_time TIME NOT NULL,
    duration_minutes INT DEFAULT 30,
    service_type VARCHAR(255),
    max_appointments INT DEFAULT 1,
    current_appointments INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_slot_date (slot_date),
    INDEX idx_slot_time (slot_time),
    INDEX idx_service_type (service_type),
    INDEX idx_available (is_available),
    INDEX idx_date_time (slot_date, slot_time)
);

-- =============================================
-- NOTIFICATIONS AND COMMUNICATIONS
-- =============================================

-- Email notifications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('email', 'sms', 'push') DEFAULT 'email',
    recipient_email VARCHAR(255),
    recipient_phone VARCHAR(20),
    subject VARCHAR(255),
    content TEXT NOT NULL,
    template_id VARCHAR(100),

    -- Related records
    application_id VARCHAR(50),
    appointment_id VARCHAR(50),
    user_id VARCHAR(50),

    -- Status
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE SET NULL,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,

    INDEX idx_notification_id (notification_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_application_id (application_id),
    INDEX idx_appointment_id (appointment_id),
    INDEX idx_created_at (created_at)
);

-- Notification templates
CREATE TABLE notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('email', 'sms', 'push') NOT NULL,
    category VARCHAR(100),
    subject VARCHAR(255),
    content TEXT NOT NULL,
    variables JSON, -- Available variables for the template
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_template_id (template_id),
    INDEX idx_type (type),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
);

-- =============================================
-- ADMIN AND LOGGING
-- =============================================

-- Admin activity logs
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_id VARCHAR(50) UNIQUE NOT NULL,
    admin_id VARCHAR(50) NOT NULL,
    action VARCHAR(255) NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    affected_resource_type VARCHAR(100), -- 'application', 'appointment', 'user', etc.
    affected_resource_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_resource_type (affected_resource_type),
    INDEX idx_created_at (created_at)
);

-- System configuration
CREATE TABLE system_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(255) UNIQUE NOT NULL,
    config_value JSON NOT NULL,
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE, -- Whether this config can be accessed by frontend
    updated_by VARCHAR(100),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_config_key (config_key),
    INDEX idx_public (is_public)
);

-- =============================================
-- SAMPLE DATA INSERTS
-- =============================================

-- Insert default admin user
INSERT INTO admin_users (admin_id, username, email, password_hash, first_name, last_name, role, permissions, is_active) VALUES
('ADMIN001', 'officer123', 'admin@consular.gov.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', '["all"]', TRUE),
('OFF001', 'officer456', 'officer@consular.gov.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Consular', 'Officer', 'officer', '["applications", "appointments"]', TRUE);

-- Insert default services
INSERT INTO services (service_id, category, title, description, processing_time, fees, required_documents, is_active) VALUES
('passport-renewal', 'Passport Services', 'Passport Renewal (Expiry)', 'Renewal of expired Indian passport', '1 month', '[{"description":"Passport fee","amount":"100","currency":"USD"}]', '["Current passport","Application form","Photographs"]', TRUE),
('visa-application', 'Visa Services', 'Regular Visa Application', 'Application for Indian visa for foreign nationals', '7-15 days', '[{"description":"Visa fee","amount":"80","currency":"USD"}]', '["Passport","Application form","Photographs","Supporting documents"]', TRUE),
('oci-services', 'OCI Related Services', 'OCI Registration', 'Overseas Citizen of India registration', '8-12 weeks', '[{"description":"OCI fee","amount":"275","currency":"USD"}]', '["Passport","Birth certificate","Photos","Supporting documents"]', TRUE),
('pcc-indian', 'Police Clearance Certificate', 'PCC for Indian Nationals', 'Police Clearance Certificate for Indian citizens', '2-3 weeks', '[{"description":"PCC fee","amount":"60","currency":"USD"}]', '["Passport","Application form","Photos"]', TRUE),
('document-attestation', 'Document Attestation', 'Attestation of Documents/Degrees', 'Official attestation of educational and personal documents', '5-7 days', '[{"description":"Attestation fee","amount":"20","currency":"USD"}]', '["Original documents","Copies","Application form"]', TRUE);

-- Insert notification templates
INSERT INTO notification_templates (template_id, name, type, category, subject, content, is_active) VALUES
('app_submitted', 'Application Submitted', 'email', 'application', 'Application Submitted Successfully', 'Dear {{applicant_name}}, your application {{application_id}} has been submitted successfully.', TRUE),
('app_approved', 'Application Approved', 'email', 'application', 'Application Approved', 'Dear {{applicant_name}}, your application {{application_id}} has been approved.', TRUE),
('appointment_confirmed', 'Appointment Confirmed', 'email', 'appointment', 'Appointment Confirmation', 'Dear {{client_name}}, your appointment on {{appointment_date}} at {{appointment_time}} is confirmed.', TRUE);

-- Insert system configuration
INSERT INTO system_config (config_key, config_value, description, is_public) VALUES
('site_settings', '{"title":"Indian Consular Services","description":"Official portal for Indian consular services"}', 'General site settings', TRUE),
('office_hours', '{"monday":"09:00-17:00","tuesday":"09:00-17:00","wednesday":"09:00-17:00","thursday":"09:00-17:00","friday":"09:00-17:00","saturday":"09:00-13:00","sunday":"closed"}', 'Office operating hours', TRUE),
('contact_info', '{"phone":"+27 11 895 0460","email":"consular.johannesburg@mea.gov.in","address":"Consulate General of India, Johannesburg"}', 'Contact information', TRUE);
