-- ============================================================
-- WarmUp — Gym Management System
-- Complete Database Schema (warmup.sql)
-- Version: 1.2 | FYP Edition
-- Compatible with: MySQL 8.0+ / MariaDB 10.4+
-- Import via: phpMyAdmin > Import > Select this file
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- ============================================================
-- CREATE DATABASE
-- ============================================================

CREATE DATABASE IF NOT EXISTS `warmup`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `warmup`;

-- ============================================================
-- TABLE 1: users
-- Purpose: Gym Owner authentication and profile.
-- Single user in V1. Extensible for multi-role in V2.
-- v1.2: Added is_active, last_login_at
-- ============================================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id`                 BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`               VARCHAR(100)        NOT NULL,
    `email`              VARCHAR(150)        NOT NULL,
    `password`           VARCHAR(255)        NOT NULL,
    `phone`              VARCHAR(20)         NULL DEFAULT NULL,
    `profile_photo`      VARCHAR(255)        NULL DEFAULT NULL,
    `is_active`          TINYINT(1)          NOT NULL DEFAULT 1,
    `last_login_at`      TIMESTAMP           NULL DEFAULT NULL,
    `email_verified_at`  TIMESTAMP           NULL DEFAULT NULL,
    `remember_token`     VARCHAR(100)        NULL DEFAULT NULL,
    `created_at`         TIMESTAMP           NULL DEFAULT NULL,
    `updated_at`         TIMESTAMP           NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 2: gym_settings
-- Purpose: Single-row config table for gym branding and locale.
-- V2: Becomes `gyms` with gym_id FK on all entities.
-- v1.2: Added country, city, currency_symbol
-- ============================================================

