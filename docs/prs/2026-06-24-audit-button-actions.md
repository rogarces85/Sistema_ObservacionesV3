# Título del PR (copy/paste en GitHub)

```
Audita acciones visibles en vistas y APIs
```

# Descripción del PR (copy/paste en GitHub)

```markdown
## Resumen

Endurece las acciones mutables de las 10 pantallas principales del
sistema (Supervisión, Observaciones, Eliminadas, Usuarios, Asignaciones,
Establecimientos, Reportes, Dashboard, Perfil, Versionado) y de sus APIs
asociadas, sin rediseñar ni introducir frameworks nuevos.

Rama: `audit/button-actions-2026-06` -> `main`
Commit: `e2b46b6`
17 archivos, +687 / -188

## Cambios por área

### `views/supervision.php` + `api/supervision.php`
- Solo observaciones `pendiente` quedan seleccionables para
  Aprobar / Cancelar / Eliminar; filas no pendientes deshabilitadas.
- Estado `checked / indeterminate / disabled` de "Seleccionar todas"
  sincronizado.
- Selección se resetea al recargar / filtrar / aplicar acciones.
- Modal de confirmación ya no se cierra si la acción falla.
- Botón "Confirmar" se desactiva durante el envío (doble submit).
- API: `normalizeObservationIds` + `requirePendingObservations`
  para que ningún POST apruebe / cancele / elimine un registro
  fuera de estado pendiente.

### `views/observaciones.php` + `api/observations.php` + `api/import.php`
- Detalle ahora usa `respuesta_establecimiento` y `fecha_revision`,
  que son los nombres reales del modelo.
- Clases `hidden` reemplazadas por `d-none` en modales.
- Crear observación ya no exige `codigo_serie` (el campo es opcional
  en el formulario).
- Importar: `parseJsonResponse` en preview y confirm; escape de
  valores en tabla; botón Confirmar deshabilitado durante el envío.
- Importar: `api/import.php` valida CSRF solo en `confirm` (la vista
  previa no muta datos).

### `views/eliminadas.php` + `api/deleted.php`
- Indicador de carga, reset de selección y sync de "Seleccionar todas"
  corregidos.
- Modal de confirmación persistente en errores.
- API: `confirm_irreversible` obligatorio para `permanent_delete` y
  `permanent_delete_multiple`; normalización de IDs.

### `views/usuarios.php` + `api/users.php`
- API: CSRF obligatorio en POST/PUT/DELETE.
- API: `confirm_delete` y `confirm_reset` obligatorios para acciones
  administrativas.
- API: supervisor no puede cambiar su propio rol desde
  `users.php?action=update`.
- UI: bloqueo de doble submit al guardar / resetear / eliminar.
- UI: el toggle de activo revierte visualmente el switch en error
  en vez de recargar forzado.
- UI: títulos con `textContent` para evitar inyección.

### `views/asignaciones.php` + `api/assignments.php`
- API: CSRF en mutaciones; normalización de años (2020..año+1) y
  meses (dedupe + clamp 1..12); validación de IDs y tipos.
- API: `copiar_anio` rechaza origen == destino.
- UI: modal "Guardar Asignaciones" deshabilita el botón durante el
  envío y resetea correctamente el modal al cerrar.
- UI: `reguladorSeleccionadoNombre` cacheado para evitar errores de
  selector en el título del modal.

### `views/establecimientos.php` + `api/locations.php`
- `parseJsonResponse` en save y toggle.
- Bloqueo de doble submit al guardar.
- Toggle revierte el switch en error sin recargar la página.
- Modal resetea estado del botón al abrir.

### `views/reportes.php` + `api/report_queue.php`
- `parseJsonResponse` en todos los endpoints de carga.
- "Encolar Excel" y "Encolar PDF" deshabilitados durante el envío.
- `success:false` ahora se muestra como error en lugar de fallar
  silenciosamente.

### `views/dashboard.php` + `api/informe_errores.php`
- `parseJsonResponse` en `cargarInformeWeb`.
- Botones "Ver en Web" y "Descargar PDF" protegidos contra doble
  submit.
- `descargarInformePDF` re-habilita el botón tras abrir el PDF.

### `views/perfil.php`
- Botón "Cambiar Contraseña" deshabilitado durante el envío.
- Errores del backend ahora se muestran vía `showMessage` con el
  mensaje del servidor.

### `views/versionado.php` + `api/versioning.php`
- Botones de Crear snapshot y Rollback con `data-version-id` /
  `data-version-tag` y listener delegado.
- Ambos botones se deshabilitan durante la operación.
- `success:false` se muestra como error.

## Seguridad

- CSRF obligatorio en `api/users.php`, `api/assignments.php`,
  `api/deleted.php`, `api/observations.php` (ya lo tenía), `api/import.php`
  (confirm), `api/report_queue.php`, `api/versioning.php`.
- Acciones administrativas ahora requieren confirmación explícita en
  backend, no solo en frontend.
- Supervisor no puede desactivarse, eliminarse, cambiarse el rol
  ni resetearse la contraseña a sí mismo.
- Endurecimiento de inputs (anio, meses, IDs) en `assignments.php` y
  `users.php`.

## UX / feedback

- `parseJsonResponse` reemplaza `await response.json()` en los
  endpoints que aún lo usaban para evitar falsos positivos en
  respuestas HTML/500.
- `showError` se invoca cuando `response.success === false` (antes
  se ignoraba en muchos handlers).
- Toggle de activo / inactivo revierte visualmente sin recargar.
- Modal de aprobación de Supervisión no se cierra si la API falla.
- Modal de asignación resetea correctamente sus radios y meses.
- Clases `hidden` legacy reemplazadas por `d-none` (Bootstrap 5).

## Verificación realizada

- `php -l` aprobado en todos los archivos modificados (vistas y APIs).
- `composer install --no-interaction` ejecutado para restaurar
  `vendor/autoload.php` y permitir el flujo de importación XLSX.
- Smoke HTTP autenticado como `supervisor1` y `registrador2`
  confirmó que las 10 pantallas, sus endpoints GET seguros y la cola
  de reportes cargan correctamente.
- Evidencia detallada:
  `specs/002-fix-button-actions/verification-evidence.md` (sección
  "Follow-up *" agregada por pantalla).

## Notas importantes

- **No se ejecutaron mutaciones reales** contra la BD oficial
  (`ENVIRONMENT=production`). Todas las acciones destructivas o
  que afectan datos reales (aprobar, cancelar, eliminar,
  eliminar permanente, importar, reset, rollback) fueron
  verificadas como `confirmation-only` o con pruebas no mutantes.
- Rollback de Versionado mantiene su doble confirmación
  (`confirm` + `prompt("ACEPTAR")`); el backend ya exigía CSRF.
- No se modificaron reglas de negocio REM ni el modelo de datos.
- No se introdujeron frameworks ni dependencias nuevas.

## Pendiente fuera de este PR

- Pruebas de mutación reales con registros de prueba (requieren BD
  de QA, no incluida en este pase por seguridad de producción).
- Auditoría de `worker_reportes.php` y de la CLI de cola
  (`backend` no testeado en navegador).
- Auditoría de `assets/js/charts.js` y `assets/js/app.js` (no
  tocados; las utilidades `parseJsonResponse` quedaron por vista).

## Checklist

- [x] Lint de archivos modificados
- [x] Smoke HTTP autenticado como supervisor y registrador
- [x] Sin mutaciones reales contra BD de producción
- [x] Evidencia registrada en `verification-evidence.md`
- [ ] Revisión de código por pares
- [ ] Pruebas con datos controlados (entorno QA)
```

# Archivos clave para abrir el PR

- URL: https://github.com/rogarces85/Sistema_ObservacionesV3/pull/new/audit/button-actions-2026-06
- Base branch: `main`
- Compare branch: `audit/button-actions-2026-06`
