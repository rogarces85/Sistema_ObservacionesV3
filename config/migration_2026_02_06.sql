-- Script de Migración: Actualización de campos
-- Sistema de Observaciones REM
-- Fecha: 2026-02-06

USE observaciones_rem;

-- La columna 'codigo_serie' ahora almacenará la SERIE (SERIE A, SERIE BM, etc.)
-- La columna 'tipo_error' ahora almacenará el TIPO (S/OBSERVACION, ERROR, REVISAR, F/PLAZO)
-- La columna 'codigo_hoja' almacenará el REM (nombre de la hoja)

-- No se requieren cambios estructurales en la tabla, solo documentación
-- Los campos ya son VARCHAR/TEXT que aceptan los nuevos valores

-- Mensaje de confirmación
SELECT 'Migración completada. Los campos existentes soportan los nuevos valores.' AS mensaje;
