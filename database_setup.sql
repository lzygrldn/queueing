-- Queueing System Database Setup
-- Run this after starting XAMPP MySQL

CREATE DATABASE IF NOT EXISTS queueing_system;
USE queueing_system;

-- Windows table
CREATE TABLE IF NOT EXISTS windows (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    window_number INT(11) UNSIGNED NOT NULL,
    window_name VARCHAR(50) NOT NULL,
    prefix VARCHAR(20) NOT NULL,
    current_number INT(11) UNSIGNED DEFAULT 0,
    last_released INT(11) UNSIGNED DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Queue table
CREATE TABLE IF NOT EXISTS queues (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    window_id INT(11) UNSIGNED NOT NULL,
    ticket_number VARCHAR(20) NOT NULL,
    queue_number INT(11) UNSIGNED NOT NULL,
    status ENUM('waiting', 'serving', 'completed', 'skipped') DEFAULT 'waiting',
    created_at DATETIME NULL,
    served_at DATETIME NULL,
    completed_at DATETIME NULL,
    FOREIGN KEY (window_id) REFERENCES windows(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Service records table for reports
CREATE TABLE IF NOT EXISTS service_records (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    window_id INT(11) UNSIGNED NOT NULL,
    ticket_number VARCHAR(20) NOT NULL,
    service_date DATE NOT NULL,
    service_type ENUM('completed', 'skipped') NOT NULL,
    created_at DATETIME NULL,
    FOREIGN KEY (window_id) REFERENCES windows(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default windows
INSERT INTO windows (window_number, window_name, prefix, current_number, last_released, status, created_at, updated_at) VALUES
(1, 'PSA', 'PSA', 0, 0, 'active', NOW(), NOW()),
(2, 'Birth Registration', 'BIRTH', 0, 0, 'active', NOW(), NOW()),
(3, 'Death Registration', 'DEATH', 0, 0, 'active', NOW(), NOW()),
(4, 'Marriage Registration', 'MARRIAGE', 0, 0, 'active', NOW(), NOW());
