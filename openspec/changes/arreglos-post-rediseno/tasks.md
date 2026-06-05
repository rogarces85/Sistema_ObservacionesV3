# Tasks: Arreglos Post-Rediseño

**Change**: `arreglos-post-rediseno` | **Depende de**: `rediseno-tabler-admin`

---

## T-01: Arreglos menores de código

- [x] 1.1 Eliminar duplicado `menu` en `includes/icons.php` (línea 57, mantener línea 48)
- [x] 1.2 Verificar que no hay otros iconos duplicados en `icons.php`
- [x] 1.3 `tabler-override.css` no existe — referencia en login.php eliminada, uso correcto de `tabler-light.css` en header.php
- [x] 1.4 Verificar que no hay emojis en ninguna vista PHP (usar grep rápido) — 0 encontrados
- [x] 1.5 Verificar que `assets/css/styles.css` fue eliminado — confirmado
- [x] 1.6 Eliminar función rota `window.tablerIcon` en `app.js` (variables `mensaje`/`tipo` indefinidas)
- [x] 1.7 Agregar iconos faltantes en `icons.php`: `file`, `clipboard-text`, `user-edit`, `key`, `file-invoice`, `table`, `versions`, `camera`, `camera-plus`, `camera-check`, `arrow-back`
- [x] 1.8 Corregir clase `.navbar-brand-text` → `.navbar-brand` en login.php (clase inexistente)
- [x] 1.9 Verificación de sintaxis PHP en todos los archivos — 0 errores
- [x] 1.10 Corregir logout: CSRF bloqueaba la destrucción de sesión — logout ya no requiere CSRF, usa fetch directo con credentials
- [x] 1.11 Corregir GestorSesion: nunca se instanciaba — ahora se instancia en footer.php con `DOMContentLoaded`
- [x] 1.12 Corregir `fetchAPI()`: no actualizaba CSRF token cuando venía en nivel superior de respuesta

## T-02: Verificación - Login y Auth

- [ ] 2.1 Verificar login page renderiza con estilos Tabler completos
- [ ] 2.2 Probar login exitoso → redirige a dashboard
- [ ] 2.3 Probar login fallido → mensaje de error visible
- [x] 2.4 Verificar logout funciona desde cualquier página
- [ ] 2.5 Verificar timeout de sesión (25 min advertencia, 30 min expiración)
- [ ] 2.6 Verificar selector de año funciona en el header

## T-03: Verificación - Layout Shell (header, sidebar, footer)

- [ ] 3.1 Verificar sidebar muestra navegación correcta según rol (Supervisor vs Registrador)
- [ ] 3.2 Verificar item activo se marca correctamente al navegar
- [x] 3.3 Verificar dropdown de usuario abre con click, cierra al hacer click fuera
- [ ] 3.4 Verificar footer muestra nombre de app y año
- [ ] 3.5 Verificar responsive: sidebar colapsa en mobile
- [ ] 3.6 Verificar loading overlay/spinner aparece durante navegación
- [ ] 3.7 Verificar Toast container existe en footer

## T-04: Verificación - Dashboard

- [ ] 4.1 Verificar 4 stat cards con datos reales (Total, Pendientes, Aprobadas, Problemas)
- [ ] 4.2 Verificar gráfico donut de distribución por estado carga
- [ ] 4.3 Verificar gráfico de barras de tipos de error carga
- [ ] 4.4 Verificar gráfico de tendencia mensual carga
- [ ] 4.5 Verificar "Últimas Observaciones" muestra datos reales
- [ ] 4.6 Verificar "Acciones Rápidas" funcionan (enlaces a vistas)
- [ ] 4.7 Verificar que no hay emojis ni iconos rotos
- [ ] 4.8 Verificar que `.page-header` está presente

## T-05: Verificación - Observaciones

- [ ] 5.1 Verificar filtros (año, mes, estado, establecimiento, búsqueda) funcionan
- [ ] 5.2 Verificar tabla muestra datos con paginación
- [ ] 5.3 Verificar badges de estado con colores correctos
- [ ] 5.4 Verificar modal crear observación abre y valida campos
- [ ] 5.5 Verificar modal editar observación carga datos existentes
- [ ] 5.6 Verificar modal detalle con historial de cambios
- [ ] 5.7 Verificar acciones: editar, ver detalle, eliminar (confirmación)
- [ ] 5.8 Verificar empty state cuando no hay datos
- [ ] 5.9 Verificar toast aparece tras crear/editar/eliminar

## T-06: Verificación - Supervisión (Supervisor only)

- [ ] 6.1 Verificar tabla con filtros carga datos de observaciones pendientes/aprobadas/rechazadas
- [ ] 6.2 Verificar modal aprobar con clasificación y detalle de error
- [ ] 6.3 Verificar modal cancelar con comentario obligatorio
- [ ] 6.4 Verificar acciones masivas (aprobar, cancelar, eliminar)
- [ ] 6.5 Verificar historial de cambios en detalle
- [ ] 6.6 Verificar toast tras cada acción

