# Research: Sistema Observaciones REM

**Creado**: 2026-06-01 | **Plan**: `specs/001-unificar-specs/plan.md`

## R01: Estructura de la BD existente

**Decisión**: Relevar tablas desde la BD MySQL en `localhost` del XAMPP. Las tablas existentes son:

| Tabla | Propósito |
|-------|-----------|
| `usuarios` | Usuarios del sistema (registradores, supervisores) |
| `comunas` | Catálogo de 7 comunas del SSO |
| `establecimientos` | 93 establecimientos de salud |
| `referentes_establecimientos` | Contactos por establecimiento |
| `observaciones` | Observaciones REM registradas |
| `historial_estados` | Auditoría de cambios de estado en observaciones |
| `historial_usuarios` | Auditoría de gestión de usuarios |
| `observaciones_eliminadas` | Papelera (soft delete desde supervisión) |
| `asignaciones_establecimientos` | Asignaciones de registradores a establecimientos |
| `versiones_sistema` | Snapshots del sistema |

**Rationale**: Documentar el esquema existente en `data-model.md` sin modificarlo.

## R02: Convención de nombres en BD

**Decisión**: La BD usa `snake_case`, nombres en plural, charset `utf8mb4_unicode_ci`, motor InnoDB. Coincide con la convención definida en la constitución.

## R03: Ruta base del proyecto

**Decisión**: `API_BASE` se calcula desde `window.location.pathname` extrayendo el directorio base. Ej: si la URL es `/respaldo_observaciones/views/dashboard.php`, `API_BASE = /respaldo_observaciones/`. Sin rutas hardcodeadas.

## R04: Versiones de librerías instaladas

**Decisión**: Verificar en `composer.json` o inclusión directa. Se asume:
- PHP 7.4.33 (XAMPP)
- PhpSpreadsheet 5.4
- TCPDF 6.10
- ApexCharts 3.45 (CDN)
- Tabler Core 1.4 (CDN o local)

## R05: Formato de archivos Excel de importación

**Decisión**: El formato se define en `importacion.md`: columnas mes, codigo_establecimiento, codigo_serie, codigo_hoja, tipo_error, detalle_observacion, plazo_entrega, anio_rem, mes_rem, usa_validador. PhpSpreadsheet auto-detecta codificación.

## Dependencias Externas

- **Tabler Icons**: CDN `https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css`
- **ApexCharts**: CDN `https://cdn.jsdelivr.net/npm/apexcharts@3.45/dist/apexcharts.min.js`
- **Tabler Core**: CDN `https://cdn.jsdelivr.net/npm/@tabler/core@1.4/dist/css/tabler.min.css`

## Patrones de Implementación

- **API pattern**: Switch por `$_GET['action']` o `$_POST['action']`, validación CSRF, verificación de rol, delegación a modelo, respuesta JSON.
- **Model pattern**: `Database::getInstance()->getConnection()->prepare($sql)` con PDO.
- **View pattern**: PHP puro que renderiza HTML con componentes Tabler, datos pasados desde modelos.
- **JS pattern**: `fetchAPI()` de `app.js` para todas las llamadas AJAX.
