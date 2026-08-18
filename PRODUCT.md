# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

**Registradores** — Profesionales de establecimientos de salud (hospitales, clínicas) que ingresan y editan observaciones del REM (Resumen Estadístico Mensual) para establecimientos asignados durante meses específicos. Trabajan en contextos mixtos: en establecimientos, oficinas centrales, o remotamente. Su flujo: crear/editar observaciones pendientes, enviar a supervisión, ver rechazos, incorporar cambios solicitados. Tienen acceso limitado a sus propias observaciones en estado `pendiente`.

**Supervisores** — Equipos de la autoridad sanitaria (Servicio de Salud Osorno) que revisan, aprueban, rechazan observaciones; administran usuarios y asignaciones de establecimientos/meses; generan reportes consolidados; resuelven errores REM. Operan desde oficinas centrales, con acceso a información de toda la red de establecimientos. Pueden ver historial, versiones y papelera de eliminadas.

## Product Purpose

Centralizar y gobernar el flujo de observaciones REM de establecimientos de salud hacia reportes consolidados de gestión sanitaria. Permite que registradores documenten hallazgos y cambios, que supervisores validen calidad y cumplimiento, y que ambos roles registren decisiones de forma auditable para control operativo, seguimiento de plazos, y generación de reportes.

## Operating Context

- **Ciclo anual:** El sistema opera por año fiscal (2024, 2025, etc.), guardado en sesión. Gestiona información histórica por período mensual.
- **Asignaciones duales:** Cada registrador tiene asignaciones anuales a establecimientos/meses; supervisores pueden crear asignaciones temporales para reasignar observaciones.
- **Errores REM categorizados:** El sistema clasifica observaciones por tipo de error (`S/OBSERVACION`, `ERROR`, `REVISAR`, `F/PLAZO`) y permite justificaciones.
- **Estados observables:** Observaciones transitan por estados definidos (`pendiente`, `aprobado`, `rechazado`, `error`, `justificado`); registradores ven solo sus propias; supervisores ven todas.
- **Importación masiva:** Supervisores pueden importar observaciones desde Excel para establecimientos específicos.
- **Reportes y exportación:** El sistema genera reportes consolidados (PDF, Excel) y mantiene papelera recuperable para datos eliminados.

## Capabilities and Constraints

**Definido:**
- Dos roles con permisos muy asiméricos (registrador ≪ supervisor).
- Control de acceso por rol distribuido en cada endpoint (no middleware centralizado).
- CSRF obligatorio en toda operación mutante (`X-CSRF-Token` header o campo POST).
- Autenticación por sesión PHP; sin SSO o OAuth integrado en la línea base.
- Tema `light`/`dark` persistido en cookie `rem.theme`, no solo localStorage.
- Chart.js para gráficos; debe re-leer tokens de diseño y refrescar en evento `rem:theme-changed`.
- Base de datos MySQL/MariaDB; migraciones manuales (sin ORM).

**Restricciones técnicas:**
- Stack legacy: PHP monolítico, Tabler 1.4 (CSS + iconos), sin framework frontend moderno.
- Sin dependencias JavaScript nuevas (jQuery, React, Vue descartados).
- No agregar Tailwind, standalone Bootstrap, ni frameworks CSS compitiendo con Tabler.
- Todas las vistas reutilizan shell compartido (`includes/header.php`, `sidebar.php`, `breadcrumbs.php`, `footer.php`).
- Sin cambios a estructura PHP; CSS/tokens solo.
- Navegadores modernos (Chrome, Firefox, Safari, Edge actuales); IE 11+ no es objetivo activo.

**No implementado / Gaps conocidos:**
- Worker de reportes (`worker_reportes.php`) tiene bugs documentados; no es confiable.
- Importación no valida asignación mensual del mismo modo que creación manual.
- Sin tests automatizados; verificación es manual/exploratoria.

## Brand Commitments

- **Nombre:** Sistema de Observaciones REM.
- **Institución:** Servicio de Salud Osorno, Región de Los Lagos, Chile.
- **Paleta:** Derivada del logo oficial DEIS Osorno.
  - Navy `#003366` — texto, titulares, encabezados (12.6:1 sobre blanco).
  - Azul `#0B71B9` — texto, enlaces, botones (5.13:1 sobre blanco).
  - Cian `#00AEEF` — relleno y acentos únicamente (contraste insuficiente para texto claro).
  - Celeste `#76C7ED` — bandas y acentos decorativos (relleno).
- **Tema:** Light/dark persistido; en modo oscuro, primary remapea a `#4FB3E8`, enlaces a `#76C7ED`.
- **Voice:** Formal, basado en procesos regulatorios; sin idioma coloquial.
- **Tokens:** CSS custom properties en `assets/css/tokens.css`; overrides componentes en `assets/css/tabler-override.css`. `assets/css/styles.css` es deprecated.

## Evidence on Hand

- **README.md:** Especificación exhaustiva de arquitectura, tabla API completa, ERD, reglas de negocio en Gherkin, tech debt conocido.
- **`.specify/memory/constitution.md`:** Reglas vinculantes del proyecto (seguridad, trazabilidad, integridad de datos, flujo de desarrollo).
- **`SECURITY.md`, `DEPLOY.md`, `OPERATIONS.md`:** Threat model, despliegue en producción, runbook.
- **`CLAUDE.md` (proyecto):** Instrucciones arquitectónicas y de desarrollo para futuros cambios.
- **Database schema:** Migraciones en `config/*.sql` y `config/migrations/`; orden crítico validado contra estado real.
- **UI actual:** Vistas PHP (12 páginas principales: `dashboard`, `observaciones`, `supervision`, `usuarios`, `asignaciones`, `establecimientos`, `reportes`, `eliminadas`, `perfil`, `login`, `boletin`).

## Product Principles

1. **Autoridad normativa sobre interfaz:** Datos y estados definidos en `config/constants.php` son la fuente de verdad; UI no inventa estados, roles, ni etiquetas.
2. **Eficiencia con trazabilidad:** Minimizar pasos del usuario sin sacrificar auditoría; cada decisión registrable es decisión gobernada.
3. **Acceso basado en rol sin middleware:** Permisos validados en backend en cada endpoint, no en UI; UI no es barrera de seguridad.
4. **Preservar identidad institucional:** Paleta y marca DEIS no son opcionales; reflejan autoridad de la institución.
5. **Operación sostenible:** Sin dependencias nuevas, sin build pipeline, sin frameworks compitiendo; CSS estático y vistas PHP renderizadas son límite de complejidad.
