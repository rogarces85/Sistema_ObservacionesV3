# Quickstart: Cerrar Gap F4 — UX de Carga y Error por Categoría

**Branch**: `[003-f4-categoria-ux]` | **Date**: 2026-06-17
**Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## Prerrequisitos

- Feature 002-mejorar-reportes-analiticos implementado y archivado.
- `views/reportes.php` con la estructura de 5 categorías (errores_establecimiento, plazos_entrega, uso_validador, errores_serie, errores_hoja).
- `assets/js/reportes.js` con `CATEGORIAS_ANALITICAS` y funciones `setEstadoAnalitico`, `cargarReportesAnaliticos`, `setBotonExportarAnalitico`.
- Datos de observaciones disponibles para al menos un año de trabajo.
- Entorno XAMPP operativo.

## Flujo de Verificación Manual

### Carga inicial (cubre pasos 1-3 del feature 002 + UX de carga)

1. Iniciar sesión como supervisor.
2. Abrir la sección **Reportes**.
3. Aplicar un filtro de año con datos → observar:
   - ✅ Cada categoría muestra **spinner visible** (no solo texto) con el texto **"Cargando Errores por establecimiento..."**, **"Cargando Plazos de entrega..."**, etc. (nombre de la categoría en español, NO el identificador técnico).
   - ✅ El spinner desaparece en menos de 100ms tras la respuesta.
   - ✅ Las cinco categorías pueden mostrar spinner en paralelo (no se bloquean entre sí).

### Cambio de categoría preserva carga explícita (cubre paso 6 del feature 002 + UX)

4. Mantener filtros aplicados.
5. Click en otra pestaña de categoría → observar:
   - ✅ La nueva categoría muestra spinner con su nombre propio.
   - ✅ Las categorías previamente cargadas mantienen su contenido (gráfico + tabla + indicador) sin parpadeo.
   - ✅ Los filtros (año, trimestre, mes, comuna, establecimiento) están preservados en los controles.

### Reintento tras error (cubre US2)

6. Forzar un error de carga temporal (opciones):
   - **Opción A**: Detener MySQL brevemente con `xampp_stop` (no viable en XAMPP), o
   - **Opción B**: Editar temporalmente `api/reports.php` para que devuelva error en una categoría específica (ej. agregar `throw new Exception('test');` en el case de la categoría), o
   - **Opción C**: Usar el DevTools del navegador → Network → bloquear la URL del endpoint temporalmente.
7. Click en la pestaña de la categoría que falló → observar:
   - ✅ El mensaje de error aparece en **español** y en **lenguaje natural** (ej. "No fue posible cargar esta categoría.").
   - ✅ Hay un **botón "Reintentar"** visible debajo del mensaje, con icono de refresh.
8. Restaurar la conexión / revertir el cambio temporal.
9. Click en **Reintentar** → observar:
   - ✅ La consulta se ejecuta nuevamente con los **mismos filtros activos**.
   - ✅ El botón se **deshabilita** mientras la consulta está en curso.
   - ✅ Si tiene éxito, el mensaje de error se reemplaza por el contenido (gráfico + tabla + indicador).
10. Si la consulta vuelve a fallar → el mensaje de error + botón "Reintentar" siguen disponibles (sin límite visible).

### Aislamiento entre categorías (cubre US2 escenario 4)

11. Forzar error en 1-2 categorías mientras las otras 3-4 cargan exitosamente.
12. Verificar:
    - ✅ Las categorías exitosas muestran su contenido normal.
    - ✅ Las categorías fallidas muestran su mensaje de error con botón "Reintentar".
    - ✅ Hacer click en "Reintentar" en una categoría NO recarga las demás.

### Cambio de filtros con spinner activo (edge case)

13. Mientras una categoría está cargando (spinner visible), cambiar el filtro de comuna → observar:
    - ✅ La consulta anterior se descarta o se ignora su respuesta.
    - ✅ La nueva consulta se inicia con el spinner visible.

