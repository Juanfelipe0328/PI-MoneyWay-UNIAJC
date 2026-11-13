CREATE DATABASE `moneyway_db`;
USE `moneyway_db`;

-- 1. Tabla de Usuarios
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `role` VARCHAR(20) DEFAULT 'user', -- 'admin' o 'user'
    `status` VARCHAR(20) DEFAULT 'activa', -- activa, suspendida, eliminada
    `consent_accepted_at` DATETIME NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL
);

-- 2. Tabla de Categorías
CREATE TABLE `categories` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) UNIQUE NOT NULL,
    `type` VARCHAR(20) NULL, -- Opcional: para definir si es ingreso o gasto
    `is_active` BOOLEAN DEFAULT TRUE,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL
);

-- 3. Tabla de Movimientos
CREATE TABLE `movements` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `type` VARCHAR(20) NOT NULL, -- ingreso o gasto
    `amount` DECIMAL(12,2) NOT NULL,
    `movement_date` DATE NOT NULL,
    `merchant` VARCHAR(100) NULL,
    `description` VARCHAR(255) NULL,
    `source` VARCHAR(20) NOT NULL, -- manual, texto, voz
    `raw_message` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
);

-- 4. Tabla de Presupuestos
CREATE TABLE `budgets` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `month` VARCHAR(7) NOT NULL, -- Formato: YYYY-MM
    `amount_limit` DECIMAL(12,2) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `unique_user_category_month` (`user_id`, `category_id`, `month`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
);

-- 5. Tabla de Metas de Ahorro
CREATE TABLE `savings_goals` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `month` VARCHAR(7) NOT NULL, -- Formato: YYYY-MM
    `target_amount` DECIMAL(12,2) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `unique_user_month` (`user_id`, `month`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- 6. Tabla de Auditoría
CREATE TABLE `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `admin_id` BIGINT UNSIGNED NOT NULL,
    `target_user_id` BIGINT UNSIGNED NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`target_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- 7. Tabla de Recomendaciones
CREATE TABLE `recommendations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `category_id` BIGINT UNSIGNED NULL,
    `message` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
);

INSERT INTO `categories` (`name`, `type`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES 
('Alimentación', 'gasto', 1, 1, NOW(), NOW()),
('Transporte', 'gasto', 1, 2, NOW(), NOW()),
('Entretenimiento', 'gasto', 1, 3, NOW(), NOW()),
('Salud', 'gasto', 1, 4, NOW(), NOW()),
('Ingresos', 'ingreso', 1, 5, NOW(), NOW());


ALTER TABLE movements ADD COLUMN custom_category VARCHAR(60) NULL;