## T-07: Verificación - Reportes

- [ ] 7.1 Verificar 3 tabs (Resumen, Errores REM, Exportar) funcionan
- [ ] 7.2 Verificar filtros (año, mes, estado, establecimiento)
- [ ] 7.3 Verificar gráficos cargan en tab Resumen
- [ ] 7.4 Verificar tabla de observaciones en tab Exportar
- [ ] 7.5 Verificar exportación Excel descarga archivo válido
- [ ] 7.6 Verificar exportación CSV con delimitador `;`
- [ ] 7.7 Verificar exportación PDF genera documento
- [ ] 7.8 Verificar paginación en vista web

## T-08: Verificación - Importación Excel (Registrador)

- [ ] 8.1 Verificar página de importación accesible para Registrador
- [ ] 8.2 Verificar botón descargar plantilla funciona
- [ ] 8.3 Verificar drag & drop o selección de archivo
- [ ] 8.4 Verificar preview muestra filas válidas y errores
- [ ] 8.5 Verificar botón confirmar importa datos
- [ ] 8.6 Verificar toast con resultado (N insertadas, M errores)

## T-09: Verificación - Usuarios (Supervisor only)

- [ ] 9.1 Verificar tabla lista usuarios con filtros
- [ ] 9.2 Verificar modal crear usuario con validación
- [ ] 9.3 Verificar opción generar contraseña aleatoria
- [ ] 9.4 Verificar modal cambiar contraseña con política
- [ ] 9.5 Verificar toggle activo/inactivo
- [ ] 9.6 Verificar no poder desactivarse/eliminarse a sí mismo
- [ ] 9.7 Verificar reset password asigna "admin123"
- [ ] 9.8 Verificar toast tras cada acción

## T-10: Verificación - Establecimientos (Supervisor only)

- [ ] 10.1 Verificar tabla con filtros por comuna y búsqueda
- [ ] 10.2 Verificar modal crear/editar establecimiento
- [ ] 10.3 Verificar modal gestionar referentes
- [ ] 10.4 Verificar orden de referentes (Encargado → Digitador → alfabético)
- [ ] 10.5 Verificar toggle activo/inactivo
- [ ] 10.6 Verificar stats (N observaciones, N asignaciones) visibles
- [ ] 10.7 Verificar toast tras crear/editar

## T-11: Verificación - Asignaciones (Supervisor only)

- [ ] 11.1 Verificar selector de año y registrador
- [ ] 11.2 Verificar árbol de establecimientos con checkboxes
- [ ] 11.3 Verificar opción "Todos" marca/desmarca todos
- [ ] 11.4 Verificar temporales activos se indican visualmente
- [ ] 11.5 Verificar modal crear/edición de asignación mensual
- [ ] 11.6 Verificar modal asignar masiva
- [ ] 11.7 Verificar modal copiar entre años
- [ ] 11.8 Verificar toast tras guardar

## T-12: Verificación - Papelera (Supervisor only)

- [ ] 12.1 Verificar tabla con filtros (año, mes, estado original)
- [ ] 12.2 Verificar restaurar observación individual
- [ ] 12.3 Verificar eliminar permanentemente individual
- [ ] 12.4 Verificar restaurar masivo
- [ ] 12.5 Verificar eliminar permanentemente masivo
- [ ] 12.6 Verificar modal confirmación para eliminación permanente
- [ ] 12.7 Verificar stats (total, por estado, por mes)
- [ ] 12.8 Verificar toast con resultado

## T-13: Verificación - Versionado (Supervisor only)

- [ ] 13.1 Verificar lista de snapshots con fecha y descripción
- [ ] 13.2 Verificar modal crear snapshot
- [ ] 13.3 Verificar modal confirmar restauración
- [ ] 13.4 Verificar mensaje de advertencia sobre migraciones BD
- [ ] 13.5 Verificar toast con resultado

## T-14: Verificación - Charts en profundidad

- [ ] 14.1 Verificar ApexCharts donut en dashboard: tooltip al hover, colores de estado
- [ ] 14.2 Verificar ApexCharts barras: data labels visibles, tooltips
- [ ] 14.3 Verificar ApexCharts línea: tendencia mensual con gradiente
- [ ] 14.4 Verificar charts en Reportes: todos cargan
- [ ] 14.5 Verificar exportación PNG de charts funciona
- [ ] 14.6 Verificar no hay errores de consola en charts

## T-15: Verificación - Consola y Browser

- [ ] 15.1 Navegar por 5 vistas, verificar cero errores (rojos) en Console
- [ ] 15.2 Verificar zero warnings de JS críticos
- [ ] 15.3 Verificar que `fetchAPI()` no genera errores de red visibles
- [ ] 15.4 Verificar que CSRF validation funciona (intentar POST sin token)
- [ ] 15.5 Verificar que 403 se devuelve para acciones no autorizadas