### Alcance por rol (cubre paso 10 del feature 002)

14. Logout → login como `registrador1`.
15. Ir a **Reportes** → aplicar filtros.
16. Verificar que las cinco categorías muestran spinner + contenido solo del alcance del registrador.
17. Si una categoría falla, el botón "Reintentar" sigue funcional.

## Criterios de Aceptación Rápida

- [ ] Spinner visible con `.spinner-border` de Bootstrap en cada categoría mientras carga.
- [ ] Texto del spinner incluye el nombre de la categoría en español (5 títulos correctos).
- [ ] Spinner aparece en <200ms tras aplicar filtro o cambiar categoría.
- [ ] Spinner desaparece en <100ms tras respuesta (exitosa o error).
- [ ] Mensaje de error en español, lenguaje natural, sin términos técnicos.
- [ ] Botón "Reintentar" presente en cada mensaje de error.
- [ ] Botón "Reintentar" se deshabilita mientras la nueva consulta está en curso.
- [ ] Múltiples clics rápidos en "Reintentar" no generan solicitudes duplicadas.
- [ ] Aislamiento: el error de una categoría no afecta a las otras cuatro.
- [ ] Reintento preserva los filtros activos (año, trimestre, mes, comuna, establecimiento).
- [ ] 5 reintentos consecutivos funcionan sin pérdida de disponibilidad del botón.
- [ ] Sin errores en consola del navegador durante los flujos.
- [ ] Sin estilos inline nuevos en `views/reportes.php` (verificable con `grep 'style=' views/reportes.php`).
- [ ] Convenciones BEM respetadas en `assets/css/tabler-override.css` (clases `reportes-analytics__*`).

## Comandos de Apoyo

```powershell
# Lint PHP
cd C:\xampp\htdocs\www\respaldo_observaciones
php -l views/reportes.php

# Lint JS
node --check assets/js/reportes.js

# Verificar que no hay estilos inline nuevos
Get-Content views/reportes.php | Select-String 'style='

# Verificar convenciones BEM en CSS
Get-Content assets/css/tabler-override.css | Select-String 'reportes-analytics__'
```

## Evidencia Esperada

- `screenshot-01-spinner-cargando.png` — categoría con spinner visible
- `screenshot-02-categoria-exitosa.png` — categoría con gráfico + tabla + indicador
- `screenshot-03-mensaje-error.png` — categoría con mensaje de error en español
- `screenshot-04-boton-reintentar.png` — close-up del botón "Reintentar" con icono
- `screenshot-05-aislamiento.png` — 4 categorías exitosas + 1 con error, en paralelo
- `screenshot-06-deshabilitado.png` — botón "Reintentar" deshabilitado durante nueva consulta
- `screenshot-07-registrador.png` — vista como registrador con spinner y error en su alcance

## Reporte de Validación

Al completar, reportar con este formato:

```markdown
# Reporte F4-UX — 003-f4-categoria-ux
**Fecha**: YYYY-MM-DD
**Ejecutado por**: [nombre]

## Lint técnico
- [ ] php -l views/reportes.php → OK
- [ ] node --check assets/js/reportes.js → OK
- [ ] sin estilos inline nuevos
- [ ] clases BEM respetadas

## Carga explícita
- [ ] Spinner aparece <200ms tras filtro
- [ ] Spinner desaparece <100ms tras respuesta
- [ ] Texto incluye nombre de categoría en español
- [ ] 5 categorías en paralelo (sin bloqueo)

## Reintento
- [ ] Botón "Reintentar" presente en error
- [ ] Reintento preserva filtros activos
- [ ] Botón se deshabilita durante nueva consulta
- [ ] 5+ reintentos consecutivos funcionan

## Aislamiento
- [ ] Error de 1 categoría no afecta a las otras 4
- [ ] Reintento de 1 categoría no recarga las demás

## Alcance por rol
- [ ] Supervisor y registrador ven spinner + error en su alcance

## Observaciones
[Issues, desviaciones, o "OK sin observaciones"]
```
