-- Migration: Optimización de índices para reportes mejorados
-- Fecha: 2026-05-08
-- Propósito: Agregar índices compuestos para optimizar consultas de reportes

USE observaciones_rem;

-- Índice compuesto para reportes por año + tipo_error (Grupo A: Errores)
ALTER TABLE observaciones
    ADD INDEX idx_anio_tipo_error (anio, tipo_error);

-- Índice compuesto para reportes por año + plazo_entrega (Grupo B: Fuera de Plazo)
ALTER TABLE observaciones
    ADD INDEX idx_anio_plazo (anio, plazo_entrega);

-- Índice compuesto para reportes por año + usa_validador (Grupo C: Validador)
ALTER TABLE observaciones
    ADD INDEX idx_anio_validador (anio, usa_validador);

-- Índice compuesto para reportes por año + codigo_serie + tipo_error (Grupo D: Serie detalle)
ALTER TABLE observaciones
    ADD INDEX idx_anio_serie_error (anio, codigo_serie, tipo_error);

-- Índice compuesto para reportes por año + codigo_hoja (Grupo D: Hoja detalle)
ALTER TABLE observaciones
    ADD INDEX idx_anio_hoja (anio, codigo_hoja);

-- Índice compuesto para reporte detallado jerárquico
ALTER TABLE observaciones
    ADD INDEX idx_anio_estado (anio, estado_actual);

-- Verificar índices creados
SHOW INDEX FROM observaciones;

SELECT 'Índices de optimización creados exitosamente' AS mensaje;
