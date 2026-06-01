# Implementation Plan: Sistema Observaciones REM

**Branch**: `001-unificar-specs` | **Date**: 2026-06-01 | **Spec**: `specs/001-unificar-specs/spec.md`

**Input**: Feature specification from `/specs/001-unificar-specs/` (11 módulos)

**Nota**: La base de datos ya existe y está poblada. No se generan migraciones ni cambios de esquema. El código existente se ignora — se implementa desde cero.

---

## Summary

Sistema web para gestión de observaciones del Resumen Estadístico Mensual (REM) del Servicio de Salud Osorno. 11 módulos: autenticación, CRUD observaciones, supervisión, reportes, importación Excel, asignaciones, establecimientos, usuarios, papelera, dashboard y versionado. Backend PHP 7.4+ con PDO MySQL, frontend Tabler Core 1.4 + ApexCharts.

## Technical Context

**Language/Version**: PHP 7.4+, JavaScript ES6+, HTML5, CSS3

**Primary Dependencies**: Tabler Core 1.4 (Bootstrap 5), Tabler Icons, ApexCharts 3.45, PhpSpreadsheet 5.4, TCPDF 6.10

**Storage**: MySQL 5.7+ (InnoDB, utf8mb4) — BD existente y poblada. NO modificar esquema.

**Testing**: Pruebas manuales durante desarrollo. No hay framework de testing automatizado.

**Target Platform**: Apache (XAMPP), Windows Server

**Project Type**: Web application monolítica (PHP + MySQL + JavaScript vanilla)

**Performance Goals**: Listados < 1s, exportaciones sync ≤ 1000 registros, dashboard < 3s carga inicial

**Constraints**: BD existente e inmutable. Sin npm/build tools. JavaScript vanilla. Sin framework PHP.

**Scale/Scope**: 93 establecimientos, 7 comunas, ~4 registradores + 1 supervisor. Sistema departamental.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

1. **Seguridad**: Todos los endpoints nuevos deben incluir CSRF, verificación de rol y consultas preparadas PDO. ✅
2. **Arquitectura**: Respeta separación Vista → API → Modelo. Sin lógica de negocio en vistas ni acceso a superglobals en modelos. ✅
3. **Idioma**: Todo en español: UI, APIs, documentación, código, commits. ✅
4. **Stack**: Solo tecnologías permitidas (Tabler, ApexCharts, PhpSpreadsheet, TCPDF). Sin nuevas dependencias. ✅
5. **Base de Datos**: NO se modifican esquemas. La BD ya existe y está poblada. No aplican migraciones. ✅

## Project Structure

### Documentation

```text
specs/001-unificar-specs/
├── spec.md                   # Feature index + convenciones globales
├── plan.md                   # This file
├── research.md               # Phase 0: decisiones técnicas
├── data-model.md             # Phase 1: modelo de datos (referencia BD existente)
├── quickstart.md             # Phase 1: guía de inicio rápido
├── contracts/                # Phase 1: contratos de API
├── tasks.md                  # Phase 2: tareas de implementación (/speckit.tasks)
└── checklists/
    ├── requirements.md       # Checklist de calidad de requisitos
    └── calidad-auditoria.md  # Auditoría cross-module
```

### Source Code

```text
/
├── index.php                 # Router principal (login + page routing + permisos)
├── views/                    # Vistas PHP (Tabler HTML)
│   ├── auth/
│   │   └── login.php
│   ├── dashboard.php
│   ├── observaciones.php
│   ├── supervision.php
│   ├── reportes.php
│   ├── importacion.php
│   ├── asignaciones.php
│   ├── establecimientos.php
│   ├── usuarios.php
│   ├── papelera.php
│   └── versionado.php
├── api/                      # API REST endpoints
│   ├── auth.php
│   ├── observaciones.php
│   ├── supervision.php
│   ├── export.php
│   ├── informe_errores.php
│   ├── import.php
│   ├── import_template.php
│   ├── asignaciones.php
│   ├── establecimientos.php
│   ├── usuarios.php
│   ├── eliminadas.php
│   ├── versiones.php
│   └── dashboard/
│       ├── estadisticas.php
│       ├── graficos.php
│       ├── recientes.php
│       ├── alertas.php
│       ├── sparklines.php
│       ├── timeline.php
│       └── kanban.php
├── models/                   # Capa de datos (PDO Singleton)
│   ├── Database.php
│   ├── Observacion.php
│   ├── Usuario.php
│   ├── Establecimiento.php
│   ├── Asignacion.php
│   ├── Referente.php
│   ├── Comuna.php
│   ├── HistorialEstado.php
│   ├── ObservacionEliminada.php
│   ├── VersionSistema.php
│   └── Importacion.php
├── assets/
│   ├── css/
│   │   └── tabler-override.css
│   └── js/
│       ├── app.js            # fetchAPI(), utilidades globales
│       ├── auth.js
│       ├── dashboard.js
│       ├── observaciones.js
│       ├── supervision.js
│       ├── reportes.js
│       ├── importacion.js
│       ├── asignaciones.js
│       ├── establecimientos.js
│       ├── usuarios.js
│       ├── papelera.js
│       └── versionado.js
├── config/
│   └── database.php          # Conexión PDO
└── uploads/
    └── versiones/            # Snapshots del sistema
```

