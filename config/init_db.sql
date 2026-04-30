-- Script de inicialización de Base de Datos
-- Sistema de Observaciones REM - Servicio de Salud Osorno

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS observaciones_rem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE observaciones_rem;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    rol ENUM('registrador', 'supervisor') NOT NULL DEFAULT 'registrador',
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de comunas
CREATE TABLE IF NOT EXISTS comunas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_comuna INT NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    INDEX idx_codigo_comuna (codigo_comuna)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de establecimientos
CREATE TABLE IF NOT EXISTS establecimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_establecimiento INT NOT NULL UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    nombre_corto VARCHAR(50) NOT NULL,
    comuna_id INT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (comuna_id) REFERENCES comunas(id) ON DELETE RESTRICT,
    INDEX idx_codigo_establecimiento (codigo_establecimiento),
    INDEX idx_comuna_id (comuna_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de observaciones
CREATE TABLE IF NOT EXISTS observaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anio INT NOT NULL,
    mes VARCHAR(20) NOT NULL,
    establecimiento_id INT NOT NULL,
    codigo_serie VARCHAR(50) NOT NULL,
    codigo_hoja VARCHAR(50) NOT NULL,
    tipo_error VARCHAR(100) NOT NULL,
    detalle_observacion TEXT NOT NULL,
    plazo_entrega ENUM('dentro_plazo', 'fuera_plazo') NOT NULL,
    usa_validador ENUM('si', 'no') NOT NULL,
    estado_actual ENUM('pendiente', 'aprobado', 'rechazado', 'error', 'justificado') NOT NULL DEFAULT 'pendiente',
    clasificacion VARCHAR(200) NULL,
    usuario_registro_id INT NOT NULL,
    usuario_supervisor_id INT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_revision TIMESTAMP NULL,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (establecimiento_id) REFERENCES establecimientos(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_supervisor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_anio (anio),
    INDEX idx_mes (mes),
    INDEX idx_estado_actual (estado_actual),
    INDEX idx_establecimiento_id (establecimiento_id),
    INDEX idx_usuario_registro_id (usuario_registro_id),
    INDEX idx_fecha_registro (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de historial de estados
CREATE TABLE IF NOT EXISTS historial_estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    observacion_id INT NOT NULL,
    estado_anterior VARCHAR(50) NOT NULL,
    estado_nuevo VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (observacion_id) REFERENCES observaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_observacion_id (observacion_id),
    INDEX idx_fecha_cambio (fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de logs del sistema
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    accion VARCHAR(100) NOT NULL,
    detalle TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar comunas del Servicio de Salud Osorno
INSERT INTO comunas (codigo_comuna, nombre) VALUES
(10001, 'Osorno'),
(10002, 'Purranque'),
(10003, 'Entre Lagos'),
(10004, 'Río Negro'),
(10005, 'San Pablo'),
(10006, 'San Juan de la Costa'),
(10007, 'Puerto Octay')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Insertar establecimientos
INSERT INTO establecimientos (codigo_establecimiento, nombre, nombre_corto, comuna_id) VALUES
-- Osorno (comuna_id: 1)
(101, 'Hospital Base San José de Osorno', 'HBSJO', 1),
(102, 'CESFAM Dr. Marcelo Lopetegui', 'Lopetegui', 1),
(103, 'CESFAM Quinto Centenario', 'Quinto Centenario', 1),
(104, 'CESFAM Pampa Gentile', 'Pampa Gentile', 1),
-- Purranque (comuna_id: 2)
(201, 'Hospital de Purranque', 'H. Purranque', 2),
(202, 'CESFAM Purranque', 'CESFAM Purranque', 2),
-- Puerto Octay (comuna_id: 7)
(701, 'Hospital de Puerto Octay', 'H. Puerto Octay', 7),
-- Establecimientos adicionales de ejemplo
(1001, 'Posta Rural Ejemplo 1', 'Posta 1', 1),
(1002, 'Posta Rural Ejemplo 2', 'Posta 2', 2),
(1003, 'Posta Rural Ejemplo 3', 'Posta 3', 3),
(1004, 'Posta Rural Ejemplo 4', 'Posta 4', 4),
(1005, 'Posta Rural Ejemplo 5', 'Posta 5', 5),
(1006, 'Posta Rural Ejemplo 6', 'Posta 6', 6),
(1007, 'Posta Rural Ejemplo 7', 'Posta 7', 7),
(1008, 'Posta Rural Ejemplo 8', 'Posta 8', 1),
(1009, 'Posta Rural Ejemplo 9', 'Posta 9', 2),
(1010, 'Posta Rural Ejemplo 10', 'Posta 10', 3),
(1011, 'Posta Rural Ejemplo 11', 'Posta 11', 4),
(1012, 'Posta Rural Ejemplo 12', 'Posta 12', 5),
(1013, 'Posta Rural Ejemplo 13', 'Posta 13', 6),
(1014, 'Posta Rural Ejemplo 14', 'Posta 14', 7),
(1015, 'Posta Rural Ejemplo 15', 'Posta 15', 1),
(1016, 'Posta Rural Ejemplo 16', 'Posta 16', 2),
(1017, 'Posta Rural Ejemplo 17', 'Posta 17', 3),
(1018, 'Posta Rural Ejemplo 18', 'Posta 18', 4),
(1019, 'Posta Rural Ejemplo 19', 'Posta 19', 5),
(1020, 'Posta Rural Ejemplo 20', 'Posta 20', 6)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Insertar usuarios de prueba
-- Contraseña para todos: 'admin123' (hasheada con password_hash)
INSERT INTO usuarios (username, password_hash, nombre_completo, rol, activo) VALUES
('supervisor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cecilia (Supervisor)', 'supervisor', TRUE),
('registrador1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rodrigo Garcés', 'registrador', TRUE),
('registrador2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Victoria Martínez', 'registrador', TRUE),
('registrador3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Roxana Mancilla', 'registrador', TRUE),
('registrador4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marcelo Horstmeier', 'registrador', TRUE)
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);

-- Mensaje de finalización
SELECT 'Base de datos inicializada correctamente' AS mensaje;