DROP TABLE IF EXISTS `gym_settings`;
CREATE TABLE `gym_settings` (
    `id`               BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `gym_name`         VARCHAR(150)        NOT NULL,
    `gym_logo`         VARCHAR(255)        NULL DEFAULT NULL,
    `owner_name`       VARCHAR(100)        NOT NULL,
    `contact_email`    VARCHAR(150)        NULL DEFAULT NULL,
    `contact_phone`    VARCHAR(20)         NULL DEFAULT NULL,
    `address`          TEXT                NULL DEFAULT NULL,
    `country`          VARCHAR(100)        NOT NULL DEFAULT 'Pakistan',
    `city`             VARCHAR(100)        NULL DEFAULT NULL,
    `currency`         VARCHAR(10)         NOT NULL DEFAULT 'PKR',
    `currency_symbol`  VARCHAR(10)         NOT NULL DEFAULT 'Rs',
    `timezone`         VARCHAR(60)         NOT NULL DEFAULT 'Asia/Karachi',
    `language`         VARCHAR(10)         NOT NULL DEFAULT 'en',
    `theme`            VARCHAR(20)         NOT NULL DEFAULT 'light',
    `date_format`      VARCHAR(20)         NOT NULL DEFAULT 'd/m/Y',
    `time_format`      VARCHAR(10)         NOT NULL DEFAULT '12h',
    `created_at`       TIMESTAMP           NULL DEFAULT NULL,
    `updated_at`       TIMESTAMP           NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 3: membership_plans
-- Purpose: Reusable plan templates (Monthly, Quarterly, Yearly).
-- v1.2: Added color, sort_order
-- ============================================================

DROP TABLE IF EXISTS `membership_plans`;
CREATE TABLE `membership_plans` (
    `id`             BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(100)        NOT NULL,
    `duration_days`  SMALLINT UNSIGNED   NOT NULL,
    `price`          DECIMAL(10,2)       NOT NULL,
    `description`    TEXT                NULL DEFAULT NULL,
    `color`          VARCHAR(20)         NULL DEFAULT NULL,
    `sort_order`     INT                 NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1)          NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP           NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP           NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 4: trainers
-- Purpose: Gym trainers and staff records.
-- No login access in V1. Managed by the Gym Owner.
-- v1.2: Added gender, date_of_birth, address
-- ============================================================

DROP TABLE IF EXISTS `trainers`;
CREATE TABLE `trainers` (
    `id`               BIGINT UNSIGNED                  NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(100)                     NOT NULL,
    `email`            VARCHAR(150)                     NULL DEFAULT NULL,
    `phone`            VARCHAR(20)                      NOT NULL,
    `gender`           ENUM('male','female','other')    NOT NULL,
    `date_of_birth`    DATE                             NULL DEFAULT NULL,
    `specialization`   VARCHAR(100)                     NULL DEFAULT NULL,
    `bio`              TEXT                             NULL DEFAULT NULL,
    `address`          TEXT                             NULL DEFAULT NULL,
    `profile_photo`    VARCHAR(255)                     NULL DEFAULT NULL,
    `salary`           DECIMAL(10,2)                    NULL DEFAULT NULL,
    `joining_date`     DATE                             NOT NULL,
    `is_active`        TINYINT(1)                       NOT NULL DEFAULT 1,
    `created_at`       TIMESTAMP                        NULL DEFAULT NULL,
    `updated_at`       TIMESTAMP                        NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 5: members
-- Purpose: Core entity — gym member records with full profile.
-- Uses soft deletes to preserve historical data.
-- v1.2: Added height, weight, blood_group
-- ============================================================

DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
    `id`                       BIGINT UNSIGNED                              NOT NULL AUTO_INCREMENT,
    `trainer_id`               BIGINT UNSIGNED                              NULL DEFAULT NULL,
    `name`                     VARCHAR(100)                                 NOT NULL,
    `email`                    VARCHAR(150)                                 NULL DEFAULT NULL,
    `phone`                    VARCHAR(20)                                  NOT NULL,
    `date_of_birth`            DATE                                         NULL DEFAULT NULL,
    `gender`                   ENUM('male','female','other')                NOT NULL,
    `profile_photo`            VARCHAR(255)                                 NULL DEFAULT NULL,
    `address`                  TEXT                                         NULL DEFAULT NULL,
    `emergency_contact_name`   VARCHAR(100)                                 NULL DEFAULT NULL,
    `emergency_contact_phone`  VARCHAR(20)                                  NULL DEFAULT NULL,
    `medical_notes`            TEXT                                         NULL DEFAULT NULL,
    `height`                   DECIMAL(5,2)                                 NULL DEFAULT NULL,
    `weight`                   DECIMAL(5,2)                                 NULL DEFAULT NULL,
    `blood_group`              VARCHAR(10)                                  NULL DEFAULT NULL,
    `joining_date`             DATE                                         NOT NULL,
    `status`                   ENUM('active','expired','expiring_soon','suspended') NOT NULL DEFAULT 'active',
    `created_at`               TIMESTAMP                                    NULL DEFAULT NULL,
    `updated_at`               TIMESTAMP                                    NULL DEFAULT NULL,
    `deleted_at`               TIMESTAMP                                    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `members_trainer_id_index` (`trainer_id`),
    KEY `members_status_index`     (`status`),
    KEY `members_phone_index`      (`phone`),
    CONSTRAINT `members_trainer_id_foreign`
        FOREIGN KEY (`trainer_id`)
        REFERENCES `trainers` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 6: member_memberships
-- Purpose: Each row = one membership period for a member.
-- Tracks plan assignment, start/end dates, and payment status.
-- (No changes in v1.2)
-- ============================================================

DROP TABLE IF EXISTS `member_memberships`;
CREATE TABLE `member_memberships` (
    `id`                  BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `member_id`           BIGINT UNSIGNED     NOT NULL,
    `membership_plan_id`  BIGINT UNSIGNED     NOT NULL,
    `start_date`          DATE                NOT NULL,
    `end_date`            DATE                NOT NULL,
    `total_amount`        DECIMAL(10,2)       NOT NULL,
    `paid_amount`         DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
    `remaining_amount`    DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
    `status`              ENUM('active','expired','expiring_soon','suspended') NOT NULL DEFAULT 'active',
    `notes`               TEXT                NULL DEFAULT NULL,
    `renewed_at`          TIMESTAMP           NULL DEFAULT NULL,
    `created_at`          TIMESTAMP           NULL DEFAULT NULL,
    `updated_at`          TIMESTAMP           NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `mm_member_id_index`  (`member_id`),
    KEY `mm_plan_id_index`    (`membership_plan_id`),
    KEY `mm_status_index`     (`status`),
    KEY `mm_end_date_index`   (`end_date`),
    CONSTRAINT `mm_member_id_foreign`
        FOREIGN KEY (`member_id`)
        REFERENCES `members` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `mm_membership_plan_id_foreign`
        FOREIGN KEY (`membership_plan_id`)
        REFERENCES `membership_plans` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 7: attendances
-- Purpose: Daily check-in records per member.
-- UNIQUE constraint on (member_id, date) prevents duplicates.
-- v1.2: Added check_out_time
-- ============================================================

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE `attendances` (
    `id`              BIGINT UNSIGNED              NOT NULL AUTO_INCREMENT,
    `member_id`       BIGINT UNSIGNED              NOT NULL,
    `date`            DATE                         NOT NULL,
    `check_in_time`   TIME                         NULL DEFAULT NULL,
    `check_out_time`  TIME                         NULL DEFAULT NULL,
    `status`          ENUM('present','absent')     NOT NULL DEFAULT 'present',
    `created_at`      TIMESTAMP                    NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP                    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attendances_member_date_unique` (`member_id`, `date`),
    KEY `attendances_date_index` (`date`),
    CONSTRAINT `attendances_member_id_foreign`
        FOREIGN KEY (`member_id`)
        REFERENCES `members` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 8: fee_payments
-- Purpose: Payment transaction records per membership period.
-- Supports partial payments. Generates printable receipts.
-- v1.2: payment_method changed from VARCHAR to ENUM
--       (manual record keeping — no gateway integration in V1)
-- ============================================================

DROP TABLE IF EXISTS `fee_payments`;
CREATE TABLE `fee_payments` (
    `id`                    BIGINT UNSIGNED                                              NOT NULL AUTO_INCREMENT,
    `member_id`             BIGINT UNSIGNED                                              NOT NULL,
    `member_membership_id`  BIGINT UNSIGNED                                              NOT NULL,
    `amount_paid`           DECIMAL(10,2)                                                NOT NULL,
    `payment_date`          DATE                                                         NOT NULL,
    `due_date`              DATE                                                         NULL DEFAULT NULL,
    `payment_method`        ENUM('cash','bank_transfer','easypaisa','jazzcash','card')   NOT NULL DEFAULT 'cash',
    `receipt_number`        VARCHAR(50)                                                  NULL DEFAULT NULL,
    `notes`                 TEXT                                                         NULL DEFAULT NULL,
    `created_at`            TIMESTAMP                                                    NULL DEFAULT NULL,
    `updated_at`            TIMESTAMP                                                    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `fp_receipt_unique`       (`receipt_number`),
    KEY `fp_member_id_index`             (`member_id`),
    KEY `fp_membership_id_index`         (`member_membership_id`),
    KEY `fp_payment_date_index`          (`payment_date`),
    CONSTRAINT `fp_member_id_foreign`
        FOREIGN KEY (`member_id`)
        REFERENCES `members` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fp_member_membership_id_foreign`
        FOREIGN KEY (`member_membership_id`)
        REFERENCES `member_memberships` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 9: expense_categories
-- Purpose: Reference lookup for expense types.
-- Seeded with: Rent, Utilities, Salaries, Equipment, Maintenance.
-- (No changes in v1.2)
-- ============================================================

DROP TABLE IF EXISTS `expense_categories`;
CREATE TABLE `expense_categories` (
    `id`          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)        NOT NULL,
    `created_at`  TIMESTAMP           NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP           NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `expense_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 10: expenses
-- Purpose: Gym operational cost records by category.
-- Net Profit = Monthly Revenue - Monthly Expenses.
-- v1.2: Added paid_to, receipt_image
-- ============================================================

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
    `id`                   BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `expense_category_id`  BIGINT UNSIGNED     NOT NULL,
    `title`                VARCHAR(150)        NOT NULL,
    `amount`               DECIMAL(10,2)       NOT NULL,
    `expense_date`         DATE                NOT NULL,
    `paid_to`              VARCHAR(150)        NULL DEFAULT NULL,
    `receipt_image`        VARCHAR(255)        NULL DEFAULT NULL,
    `notes`                TEXT                NULL DEFAULT NULL,
    `created_at`           TIMESTAMP           NULL DEFAULT NULL,
    `updated_at`           TIMESTAMP           NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `expenses_category_id_index` (`expense_category_id`),
    KEY `expenses_date_index`        (`expense_date`),
    CONSTRAINT `expenses_expense_category_id_foreign`
        FOREIGN KEY (`expense_category_id`)
        REFERENCES `expense_categories` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 11: notifications
-- Purpose: In-app alerts for the Gym Owner.
-- Polymorphic (notifiable_type / notifiable_id) for flexibility.
-- (No changes in v1.2)
-- ============================================================

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id`               BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `type`             VARCHAR(100)        NOT NULL,
    `notifiable_type`  VARCHAR(100)        NOT NULL,
    `notifiable_id`    BIGINT UNSIGNED     NOT NULL,
    `title`            VARCHAR(150)        NOT NULL,
    `message`          TEXT                NOT NULL,
    `is_read`          TINYINT(1)          NOT NULL DEFAULT 0,
    `read_at`          TIMESTAMP           NULL DEFAULT NULL,
    `created_at`       TIMESTAMP           NULL DEFAULT NULL,
    `updated_at`       TIMESTAMP           NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `notif_morph_index`    (`notifiable_type`, `notifiable_id`),
    KEY `notif_is_read_index`  (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 12: activity_logs
-- Purpose: Full audit trail of every CRUD action.
-- Polymorphic subject to reference any affected entity.
-- v1.2: Added user_agent
-- ============================================================

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`       BIGINT UNSIGNED     NOT NULL,
    `action`        VARCHAR(100)        NOT NULL,
    `description`   TEXT                NOT NULL,
    `subject_type`  VARCHAR(100)        NULL DEFAULT NULL,
    `subject_id`    BIGINT UNSIGNED     NULL DEFAULT NULL,
    `ip_address`    VARCHAR(45)         NULL DEFAULT NULL,
    `user_agent`    TEXT                NULL DEFAULT NULL,
    `created_at`    TIMESTAMP           NULL DEFAULT NULL,
    `updated_at`    TIMESTAMP           NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `al_user_id_index`    (`user_id`),
    KEY `al_action_index`     (`action`),
    KEY `al_subject_index`    (`subject_type`, `subject_id`),
    KEY `al_created_at_index` (`created_at`),
    CONSTRAINT `activity_logs_user_id_foreign`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- RE-ENABLE FOREIGN KEY CHECKS
-- ============================================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED DATA: Default Expense Categories
-- ============================================================

INSERT INTO `expense_categories` (`name`, `created_at`, `updated_at`) VALUES
('Rent',          NOW(), NOW()),
('Utilities',     NOW(), NOW()),
('Salaries',      NOW(), NOW()),
('Equipment',     NOW(), NOW()),
('Maintenance',   NOW(), NOW()),
('Miscellaneous', NOW(), NOW());

-- ============================================================
-- SEED DATA: Default Membership Plans
-- color: green palette aligned with WarmUp brand (#22C55E family)
-- ============================================================

INSERT INTO `membership_plans` (`name`, `duration_days`, `price`, `description`, `color`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('Monthly',     30,  3000.00,  '1 Month Membership Plan',   '#22C55E', 1, 1, NOW(), NOW()),
('Quarterly',   90,  8000.00,  '3 Months Membership Plan',  '#16A34A', 2, 1, NOW(), NOW()),
('Half Yearly', 180, 14000.00, '6 Months Membership Plan',  '#15803D', 3, 1, NOW(), NOW()),
('Yearly',      365, 25000.00, '12 Months Membership Plan', '#166534', 4, 1, NOW(), NOW());

-- ============================================================
-- SEED DATA: Default Gym Settings (single row)
-- ============================================================

INSERT INTO `gym_settings` (
    `gym_name`, `owner_name`, `country`, `city`,
    `currency`, `currency_symbol`, `timezone`,
    `language`, `theme`, `date_format`, `time_format`,
    `created_at`, `updated_at`
) VALUES (
    'WarmUp Gym', 'Gym Owner', 'Pakistan', 'Karachi',
    'PKR', 'Rs', 'Asia/Karachi',
    'en', 'light', 'd/m/Y', '12h',
    NOW(), NOW()
);

-- ============================================================
-- End of warmup.sql
-- WarmUp — Gym Management System v1.2
-- ============================================================
