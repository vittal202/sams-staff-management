-- =============================================================================
-- master_schema.sql
-- SAMS Project - Unified Database Setup Script
-- Merges all previous SQL files into one clean, idempotent script.
-- Run this once on a fresh MySQL instance to fully set up the database.
-- Passwords (bcrypt): 'password' = $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- =============================================================================

-- =============================================================================
-- SECTION 1: DATABASE
-- =============================================================================
CREATE DATABASE IF NOT EXISTS `sams_rbac_backend`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `sams_rbac_backend`;

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- SECTION 2: CORE TABLES (Schema)
-- =============================================================================

-- 2.1 Roles
CREATE TABLE IF NOT EXISTS `roles` (
    `id`        INT AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.2 Departments
CREATE TABLE IF NOT EXISTS `departments` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `icon`        VARCHAR(50)  DEFAULT 'corporate_fare',
    `color_class` VARCHAR(50)  DEFAULT 'text-blue-600',
    `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.3 Users (complete final schema with all columns)
CREATE TABLE IF NOT EXISTS `users` (
    `id`                  BIGINT(20)   NOT NULL AUTO_INCREMENT,
    `username`            VARCHAR(255) NOT NULL,
    `full_name`           VARCHAR(100) NOT NULL,
    `email`               VARCHAR(100) NOT NULL UNIQUE,
    `password`            VARCHAR(255) NOT NULL,
    `role_id`             INT,
    `department_id`       INT,
    `manager_id`          BIGINT(20),
    `job_title`           VARCHAR(100),
    `avatar_url`          VARCHAR(255) DEFAULT NULL,
    `language`            VARCHAR(50)  DEFAULT 'English',
    `timezone`            VARCHAR(100) DEFAULT 'UTC',
    `theme`               VARCHAR(50)  DEFAULT 'light',
    `compact_view`        TINYINT(1)   DEFAULT 0,
    `email_notifications` TINYINT(1)   DEFAULT 1,
    `push_notifications`  TINYINT(1)   DEFAULT 1,
    `two_fa_enabled`      TINYINT(1)   DEFAULT 0,
    `created_at`          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    FOREIGN KEY (`role_id`)       REFERENCES `roles`(`id`)       ON DELETE SET NULL,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`manager_id`)    REFERENCES `users`(`id`)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.4 Permissions
CREATE TABLE IF NOT EXISTS `permissions` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `permission_name` VARCHAR(100) NOT NULL UNIQUE,
    `description`     TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.5 Role-Permissions Mapping
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id`       INT,
    `permission_id` INT,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`)       REFERENCES `roles`(`id`)       ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.6 Protected Pages
CREATE TABLE IF NOT EXISTS `protected_pages` (
    `id`               INT AUTO_INCREMENT PRIMARY KEY,
    `page_url`         VARCHAR(255) NOT NULL UNIQUE,
    `required_role_id` INT,
    FOREIGN KEY (`required_role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.7 Auth: Tokens (Remember Me / API)
CREATE TABLE IF NOT EXISTS `tokens` (
    `id`      BIGINT(20)   NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT(20)   NOT NULL,
    `token`   VARCHAR(255) NOT NULL,
    `expiry`  DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.8 Auth: Email OTPs (Password Reset)
CREATE TABLE IF NOT EXISTS `email_otps` (
    `id`         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(255) NOT NULL,
    `otp_hash`   VARCHAR(255) NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.9 Auth: User Sessions
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id`         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT(20)   NOT NULL,
    `session_id` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.10 Notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `type`       VARCHAR(50)  DEFAULT 'update' COMMENT 'update | success | warning | security',
    `title`      VARCHAR(255) NOT NULL,
    `message`    TEXT         NOT NULL,
    `is_read`    TINYINT(1)   DEFAULT 0,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- SECTION 3: SEED DATA - ROLES
-- =============================================================================
INSERT IGNORE INTO `roles` (`id`, `role_name`) VALUES
(1, 'Board Member'),
(2, 'CEO'),
(3, 'Manager'),
(4, 'Employee');

-- =============================================================================
-- SECTION 4: SEED DATA - DEPARTMENTS (with icons and colors)
-- =============================================================================
INSERT IGNORE INTO `departments` (`id`, `name`, `icon`, `color_class`) VALUES
( 1, 'Executive',           'corporate_fare',      'text-blue-600'),
( 2, 'Operations',          'inventory_2',         'text-emerald-600'),
( 3, 'Finance',             'payments',            'text-amber-600'),
( 4, 'Human Resources',     'group',               'text-indigo-600'),
( 5, 'Marketing',           'campaign',            'text-rose-600'),
( 6, 'Sales',               'sell',                'text-orange-600'),
( 7, 'Engineering',         'terminal',            'text-cyan-600'),
( 8, 'Product',             'inventory',           'text-violet-600'),
( 9, 'IT Support',          'support_agent',       'text-sky-600'),
(10, 'Legal',               'gavel',               'text-slate-600'),
(11, 'Research & Development','science',           'text-teal-600'),
(12, 'Customer Success',    'handshake',           'text-lime-600'),
(13, 'Logistics',           'local_shipping',      'text-orange-700'),
(14, 'Design',              'palette',             'text-pink-600'),
(15, 'Quality Assurance',   'verified',            'text-green-600'),
(16, 'Training',            'school',              'text-yellow-600'),
(17, 'Facilities',          'domain',              'text-gray-600'),
(18, 'Procurement',         'shopping_cart',       'text-blue-700'),
(19, 'Public Relations',    'public',              'text-purple-600'),
(20, 'Strategy',            'insights',            'text-emerald-700'),
(21, 'Compliance',          'rule',                'text-red-600'),
(22, 'Fundraising',         'volunteer_activism',  'text-rose-500'),
(23, 'Event Planning',      'event',               'text-orange-500'),
(24, 'Data Analytics',      'hub',                 'text-blue-500'),
(25, 'Internal Audit',      'fact_check',          'text-zinc-600');

-- =============================================================================
-- SECTION 5: SEED DATA - PROTECTED PAGES
-- =============================================================================
INSERT IGNORE INTO `protected_pages` (`page_url`, `required_role_id`) VALUES
('dashboard/board.php',    1),
('dashboard/ceo.php',      2),
('dashboard/manager.php',  3),
('dashboard/employee.php', 4);

-- =============================================================================
-- SECTION 6: SEED DATA - USERS
-- All passwords are bcrypt of 'password': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- =============================================================================

-- 6.1 CEO (single root of org chart) + System Admin account
-- CEO (id=2): role_id=2, no manager → root of the entire org chart
-- Admin (id=1): role_id=3, manager_id=2 → hidden system account, reports to CEO
INSERT IGNORE INTO `users` (`id`, `username`, `full_name`, `email`, `password`, `role_id`, `department_id`, `manager_id`, `job_title`) VALUES
(1, 'admin',    'Michael Scott',     'michael.scott@sams.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 2, 'Administrator'),
(2, 'alexandra','Alexandra Sterling','alexandra.sterling@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1, NULL, 'Chief Executive Officer');

-- 6.2 Directors (report to CEO id=2)
INSERT IGNORE INTO `users` (`username`, `full_name`, `email`, `password`, `role_id`, `department_id`, `manager_id`, `job_title`) VALUES
('michael.chen',    'Michael Chen',    'michael.chen@sams.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 2, 'Operations Director'),
('sarah.williams',  'Sarah Williams',  'sarah.williams@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 2, 2, 'HR Director'),
('david.martinez',  'David Martinez',  'david.martinez@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 4, 2, 'HR Director'),
('emily.johnson',   'Emily Johnson',   'emily.johnson@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 4, 2, 'HR Manager'),
('thomas.anderson', 'Thomas Anderson', 'thomas.anderson@sams.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 9, 2, 'IT Director'),
('jennifer.lee',    'Jennifer Lee',    'jennifer.lee@sams.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 9, 2, 'IT Manager'),
('christopher.brown','Christopher Brown','christopher.brown@sams.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 7, 2, 'Engineering Director'),
('amanda.davis',    'Amanda Davis',    'amanda.davis@sams.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 7, 2, 'Engineering Manager'),
('daniel.wilson',   'Daniel Wilson',   'daniel.wilson@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 3, 2, 'Finance Director'),
('jessica.taylor',  'Jessica Taylor',  'jessica.taylor@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 3, 2, 'Finance Manager'),
('matthew.garcia',  'Matthew Garcia',  'matthew.garcia@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 5, 2, 'Marketing Director'),
('ashley.rodriguez','Ashley Rodriguez','ashley.rodriguez@sams.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 5, 2, 'Marketing Manager'),
('andrew.miller',   'Andrew Miller',   'andrew.miller@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 6, 2, 'Sales Director'),
('michelle.white',  'Michelle White',  'michelle.white@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 6, 2, 'Sales Manager'),
('joshua.harris',   'Joshua Harris',   'joshua.harris@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 8, 2, 'Product Director'),
('nicole.clark',    'Nicole Clark',    'nicole.clark@sams.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 8, 2, 'Product Manager');

-- 6.3 Mid-level Managers (report to directors above by email lookup)
INSERT IGNORE INTO `users` (`username`, `full_name`, `email`, `password`, `role_id`, `department_id`, `manager_id`, `job_title`) VALUES
('ryan.thompson',    'Ryan Thompson',    'ryan.thompson@sams.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, (SELECT id FROM (SELECT id FROM users WHERE email='michael.chen@sams.com') t),    'Operations Manager'),
('lauren.scott',     'Lauren Scott',     'lauren.scott@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, (SELECT id FROM (SELECT id FROM users WHERE email='michael.chen@sams.com') t),     'Operations Manager'),
('brandon.king',     'Brandon King',     'brandon.king@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 4, (SELECT id FROM (SELECT id FROM users WHERE email='david.martinez@sams.com') t),    'HR Coordinator'),
('stephanie.green',  'Stephanie Green',  'stephanie.green@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 4, (SELECT id FROM (SELECT id FROM users WHERE email='emily.johnson@sams.com') t),     'HR Coordinator'),
('kevin.baker',      'Kevin Baker',      'kevin.baker@sams.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 9, (SELECT id FROM (SELECT id FROM users WHERE email='thomas.anderson@sams.com') t),   'IT Specialist'),
('rachel.adams',     'Rachel Adams',     'rachel.adams@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 9, (SELECT id FROM (SELECT id FROM users WHERE email='jennifer.lee@sams.com') t),      'IT Specialist'),
('justin.nelson',    'Justin Nelson',    'justin.nelson@sams.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 7, (SELECT id FROM (SELECT id FROM users WHERE email='christopher.brown@sams.com') t), 'Software Engineer'),
('megan.carter',     'Megan Carter',     'megan.carter@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 7, (SELECT id FROM (SELECT id FROM users WHERE email='amanda.davis@sams.com') t),     'Software Engineer'),
('tyler.mitchell',   'Tyler Mitchell',   'tyler.mitchell@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 3, (SELECT id FROM (SELECT id FROM users WHERE email='daniel.wilson@sams.com') t),    'Accountant'),
('samantha.perez',   'Samantha Perez',   'samantha.perez@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 3, (SELECT id FROM (SELECT id FROM users WHERE email='jessica.taylor@sams.com') t),   'Accountant'),
('nathan.roberts',   'Nathan Roberts',   'nathan.roberts@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 5, (SELECT id FROM (SELECT id FROM users WHERE email='matthew.garcia@sams.com') t),   'Marketing Specialist'),
('victoria.turner',  'Victoria Turner',  'victoria.turner@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 5, (SELECT id FROM (SELECT id FROM users WHERE email='ashley.rodriguez@sams.com') t), 'Marketing Specialist'),
('eric.phillips',    'Eric Phillips',    'eric.phillips@sams.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 6, (SELECT id FROM (SELECT id FROM users WHERE email='andrew.miller@sams.com') t),    'Salesperson'),
('brittany.campbell','Brittany Campbell','brittany.campbell@sams.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 6, (SELECT id FROM (SELECT id FROM users WHERE email='michelle.white@sams.com') t),   'Salesperson'),
('aaron.parker',     'Aaron Parker',     'aaron.parker@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 8, (SELECT id FROM (SELECT id FROM users WHERE email='joshua.harris@sams.com') t),    'Support Analyst'),
('kayla.evans',      'Kayla Evans',      'kayla.evans@sams.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 8, (SELECT id FROM (SELECT id FROM users WHERE email='nicole.clark@sams.com') t),     'Support Analyst');

-- 6.4 Team Members / Employees
INSERT IGNORE INTO `users` (`username`, `full_name`, `email`, `password`, `role_id`, `department_id`, `manager_id`, `job_title`) VALUES
-- Operations
('jacob.edwards',  'Jacob Edwards',  'jacob.edwards@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 1, (SELECT id FROM (SELECT id FROM users WHERE email='ryan.thompson@sams.com') t),  'Operations Staff'),
('olivia.collins', 'Olivia Collins', 'olivia.collins@sams.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 1, (SELECT id FROM (SELECT id FROM users WHERE email='ryan.thompson@sams.com') t),  'Operations Staff'),
('ethan.stewart',  'Ethan Stewart',  'ethan.stewart@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 1, (SELECT id FROM (SELECT id FROM users WHERE email='lauren.scott@sams.com') t),   'Operations Staff'),
('sophia.morris',  'Sophia Morris',  'sophia.morris@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 1, (SELECT id FROM (SELECT id FROM users WHERE email='lauren.scott@sams.com') t),   'Operations Staff'),
-- HR
('mason.rogers',   'Mason Rogers',   'mason.rogers@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 4, (SELECT id FROM (SELECT id FROM users WHERE email='brandon.king@sams.com') t),   'HR Coordinator'),
('isabella.reed',  'Isabella Reed',  'isabella.reed@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 4, (SELECT id FROM (SELECT id FROM users WHERE email='brandon.king@sams.com') t),   'HR Coordinator'),
('logan.cook',     'Logan Cook',     'logan.cook@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 4, (SELECT id FROM (SELECT id FROM users WHERE email='stephanie.green@sams.com') t), 'HR Coordinator'),
('ava.morgan',     'Ava Morgan',     'ava.morgan@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 4, (SELECT id FROM (SELECT id FROM users WHERE email='stephanie.green@sams.com') t), 'HR Coordinator'),
-- IT
('lucas.bell',     'Lucas Bell',     'lucas.bell@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 9, (SELECT id FROM (SELECT id FROM users WHERE email='kevin.baker@sams.com') t),    'IT Specialist'),
('emma.murphy',    'Emma Murphy',    'emma.murphy@sams.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 9, (SELECT id FROM (SELECT id FROM users WHERE email='kevin.baker@sams.com') t),    'IT Specialist'),
('aiden.bailey',   'Aiden Bailey',   'aiden.bailey@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 9, (SELECT id FROM (SELECT id FROM users WHERE email='rachel.adams@sams.com') t),   'IT Specialist'),
('mia.rivera',     'Mia Rivera',     'mia.rivera@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 9, (SELECT id FROM (SELECT id FROM users WHERE email='rachel.adams@sams.com') t),   'IT Specialist'),
-- Engineering
('jackson.cooper',        'Jackson Cooper',        'jackson.cooper@sams.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 7, (SELECT id FROM (SELECT id FROM users WHERE email='justin.nelson@sams.com') t),  'Software Engineer'),
('charlotte.richardson',  'Charlotte Richardson',  'charlotte.richardson@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 7, (SELECT id FROM (SELECT id FROM users WHERE email='justin.nelson@sams.com') t),  'Software Engineer'),
('liam.cox',              'Liam Cox',              'liam.cox@sams.com',              '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 7, (SELECT id FROM (SELECT id FROM users WHERE email='megan.carter@sams.com') t),   'Software Engineer'),
('amelia.howard',         'Amelia Howard',         'amelia.howard@sams.com',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 7, (SELECT id FROM (SELECT id FROM users WHERE email='megan.carter@sams.com') t),   'Software Engineer'),
-- Finance
('noah.ward',      'Noah Ward',      'noah.ward@sams.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 3, (SELECT id FROM (SELECT id FROM users WHERE email='tyler.mitchell@sams.com') t),  'Accountant'),
('harper.torres',  'Harper Torres',  'harper.torres@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 3, (SELECT id FROM (SELECT id FROM users WHERE email='tyler.mitchell@sams.com') t),  'Accountant'),
('carter.peterson','Carter Peterson','carter.peterson@sams.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 3, (SELECT id FROM (SELECT id FROM users WHERE email='samantha.perez@sams.com') t), 'Accountant'),
('ella.gray',      'Ella Gray',      'ella.gray@sams.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 3, (SELECT id FROM (SELECT id FROM users WHERE email='samantha.perez@sams.com') t), 'Accountant'),
-- Marketing
('wyatt.ramirez',  'Wyatt Ramirez',  'wyatt.ramirez@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 5, (SELECT id FROM (SELECT id FROM users WHERE email='nathan.roberts@sams.com') t),  'Marketing Specialist'),
('aria.james',     'Aria James',     'aria.james@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 5, (SELECT id FROM (SELECT id FROM users WHERE email='nathan.roberts@sams.com') t),  'Marketing Specialist'),
('grayson.watson', 'Grayson Watson', 'grayson.watson@sams.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 5, (SELECT id FROM (SELECT id FROM users WHERE email='victoria.turner@sams.com') t), 'Marketing Specialist'),
('scarlett.brooks','Scarlett Brooks','scarlett.brooks@sams.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 5, (SELECT id FROM (SELECT id FROM users WHERE email='victoria.turner@sams.com') t), 'Marketing Specialist'),
-- Sales
('luke.kelly',     'Luke Kelly',     'luke.kelly@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 6, (SELECT id FROM (SELECT id FROM users WHERE email='eric.phillips@sams.com') t),    'Salesperson'),
('chloe.sanders',  'Chloe Sanders',  'chloe.sanders@sams.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 6, (SELECT id FROM (SELECT id FROM users WHERE email='eric.phillips@sams.com') t),    'Salesperson'),
('owen.price',     'Owen Price',     'owen.price@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 6, (SELECT id FROM (SELECT id FROM users WHERE email='brittany.campbell@sams.com') t), 'Salesperson'),
('lily.bennett',   'Lily Bennett',   'lily.bennett@sams.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 6, (SELECT id FROM (SELECT id FROM users WHERE email='brittany.campbell@sams.com') t), 'Salesperson'),
-- Customer Support / Product
('caleb.wood',     'Caleb Wood',     'caleb.wood@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 8, (SELECT id FROM (SELECT id FROM users WHERE email='aaron.parker@sams.com') t),   'Support Analyst'),
('zoe.barnes',     'Zoe Barnes',     'zoe.barnes@sams.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 8, (SELECT id FROM (SELECT id FROM users WHERE email='aaron.parker@sams.com') t),   'Support Analyst'),
('elijah.ross',    'Elijah Ross',    'elijah.ross@sams.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 8, (SELECT id FROM (SELECT id FROM users WHERE email='kayla.evans@sams.com') t),    'Support Analyst'),
('grace.henderson','Grace Henderson','grace.henderson@sams.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 8, (SELECT id FROM (SELECT id FROM users WHERE email='kayla.evans@sams.com') t),    'Support Analyst'),
-- Additional Org Chart members (report to CEO)
('grace.lee',    'Grace Lee',    'grace.lee@company.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 4,  2, 'HR Coordinator'),
('david.kim',    'David Kim',    'david.kim@company.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 9,  2, 'IT Specialist'),
('lucas.gray',   'Lucas Gray',   'lucas.gray@company.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 9,  2, 'IT Specialist'),
('emily.rose',   'Emily Rose',   'emily.rose@company.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 16, 2, 'Trainer'),
('michael.scott','Michael Scott','michael.scott@company.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 25, 2, 'Auditor');

-- 6.5 Auto-generate usernames for any users missing them
UPDATE `users`
SET `username` = SUBSTRING_INDEX(`email`, '@', 1)
WHERE `username` = '' OR `username` IS NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- SECTION 7: VERIFICATION SUMMARY
-- =============================================================================
SELECT 'Database setup complete!' AS Status;
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_departments FROM departments;
SELECT r.role_name, COUNT(u.id) AS user_count
FROM roles r LEFT JOIN users u ON u.role_id = r.id
GROUP BY r.id, r.role_name;
