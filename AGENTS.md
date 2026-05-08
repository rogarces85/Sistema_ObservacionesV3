# AGENTS.md — Sistema de Observaciones REM

## Arquitectura
- PHP 7.4+ vanilla, sin framework. Patrón MVC simplificado.
- **Entry point:** `index.php` — router por `?page=` a `views/{page}.php`.
- **APIs REST:** `api/*.php` — reciben peticiones AJAX desde `assets/js/app.js`.
- **Modelos:** `models/*.php` — acceso a datos vía PDO (`Database.php` singleton).
- **No hay Node.js/npm.** Todo el frontend es vanilla JS + CSS (BEM).
- Dependencias PHP vía Composer: `phpoffice/phpspreadsheet`, `tecnickcom/tcpdf`.

## Comandos del desarrollador
```bash
composer install              # Instalar dependencias PHP
mysql -h localhost -u root -p < config/init_db.sql  # Inicializar BD
mysql -h localhost -u root -p observaciones_rem < config/migration_2026_05_08_reportes.sql  # Índices reportes
php db_check.php              # Verificar conexión a BD
php -l path/to/file.php       # Syntax check
```
- **No hay** linter, formatter, typecheck, ni test runner local.

## Pruebas
- **No hay** linter, formatter, typecheck, ni test runner local.
- Verificar con `php -l` cada archivo PHP modificado antes de deploy.

## Base de datos
- Servidor real: `10.8.152.199:3306` (ver `config/config.php`).
- DB: `observaciones_rem`.
- Tablas principales: `usuarios`, `observaciones`, `historial_estados`, `establecimientos`, `comunas`, `logs`, `asignaciones_establecimientos`, `observaciones_eliminadas`.
- `config/config.php` contiene credenciales en texto plano — **no commitear en producción**.

## Rutas y permisos
- Páginas: `dashboard`, `observaciones`, `supervision`, `reportes`, `usuarios`, `perfil`, `asignaciones`, `eliminadas`.
- `supervision`, `usuarios`, `asignaciones`, `eliminadas` → solo rol `supervisor`. `registrador` es redirigido a `dashboard`.
- Sesión: `$_SESSION['logged_in']`, `$_SESSION['rol']`, `$_SESSION['year']`.

## Reportes (v2.1)
- **20 dimensiones** de reporte en `api/reports.php` (6 generales + 14 específicas).
- **6 tabs** en `views/reportes.php`: General, Errores, Fuera de Plazo, Validador, Serie/Hoja, PDF Detallado.
- **PDF Detallado:** Jerárquico Comuna→Establecimiento→Mes con rowspan, header rojo oscuro, código de colores por estado.
- **Exportación específica:** Cada sub-reporte tiene botón de exportación a Excel via `api/export.php?report_type={tipo}`.
- **Índices:** 6 índices compuestos nuevos en `config/migration_2026_05_08_reportes.sql`.

## Importación masiva
- Flujo de 2 pasos: **preview** (validación sin insertar) → **confirm** (inserción real).
- Archivos se suben a `uploads/` (ignorado por git).
- Validación: busca establecimiento por código primero, luego por nombre.

## Convenciones
- CSRF: `includes/csrf.php` — tokens en formularios POST/PUT/DELETE.
- Contraseñas: `password_hash()` / `password_verify()` (bcrypt).
- Zona horaria: `America/Santiago`.
- Respuestas JSON de APIs: función `jsonResponse($success, $data, $message, $code)`.
- **Sesión:** `session_start()` se maneja **solo** en `config/config.php`. Las APIs **no** deben llamarlo directamente.

## Gotchas
- `session.cookie_secure = 0` (desactivado porque corre en HTTP local vía XAMPP).
- `controllers/` existe pero está vacío — la lógica de control vive en `api/*.php`.
- `uploads/` debe existir y tener permisos de escritura para la importación.
- El sistema usa `$_GET['page']` sin URL rewriting — las URLs son `?page=dashboard`, no rutas limpias.
- **NO llamar `session_start()` en APIs** — causa conflicto con `ini_set()` en `config.php` y produce "No autenticado".
- `api/export.php` fue corregido en v2.1 para eliminar `session_start()` redundante.