**Structure Decision**: Web application monolítica. Estructura de 3 capas (views/ → api/ → models/) + assets/js/ para frontend. Sin subdirectorios por módulo en models/ (todos planos).

## Complexity Tracking

Sin violaciones a la constitución. Proyecto monolítico estándar de 3 capas.

---

## Phase 0: Research

### Unknowns to Resolve

| # | Unknown | Source | Research Task |
|---|---------|--------|---------------|
| R01 | ¿Estructura exacta de la BD existente? | DB preexistente | Relevar tablas, columnas, tipos, FK, índices desde la BD real |
| R02 | ¿Convención de nombres en BD existente? | DB preexistente | Verificar `snake_case`, nombres plurales, charset utf8mb4 |
| R03 | ¿Ruta base del proyecto en producción? | Constitution | Determinar `API_BASE` dinámica desde `window.location.pathname` |
| R04 | ¿Versión exacta de PHP y librerías instaladas? | Stack | Verificar versión PHP, PhpSpreadsheet, TCPDF, ApexCharts |
| R05 | ¿Formato de los archivos Excel de importación? | `importacion.md` | Revisar plantillas existentes, columnas, validaciones |

### Research Output

See `research.md` for consolidated findings.

---

## Phase 1: Design & Contracts

### Deliverables

| Artifact | Description |
|----------|-------------|
| `data-model.md` | Mapeo de la BD existente: tablas, columnas, relaciones (documentación, no migración) |
| `contracts/` | Especificación de endpoints API: request/response para cada módulo |
| `quickstart.md` | Guía de instalación y configuración del entorno de desarrollo |

### Data Model Approach

La BD ya existe y está poblada. `data-model.md` documentará el esquema existente, no creará uno nuevo. Se relevarán las tablas desde la BD real y se mapearán a las entidades de las especificaciones.

### Agent Context Update

Actualizar AGENTS.md con la referencia a este plan.

---

## Phase 2: Implementation Plan

### Orden de Implementación

| Fase | Módulo | Depende de | Prioridad |
|------|--------|------------|-----------|
| 1 | `auth-sesion` | — | P1 |
| 2 | `establecimientos` | — | P1 |
| 3 | `usuarios` | auth-sesion | P1 |
| 4 | `asignaciones` | establecimientos, usuarios | P1 |
| 5 | `observaciones` | auth-sesion, establecimientos, asignaciones | P1 |
| 6 | `supervision` | observaciones | P1 |
| 7 | `importacion` | observaciones | P1 |
| 8 | `reportes-exportacion` | observaciones, supervision | P1 |
| 9 | `papelera-eliminadas` | observaciones, supervision | P2 |
| 10 | `dashboard` | observaciones, supervision, asignaciones | P2 |
| 11 | `versionado` | — | P2 |

### Tareas por Módulo

Cada módulo incluirá:
1. **Backend**: modelo PHP (si aplica), API endpoint, lógica de negocio
2. **Frontend**: vista PHP, JavaScript del módulo, integración con Tabler
3. **Pruebas**: verificación manual de escenarios Gherkin
4. **Documentación**: manual de usuario con mockups (generado en `/speckit.tasks`)

### Manual de Usuario

Cada módulo generará su manual de usuario con mockups incluidos en la carpeta `docs/manuales/`. Los mockups se crearán como diagramas ASCII o referencias visuales en el propio markdown.
