-- ====================================
-- SCRIPT DE INICIALIZACIÓN DE LA BASE DE DATOS
-- ====================================
-- Este script se ejecuta automáticamente al crear el contenedor de MariaDB
-- Crea las tablas necesarias e inserta datos de prueba

-- Seleccionar la base de datos
USE ticketing;

-- ====================================
-- TABLA: users
-- ====================================
-- Almacena los usuarios del sistema
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLA: tickets
-- ====================================
-- Almacena los tickets del sistema
CREATE TABLE IF NOT EXISTS tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- DATOS DE PRUEBA
-- ====================================

-- Insertar usuario administrador de prueba
-- Email: admin@empresa.com
-- Password: admin1234
-- Hash generado con password_hash('admin1234', PASSWORD_DEFAULT) en PHP 8.2
INSERT INTO users (name, email, password_hash, role) VALUES
('Administrador', 'admin@empresa.com', '$2y$10$n3SXnnRuQGnioeZZBD9Gi.NvWk7quGhdftVKTwTFrcypZgsJAgILm', 'admin'),
('Usuario Demo', 'user@empresa.com', '$2y$10$n3SXnnRuQGnioeZZBD9Gi.NvWk7quGhdftVKTwTFrcypZgsJAgILm', 'user');

-- Insertar tickets de ejemplo
INSERT INTO tickets (title, description, status, created_by) VALUES
('Problema con el login', 'No puedo acceder a mi cuenta. Me dice que la contraseña es incorrecta.', 'open', 1),
('Solicitud de nueva funcionalidad', 'Me gustaría poder exportar los tickets a PDF.', 'open', 2),
('Error en la página de tickets', 'Al intentar cerrar un ticket, aparece un error 500.', 'closed', 1),
('Mejora en el diseño', 'El botón de crear ticket es difícil de encontrar.', 'open', 2);

-- Información útil
SELECT 'Base de datos inicializada correctamente' AS status;
SELECT 'Usuario de prueba creado:' AS info, 'admin@empresa.com / admin1234' AS credentials;
