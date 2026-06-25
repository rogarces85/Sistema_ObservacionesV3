-- deploy/sql/create-db-user.sql
-- Crear usuario MySQL dedicado con privilegios mínimos.
-- Ejecutar como root de MySQL:
--   sudo mysql < deploy/sql/create-db-user.sql

CREATE USER 'rem_app'@'localhost'
    IDENTIFIED BY 'CAMBIAR_POR_PASSWORD_SEGURA';

-- Privilegios mínimos sobre la base del sistema
GRANT SELECT, INSERT, UPDATE, DELETE
    ON observaciones_rem.*
    TO 'rem_app'@'localhost';

-- Sin DROP, sin GRANT, sin ALTER sobre la BD (las migraciones las corre root)
FLUSH PRIVILEGES;

-- Verificacion
SELECT user, host FROM mysql.user WHERE user = 'rem_app';
