-- ============================================================
-- Usuarios Demo — Solo para entornos de desarrollo
-- ============================================================
-- Credenciales:
--   demo_registrador / Demo1234  (rol: registrador)
--   demo_supervisor   / Demo1234  (rol: supervisor)
-- ============================================================
-- ADVERTENCIA: No ejecutar en producción.
-- ============================================================

INSERT INTO usuarios (username, password_hash, nombre_completo, rol, activo)
VALUES
    ('demo_registrador', '$2y$10$upv.GilyyZLESmDWtcChXOK6QH7v8Nn/IZrZWymbddDh3YJGz1OZa', 'Demo Registrador', 'registrador', 1),
    ('demo_supervisor', '$2y$10$upv.GilyyZLESmDWtcChXOK6QH7v8Nn/IZrZWymbddDh3YJGz1OZa', 'Demo Supervisor', 'supervisor', 1)
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash),
    nombre_completo = VALUES(nombre_completo),
    rol = VALUES(rol),
    activo = 1,
    fecha_actualizacion = NOW();
