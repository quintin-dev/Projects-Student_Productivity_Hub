-- Student Productivity Hub Database Schema
-- Version: 1.0
-- Date: June 10, 2025
-- Environment: Local Development (productivity_hub.local)

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS productivity_hub 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE productivity_hub;

-- =====================================================
-- TASKS TABLE - Core task management functionality
-- =====================================================
CREATE TABLE IF NOT EXISTS tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT UNSIGNED,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    is_deleted BOOLEAN DEFAULT FALSE,
    
    -- Indexing for performance
    INDEX idx_category (category_id),
    INDEX idx_priority (priority),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted (is_deleted)
) ENGINE=InnoDB;

-- =====================================================
-- CATEGORIES TABLE - Task categorization
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#3498db', -- Hex color code
    icon VARCHAR(50) DEFAULT 'folder',
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- =====================================================
-- STUDY SESSIONS TABLE - Pomodoro and study tracking
-- =====================================================
CREATE TABLE IF NOT EXISTS study_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED,
    session_type ENUM('pomodoro', 'focus', 'break') DEFAULT 'pomodoro',
    planned_duration INT UNSIGNED NOT NULL, -- Duration in minutes
    actual_duration INT UNSIGNED, -- Actual duration completed
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    notes TEXT,
    interruptions INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_task (task_id),
    INDEX idx_session_type (session_type),
    INDEX idx_start_time (start_time),
    INDEX idx_completed (is_completed),
    
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- HABITS TABLE - Habit tracking and streaks
-- =====================================================
CREATE TABLE IF NOT EXISTS habits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    frequency ENUM('daily', 'weekly', 'custom') DEFAULT 'daily',
    target_count INT UNSIGNED DEFAULT 1,
    current_streak INT UNSIGNED DEFAULT 0,
    best_streak INT UNSIGNED DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_frequency (frequency),
    INDEX idx_active (is_active),
    INDEX idx_streak (current_streak)
) ENGINE=InnoDB;

-- =====================================================
-- HABIT LOGS TABLE - Daily habit completion tracking
-- =====================================================
CREATE TABLE IF NOT EXISTS habit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    habit_id INT UNSIGNED NOT NULL,
    completion_date DATE NOT NULL,
    completed_count INT UNSIGNED DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_habit (habit_id),
    INDEX idx_date (completion_date),
    INDEX idx_completed (is_completed),
    UNIQUE KEY unique_habit_date (habit_id, completion_date),
    
    FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- SETTINGS TABLE - User preferences and app configuration
-- =====================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_user_configurable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key (setting_key),
    INDEX idx_user_configurable (is_user_configurable)
) ENGINE=InnoDB;

-- =====================================================
-- AUDIT LOGS TABLE - Track data changes for integrity
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT UNSIGNED NOT NULL,
    action ENUM('create', 'update', 'delete') NOT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_table (table_name),
    INDEX idx_record (record_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- FOREIGN KEY CONSTRAINTS
-- =====================================================
ALTER TABLE tasks 
ADD CONSTRAINT fk_tasks_category 
FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;

-- =====================================================
-- DEFAULT DATA INSERTION
-- =====================================================

-- Insert default categories
INSERT INTO categories (name, color, icon, description) VALUES
('Personal', '#e74c3c', 'user', 'Personal tasks and activities'),
('Study', '#3498db', 'book-open', 'Academic and learning tasks'),
('Work', '#2ecc71', 'briefcase', 'Professional and work-related tasks'),
('Health', '#f39c12', 'heart', 'Health, fitness, and wellness tasks'),
('Projects', '#9b59b6', 'folder-open', 'Long-term projects and goals')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_user_configurable) VALUES
('pomodoro_duration', '25', 'integer', 'Default Pomodoro session duration in minutes', TRUE),
('short_break_duration', '5', 'integer', 'Short break duration in minutes', TRUE),
('long_break_duration', '15', 'integer', 'Long break duration in minutes', TRUE),
('daily_goal_tasks', '5', 'integer', 'Default daily task completion goal', TRUE),
('theme_preference', 'light', 'string', 'User interface theme preference', TRUE),
('notification_enabled', 'true', 'boolean', 'Enable browser notifications', TRUE),
('auto_start_breaks', 'false', 'boolean', 'Automatically start break timers', TRUE),
('app_version', '1.0.0', 'string', 'Current application version', FALSE)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Insert sample habits for demonstration
INSERT INTO habits (name, description, frequency, target_count) VALUES
('Daily Reading', 'Read for at least 30 minutes every day', 'daily', 1),
('Exercise', 'Complete daily exercise or workout', 'daily', 1),
('Study Review', 'Review and organize study notes', 'daily', 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- =====================================================
-- PERFORMANCE OPTIMIZATION
-- =====================================================

-- Optimize tables for better performance
OPTIMIZE TABLE tasks, categories, study_sessions, habits, habit_logs, settings, audit_logs;

-- Update table statistics
ANALYZE TABLE tasks, categories, study_sessions, habits, habit_logs, settings, audit_logs;
