# Sistema de Observaciones REM

> **Fuente de la verdad (Spec Kit — Paso 0 / `/speckit.discover`)**
> Este documento es el resultado de ingeniería inversa del sistema en producción y constituye la base para todos los comandos posteriores de Spec Kit (`/speckit.constitution`, `/speckit.specify`, `/speckit.plan`, etc.).

**Versión del sistema:** 2.3.0 — **Última actualización:** Junio 2026
**Organización:** Servicio de Salud Osorno (SSO) — Departamento de Estadística (DEGI)
**Dominio:** Salud pública chilena — Resumen Estadístico Mensual (REM)

---

## Tabla de Contenidos

1. [Descripción General](#1-descripción-general)
2. [Arquitectura y Stack Tecnológico](#2-arquitectura-y-stack-tecnológico)
3. [Dominio del Negocio REM](#3-dominio-del-negocio-rem)
4. [Roles y Matriz de Permisos](#4-roles-y-matriz-de-permisos)
5. [Estructura del Proyecto](#5-estructura-del-proyecto)
6. [Modelos de Datos (Esquema BD)](#6-modelos-de-datos-esquema-bd)
7. [APIs y Endpoints](#7-apis-y-endpoints)
8. [Flujos Principales](#8-flujos-principales)
9. [Lógica de Negocio (Modelos PHP)](#9-lógica-de-negocio-modelos-php)
10. [Configuración del Entorno](#10-configuración-del-entorno)
11. [Instalación y Despliegue](#11-instalación-y-despliegue)
12. [Seguridad y Auditoría](#12-seguridad-y-auditoría)
13. [Manuales de Usuario](#13-manuales-de-usuario)
14. [Convenciones y Patrones de Código](#14-convenciones-y-patrones-de-código)
15. [Roadmap y Mejoras Pendientes](#15-roadmap-y-mejoras-pendientes)
16. [Historial de Versiones](#16-historial-de-versiones)
17. [Solución de Problemas](#17-solución-de-problemas)
18. [Recursos Adicionales](#18-recursos-adicionales)

---

## 1. Descripción General

El **Sistema de Observaciones REM** es una aplicación web institucional del Servicio de Salud Osorno (SSO) que gestiona, supervisa y reporta las **observaciones** detectadas en los reportes **REM (Resumen Estadístico Mensual)** enviados por los establecimientos de salud de la red asistencial. Cada observación documenta una incidencia — error, fuera de plazo, falta de uso del validador o confirmación de sin observación — y sigue un ciclo de vida supervisado por el Departamento de Estadística (DEGI).

El sistema resuelve tres problemas operativos del SSO:

1. **Trazabilidad de la calidad del dato REM** a nivel de cada establecimiento, comuna, serie y hoja REM.
2. **Ciclo de supervisión** entre registradores (que reportan) y supervisores (que aprueban, cancelan o mueven a papelera).
3. **Reportería institucional** con tableros, gráficos, informes trimestrales/anuales en PDF y exportación masiva en Excel/CSV.

### Tabla resumen de módulos

| Módulo | Propósito | Rol primario |
|--------|-----------|---------------|
| Autenticación | Login, logout, cambio de año de trabajo, sesión PHP + CSRF | Ambos |
| Observaciones | CRUD, importación Excel, historial, filtros | Registrador (escribe) / Supervisor (lee todo) |
| Supervisión | Aprobar/cancelar, mover a papelera, aprobar con selector S/OBSERVACION/ERROR | Supervisor |
| Reportes | 5 tabs analíticas (Errores Establecimiento, Plazos, Validador, Serie, Hoja) | Ambos |
| Informe de Errores REM | Generación trimestral/anual en PDF institucional | Supervisor |
| Usuarios | CRUD, roles, contraseñas, auditoría | Supervisor |
| Asignaciones | Vinculación anual + reasignación temporal por meses | Supervisor |
| Establecimientos y Referentes | Catálogo + contactos por establecimiento | Supervisor |
| Importación | Carga masiva desde Excel con preview | Registrador |
| Exportación | Excel / PDF / CSV (general + detallado + sub-reportes) | Ambos |
| Papelera | Soft-delete con restauración y eliminación permanente | Supervisor |
| Versionado | Snapshots del código fuente y rollback | Supervisor |
| Cola de Reportes | Procesamiento asíncrono vía `worker_reportes.php` | Ambos |

> **Alcance (no-objetivos):** El sistema no reemplaza al REM propiamente tal (origen de los datos), no se integra con DEIS ministerial directamente, y no incluye firma electrónica avanzada. Su alcance es la **gestión interna de observaciones** dentro del SSO.

---

## 2. Arquitectura y Stack Tecnológico

### Patrón general

```
┌──────────────────────────────────────────────────────────────────┐
│                         index.php (Router)                       │
│  • Verifica sesión                                               │
│  • Aplica whitelist de páginas                                   │
│  • Aplica guard de permisos por rol                              │
└──────────────────────────────────────────────────────────────────┘
                │
        ┌───────┴───────┐
        ▼               ▼
   includes/         views/                  api/ (REST JSON)
   ─────────         ──────                  ───────────────
   header.php        dashboard.php           auth.php
   footer.php        observaciones.php       observations.php
   sidebar.php       supervision.php         supervision.php
   csrf.php          reportes.php            reports.php
   icons.php         usuarios.php            export.php
                     ...                     informe_errores.php
                                             ...
                │
                ▼
           models/  ─────────►  config/database.php  ─────────►  MySQL
           ────────             ───────────────────              ─────
           Database.php         Conexión PDO Singleton
           User.php             (utf8mb4, emulate_prepares=false)
           Observation.php
           ... 17 modelos
```

### Stack

| Capa | Tecnología | Versión | Notas |
|------|-----------|---------|-------|
| Backend | PHP | 7.4+ | Vanilla, sin framework |
| Persistencia | MySQL / MariaDB | 5.7+ | InnoDB, utf8mb4_unicode_ci |
| Acceso a datos | PDO | — | Singleton en `models/Database.php` |
| Frontend | HTML5 + CSS3 + JS ES6+ | — | Vanilla, sin framework SPA |
| UI Kit | Tabler Core | 1.4.x | Bootstrap 5 + Tabler Icons |
| Gráficos | ApexCharts | 3.45 | `assets/js/charts-apex.js` |
| Tablas dinámicas | Tom-Select | última en `assets/libs/` | Selects enriquecidos |
| Calendarios | FullCalendar, Litepicker | — | Selector de fechas |
| Firma | Signature Pad | — | Captura de rúbrica en informe |
| Excel | PhpSpreadsheet | 5.4 | Importación + exportación |
| PDF | TCPDF | 6.10 | Informe de Errores + detallado |
| Servidor | Apache | — | Vía XAMPP |
| Gestor de dependencias PHP | Composer | — | `composer.json` |
| Gestor de dependencias JS | npm | — | `package.json` (solo `@tabler/core`) |

### Patrones arquitectónicos identificados

- **MVC sin framework:** `index.php` enruta → carga `views/*.php` (vistas) → estas consumen `models/*.php` (lógica de datos) → AJAX llama a `api/*.php` (endpoints JSON).
- **Singleton PDO:** `Database::getInstance()` única conexión por request.
- **Front Controller parcial:** `index.php` centraliza login y permisos; cada endpoint en `api/` es autocontenido (no hay dispatcher de rutas REST).
- **Token Bucket CSRF:** `random_bytes(32)` → hex → meta tag + header `X-CSRF-TOKEN` → validación servidor.
- **Soft Delete híbrido:** API `DELETE` por defecto = hard delete; `supervision/delete` = soft delete (mueve a `observaciones_eliminadas`).
- **Cola asíncrona:** `ReportQueue` + `worker_reportes.php` ejecutado por cron.

---

## 3. Dominio del Negocio REM

### Glosario mínimo

- **REM (Resumen Estadístico Mensual):** reporte mensual obligatorio que cada establecimiento de salud envía al DEGI/DEIS con estadísticas de producción asistencial.
- **Serie REM:** clasificación temática del reporte. Sistema soporta **6 series**: A, BS, BM, P, ANEXO, D.
- **Hoja REM:** sub-formulario dentro de una serie (ej. A01, A23, BM18a, Hoja Control, Renombre archivo).
- **Observación:** nota de calidad sobre una hoja REM en un establecimiento/mes específico. Tiene ciclo de vida y estado.

### Estados de una observación

| Estado (`estado_actual`) | Significado | Color UI |
|----|----|----|
| `pendiente` | Registrada, esperando revisión | Amarillo |
| `aprobado` | Supervisor aprobó (S/OBSERVACION) | Verde |
| `rechazado` | Supervisor canceló | Rojo |
| `error` | Supervisor aprobó como error | Rojo oscuro |
| `justificado` | Con respuesta válida del establecimiento | Azul |

### Tipos de error (`tipo_error`)

| Valor | Significado |
|-------|-------------|
| `S/OBSERVACION` | Sin observación (estado `aprobado`) |
| `ERROR` | Error detectado (estado `error`) |
| `REVISAR` | Requiere revisión |
| `F/PLAZO` | Fuera de plazo |

### Clasificaciones (al aprobar como error)

- Corregido
- Error
- Sin respuesta del Establecimiento
- Respuesta incorrecta de Establecimiento

### Plazo de entrega y uso de validador

- **Plazo:** `dentro_plazo` / `fuera_plazo` (binario).
- **Validador:** `si` / `no` (¿el establecimiento usó el software validador REM?).

### Series y Hojas REM soportadas (extracto)

- **SERIE A** (28 hojas): A01–A09, A11, A11a, A19a, A19b, A21, A23–A34, A30AR, Hoja Nombre, Hoja Control, Renombre archivo.
- **SERIE BS** (3): B, B17, Hoja Nombre, Hoja Control, Renombre archivo.
- **SERIE BM** (2): BM18, BM18a, Hoja Nombre, Hoja Control, Renombre archivo.
- **SERIE P** (8): P01–P07, P09, Hoja Nombre, Hoja Control, Renombre archivo.
- **SERIE ANEXO** (10): Parto_RN, S_Infancia, I.T.S, Rechazos, Farmacia, S_Mental, S_Adolescencia, Laboratorio, Intercultural, S_Familiar, Hoja Nombre, Hoja Control, Renombre archivo.
- **SERIE D** (2): D15, D16, Hoja Nombre, Hoja Control, Renombre archivo.

> Detalle completo en `config/constants.php` (variable `$HOJAS_POR_SERIE`).

---

## 4. Roles y Matriz de Permisos

### Roles

- **Supervisor** (Cecilia) — Acceso total. Gestiona usuarios, asignaciones, supervisa observaciones, ve y restaura papelera, accede a versionado e informes.
- **Registrador** (Rodrigo, Victoria, Roxana, Marcelo) — Crea/edita solo sus observaciones, importa Excel, ve reportes propios, cambia su contraseña.

### Matriz consolidada

| Capacidad | Registrador | Supervisor |
|-----------|:-----------:|:----------:|
| Autenticación y cambio de año | ✅ | ✅ |
| Crear observación | ✅ (solo establecimientos asignados para el mes) | ❌ |
| Ver observaciones | ✅ (solo propias) | ✅ (todas) |
| Editar observación | ✅ (solo propias en `pendiente`) | ✅ (todas) |
| Aprobar / Cancelar (selector S/OBSERVACION / ERROR) | ❌ | ✅ |
| Mover a papelera (soft-delete) | ❌ | ✅ |
| Restaurar / Eliminar permanente | ❌ | ✅ |
| Importar Excel | ✅ | ❌ |
| Exportar reportes | ✅ (alcance propio) | ✅ (alcance total) |
| Generar Informe de Errores PDF | ❌ | ✅ |
| Gestionar usuarios | ❌ | ✅ |
| Gestionar asignaciones (anual + temporal) | ❌ | ✅ |
| Gestionar establecimientos y referentes | ❌ | ✅ |
| Acceder a versionado (snapshots / rollback) | ❌ | ✅ |
| Cambiar su propia contraseña | ✅ | ✅ |

> **Regla de oro:** toda restricción se valida en el **backend** (no confiar en el frontend). Ejemplo: `api/observations.php` rechaza con 403 si un registrador intenta crear para un establecimiento no asignado en el mes.

---

## 5. Estructura del Proyecto

```
ObservacionesREM_V2/
├── index.php                        # Router principal (auth + whitelist + guard de rol)
├── composer.json                    # phpoffice/phpspreadsheet ^5.4, tecnickcom/tcpdf ^6.10
├── composer.lock
├── package.json                     # @tabler/core ^1.4.0
├── package-lock.json
├── worker_reportes.php              # Worker CLI de la cola de reportes (cron)
├── MANUAL_REGISTRO_OBSERVACIONES.html   # Manual HTML extenso (legacy)
├── MÓDULOS PRINCIPALES DEL SISTEMA.txt # Resumen en texto plano
│
├── api/                             # Endpoints REST JSON (un archivo por recurso)
│   ├── auth.php                     # login, logout, check, change_year
│   ├── observations.php             # CRUD observaciones
│   ├── supervision.php              # approve, cancel, delete, update_status, get_filtered, get_detail
│   ├── reports.php                  # ~20 reportes (mes, establecimiento, comuna, serie, hoja, plazos, validador, errores, agregados)
│   ├── export.php                   # Excel/PDF/CSV + reportes específicos
│   ├── informe_errores.php          # Informe trimestral/anual (JSON + PDF)
│   ├── locations.php                # Comunas y establecimientos
│   ├── import.php                   # Importación Excel con preview/confirm
│   ├── import_template.php          # Generación de plantilla
│   ├── users.php                    # CRUD usuarios + password management
│   ├── assignments.php              # Asignación anual/temporal de establecimientos
│   ├── deleted.php                  # Papelera (list, restore, permanent_delete)
│   ├── versioning.php               # Snapshots y rollback
│   ├── update_estado.php            # Cambio genérico de estado
│   └── versiones.php                # (legacy) Snapshots
│
├── views/                           # Vistas server-rendered (PHP + HTML)
│   ├── auth/login.php               # Formulario de inicio de sesión
│   ├── dashboard.php                # Stats + gráficos + últimas observaciones + alertas
│   ├── observaciones.php            # CRUD + importación
│   ├── supervision.php              # Panel supervisor con acciones masivas
│   ├── reportes.php                 # 5 tabs analíticas
│   ├── usuarios.php                 # CRUD usuarios
│   ├── perfil.php                   # Perfil y cambio de contraseña
│   ├── asignaciones.php             # Asignar establecimientos (anual/temporal)
│   ├── papelera.php                 # (servida como `?pagina=eliminadas`)
│   ├── establecimientos.php         # Gestión establecimientos y referentes
│   ├── importacion.php              # Vista de importación
│   └── versionado.php               # Snapshots y rollback
│
├── models/                          # Capa de datos (17 modelos)
│   ├── Database.php                 # Singleton PDO
│   ├── User.php / Usuario.php
│   ├── Observation.php / Observacion.php
│   ├── Location.php / Comuna.php / Establecimiento.php
│   ├── Asignacion.php / EstablecimientoAsignacion.php
│   ├── Referente.php
│   ├── HistorialEstado.php / HistorialUsuario.php
│   ├── PapeleraEliminada.php / DeletedObservation.php
│   ├── Exporter.php
│   ├── ReportQueue.php
│   └── VersionSistema.php / Version.php
│
├── includes/                        # Componentes reutilizables
│   ├── header.php                   # Meta CSRF + nav + selector de año
│   ├── footer.php                   # Scripts Tabler, ApexCharts, toasts, app
│   ├── sidebar.php                  # Menú lateral agrupado por rol
│   ├── csrf.php                     # Clase CSRF (gen, val, regen)
│   └── icons.php                    # Helper tablerIcon() con SVG inline
│
├── config/
│   ├── config.php                   # Conexión BD, entornos prod/dev, sesión, timezone
│   ├── constants.php                # Estados, roles, series, hojas, meses, colores
│   ├── database.php
│   ├── init_db.sql                  # Creación de BD + datos semilla
│   ├── create_asignaciones_table.sql
│   ├── sprint3_migration.sql        # Tabla observaciones_eliminadas
│   ├── update_establecimientos.sql  # 93 registros oficiales DEIS
│   ├── migration_2026_02_06.sql     # Documentación cambio semántico (SERIE/TIPO/REM)
│   ├── migration_2026_05_08_limpieza_comunas.sql
│   ├── migration_2026_05_08_reportes.sql    # 6 índices compuestos
│   └── migrations/
│       └── add_tipo_asignacion.sql  # Columna tipo_asignacion (anual/temporal)
│
├── assets/
│   ├── css/
│   │   └── tabler-override.css      # Paleta SSO, BEM, responsive
│   ├── js/
│   │   ├── app.js                   # fetchAPI, modals, CSRF, logout
│   │   ├── charts-apex.js           # Inicialización ApexCharts
│   │   ├── toasts.js                # Notificaciones (sistema nuevo)
│   │   ├── notifications.js         # (legacy, reemplazado)
│   │   ├── dashboard.js
│   │   ├── asignaciones.js
│   │   ├── establecimientos.js
│   │   ├── importacion.js
│   │   ├── papelera.js
│   │   ├── reportes.js
│   │   ├── supervision.js
│   │   ├── usuarios.js
│   │   └── versionado.js
│   └── libs/                        # Librerías JS third-party (vendor)
│       ├── @tabler/core/            # UI kit
│       ├── @hotwired/turbo/
│       ├── apexcharts/              # Gráficos
│       ├── tom-select/              # Selects enriquecidos
│       ├── fullcalendar/
│       ├── signature_pad/
│       ├── litepicker/
│       ├── dropzone/
│       ├── list.js/
│       ├── fslightbox/
│       ├── nouislider/
│       ├── imask/
│       ├── plyr/
│       ├── star-rating.js/
│       ├── hugerte/                 # Editor rich-text
│       ├── countup.js/
│       ├── @melloware/coloris/      # Color picker
│       └── typed.js/
│
├── uploads/                         # Archivos subidos (gitignored)
│   └── versiones/                   # Snapshots del sistema (creado en runtime)
│
├── docs/                            # Documentación
│   ├── auditoria-seguridad.md      # Auditoría CSRF/permisos (T-040, 2026-06-02)
│   └── manuales/                    # 12 manuales de usuario + mockups SVG
│
├── specs/                           # Especificaciones SpecKit + specs legacy
│   ├── INDICE.md                    # Índice maestro de 11 módulos
│   ├── login.md / mod-auth.md
│   ├── obs-modulo.md / obs-crear-observacion.md
│   ├── mod-supervision.md
│   ├── mod-asignaciones.md
│   ├── mod-establecimientos.md
│   ├── mod-importacion.md
│   ├── mod-exportacion.md
│   ├── mod-usuarios.md
│   ├── mod-eliminadas.md
│   ├── versiones.md
│   ├── 001-unificar-specs/
│   └── 002-mejorar-reportes-analiticos/   # Cambio activo SpecKit
│
├── openspec/                        # Cambios Spec Kit
│   ├── config.yaml                  # Reglas proposal/tasks
│   ├── changes/
│   │   ├── archive/                 # 13 cambios ya aplicados
│   │   │   ├── 2026-05-25-actualizar-docs-y-config/
│   │   │   ├── 2026-05-25-mejorar-modal-aprobacion-observaciones/
│   │   │   ├── 2026-05-25-reportes-errores-rediseno/
│   │   │   ├── 2026-05-25-selector-estado-aprobacion/
│   │   │   ├── 2026-05-27-modularizar-reportes-nav-tabs/
│   │   │   ├── 2026-05-27-refactor-reportes-ui-filtros/
│   │   │   ├── 2026-05-29-detalle-observacion-completo/
│   │   │   ├── 2026-05-29-filtro-trimestre-reportes/
│   │   │   ├── 2026-05-29-hoja-nombre-select/
│   │   │   ├── 2026-05-29-informe-errores-rem/
│   │   │   ├── 2026-05-29-mejora-reportes-plazo-validador/
│   │   │   ├── 2026-05-29-refinar-graficos-chartjs/
│   │   │   └── 2026-06-01-dashboard-tabler-features/
│   │   ├── arreglos-post-rediseno/  # Cambio en curso (no archivado)
│   │   ├── boxed-layout-tabler/
│   │   ├── migrar-tabler-dashboard/
│   │   ├── rediseno-tabler-admin/
│   │   └── refactor-observation-modal/
│   └── specs/                       # Specs aprobadas (delta de cambios archivados)
│
├── .specify/                        # Templates SpecKit puros
│   ├── templates/                   # spec/plan/tasks/constitution/checklist
│   ├── memory/                      # Constitución del proyecto
│   ├── scripts/
│   ├── workflows/
│   ├── integrations/
│   ├── extensions/
│   ├── extensions.yml
│   ├── feature.json
│   ├── init-options.json
│   └── integration.json
│
├── .opencode/                       # Configuración de opencode (skill tool)
│   ├── commands/                    # 18 comandos (specify, plan, tasks, etc.)
│   ├── skills/                      # Skills disponibles
│   └── package.json
│
├── .gitignore
└── AGENTS.md                        # Contexto del agente (SPECKIT block)
```

### Resumen funcional de carpetas

| Carpeta | Responsabilidad |
|---------|-----------------|
| `api/` | Endpoints REST JSON consumidos por fetch desde el frontend. |
| `views/` | Plantillas PHP que renderizan HTML server-side; incluyen header/footer/sidebar. |
| `models/` | Capa de datos con PDO; encapsula queries, validaciones y reglas de negocio. |
| `includes/` | Layout transversal (header, footer, sidebar) + helpers globales (CSRF, iconos). |
| `config/` | Configuración, conexión BD y **scripts SQL de inicialización/migración**. |
| `assets/` | CSS, JS de aplicación, librerías vendor JS. |
| `uploads/` | Archivos generados (snapshots de versionado, archivos importados temporales). |
| `docs/` | Auditoría + manuales de usuario con mockups. |
| `specs/` | Especificaciones funcionales en Markdown (módulos + cambios SpecKit). |
| `openspec/` | Cambios SpecKit en curso y archivados (delta + tareas). |
| `.specify/` | Templates y constitución de SpecKit (framework). |
| `.opencode/` | Comandos y skills de opencode. |

---

## 6. Modelos de Datos (Esquema BD)

Base de datos: **`observaciones_rem`** — charset `utf8mb4_unicode_ci` — engine InnoDB.

### Tablas

| Tabla | Propósito | Columnas clave |
|-------|-----------|----------------|
| `usuarios` | Cuentas del sistema | `id`, `username`, `password_hash` (bcrypt), `nombre_completo`, `rol` (`registrador`/`supervisor`), `activo` |
| `comunas` | 7 comunas oficiales SSO | `id`, `codigo_comuna` (DEIS), `nombre` |
| `establecimientos` | 93 establecimientos DEIS | `id`, `codigo_establecimiento`, `nombre`, `nombre_corto`, `comuna_id` (FK), `activo` |
| `observaciones` | Tabla principal | `id`, `anio`, `mes`, `establecimiento_id` (FK), `codigo_serie`, `codigo_hoja`, `tipo_error`, `detalle_observacion`, `plazo_entrega` (`dentro_plazo`/`fuera_plazo`), `usa_validador` (`si`/`no`), `estado_actual`, `clasificacion`, `detalle_error`, `respuesta_establecimiento`, `usuario_registro_id` (FK), `usuario_supervisor_id` (FK) |
| `historial_estados` | Trazabilidad | `id`, `observacion_id` (FK), `estado_anterior`, `estado_nuevo`, `usuario_id` (FK), `comentario`, `created_at` |
| `asignaciones_establecimientos` | Vinculación registrador↔estab. | `id`, `usuario_id` (FK), `establecimiento_id` (FK), `anio`, `meses` (ALL o CSV 1-12), `tipo_asignacion` (`anual`/`temporal`) |
| `observaciones_eliminadas` | Papelera (soft-delete) | `id`, `observacion_id` (origen), copia completa de campos + `motivo_eliminacion`, `fecha_eliminacion`, `usuario_elimina_id` |
| `versiones_sistema` | Snapshots | `id`, `version_tag` (vXXX), `descripcion`, `snapshot_path`, `archivos_json` (manifest MD5), `usuario_id` |
| `reportes_pendientes` | Cola asíncrona | `id`, `usuario_id`, `tipo_reporte`, `formato`, `parametros` (JSON), `estado` (`PENDIENTE`/`PROCESANDO`/`LISTO`/`ERROR`) |
| `historial_usuarios` | Auditoría de usuarios | `id`, `usuario_id`, `accion` (`CREACION`/`ACTIVACION`/`DESACTIVACION`/`CAMBIO_PASSWORD`/...), `detalles`, `created_at` |
| `referentes_establecimientos` | Contactos por establecimiento | `id`, `establecimiento_id` (FK), `cargo`, `nombre`, `telefono`, `email`, `activo` |
| `logs` | Log general | `id`, `usuario_id`, `accion`, `detalle`, `ip_address`, `user_agent`, `created_at` |

### Índices de optimización (migration_2026_05_08_reportes)

```
idx_anio_tipo_error     (anio, tipo_error)
idx_anio_plazo          (anio, plazo_entrega)
idx_anio_validador      (anio, usa_validador)
idx_anio_serie_error    (anio, codigo_serie, tipo_error)
idx_anio_hoja           (anio, codigo_hoja)
idx_anio_estado         (anio, estado_actual)
```

### Cardinalidades (resumen ER)

```
usuarios (1) ──< (N) observaciones [usuario_registro_id]
usuarios (1) ──< (N) observaciones [usuario_supervisor_id]
usuarios (1) ──< (N) asignaciones_establecimientos
establecimientos (1) ──< (N) observaciones
establecimientos (1) ──< (N) asignaciones_establecimientos
establecimientos (1) ──< (N) referentes_establecimientos
comunas (1) ──< (N) establecimientos
observaciones (1) ──< (N) historial_estados
observaciones (1) ──< (1) observaciones_eliminadas [soft-delete]
```

---

## 7. APIs y Endpoints

> Todas las llamadas mutativas (POST/PUT/DELETE) requieren **CSRF** vía header `X-CSRF-TOKEN` o campo `_csrf`. La validación de permisos por rol se aplica en cada endpoint.

### `api/auth.php`
| Acción | Método | Descripción |
|--------|--------|-------------|
| `login` | POST | Autentica (`username`+`password`+`year`); inicia sesión PHP |
| `logout` | POST | Destruye sesión |
| `check` | GET | Verifica sesión activa y retorna datos del usuario |
| `change_year` | POST | Cambia año de trabajo (rango válido 2020 ~ año_siguiente) |

### `api/observations.php`
| Acción | Método | Descripción |
|--------|--------|-------------|
| `—` | GET | Lista del año (filtro por rol: registrador ve solo las suyas) |
| `?id=N` | GET | Detalle por ID (con permisos) |
| `historial?id=N` | GET | Historial de cambios |
| `stats` | GET | Agregados (por estado, mes, tipo de error) |
| `—` | POST | Crear (solo registrador; valida asignación mensual) |
| `?id=N` PUT | Actualizar (registrador: solo propias en `pendiente`; supervisor: todo) |
| `?id=N` DELETE | Eliminar (solo supervisor) |

**Campos requeridos al crear:** `mes`, `establecimiento_id`, `codigo_serie`, `tipo_error`, `detalle_observacion`, `plazo_entrega`, `usa_validador`. `codigo_hoja` requerido excepto para `S/OBSERVACION`.

### `api/supervision.php` (solo supervisor)
| Acción | Método | Descripción |
|--------|--------|-------------|
| `approve` | POST | Aprobar con selector `estado_resultante` (`sin_observacion` → `aprobado`+`S/OBSERVACION`; `error` → `error`+`ERROR`) + `clasificacion` + `detalle_error` |
| `cancel` | POST | Cancelar (`estado=rechazado`) |
| `delete` | POST | Mover a papelera (soft-delete) |
| `update_status` | POST | Cambio de estado genérico |
| `get_filtered` | GET | Lista con filtros (año, mes, estado, establecimiento, registrador, texto) + paginación |
| `get_detail` | GET | Detalle completo + historial |

### `api/reports.php` (solo GET)
20+ reportes: `mes`, `establecimiento`, `comuna`, `serie`, `plazo`, `validador`, `errores_mes/establecimiento/comuna`, `errores_por_serie`, `errores_por_hoja`, `fuera_plazo_mes/establecimiento/comuna`, `validador_mes/establecimiento/comuna`, `serie_detalle`, `hoja_detalle`, `plazo-agregado`, `validador-agregado`, `error-reports` (unificado con 5 sub-reportes).

### `api/export.php`
| `report_type` | Formatos | Descripción |
|----------------|----------|-------------|
| `general` (default) | Excel, PDF, CSV | General con filtros |
| `detallado` | PDF | Jerárquico Comuna→Establecimiento→Mes con rowspan + colores |
| `errores_*`, `fuera_plazo_*`, `validador_*`, `serie_detalle`, `hoja_detalle` | Excel | Sub-reportes individuales |

### `api/informe_errores.php` (solo supervisor)
| Parámetros | Descripción |
|------------|-------------|
| `tipo=trimestral&trimestre=1-4` | Informe trimestral |
| `tipo=anual` | Informe anual completo |
| `format=json` | Vista web paginada (20/pág) |
| `format=pdf` | PDF profesional vertical con logo SSO y firma |

### `api/locations.php`
`comunas` (GET), `establecimientos` (GET, con `comuna_id` o `comuna_nombre`), `establecimientos_all` (GET, incluye inactivos), `create`/`update`/`toggle` (POST, solo supervisor).

### `api/users.php` (mayoría solo supervisor)
GET lista/detalle, POST crear (con generación aleatoria opcional de password), PUT `update`/`password`/`reset_password`/`toggle`, DELETE (no a sí mismo).

**Política de contraseñas:** ≥ 8 caracteres, ≥ 1 mayúscula, ≥ 1 número.

### `api/assignments.php` (solo supervisor)
`list`, `registradores`, `establecimientos`, `asignados`, `asignar` (anual o temporal con meses), `asignar_multiple`, `remover` (completa o parcial), `copiar_anio`, `temporales`.

### `api/deleted.php` (solo supervisor)
`list` (filtros: año, mes, comuna, establecimiento, registrador, texto), `stats`, `restore`, `permanent_delete`, `restore_multiple`, `permanent_delete_multiple`.

### `api/versioning.php` (solo supervisor)
`list`, `detail?id=N`, `create` (snapshot → `uploads/versiones/vXXX/` con manifest MD5), `rollback?id=N` (restaura + crea nueva versión de registro).

### `api/import.php` / `api/import_template.php`
`import.php` recibe Excel → validación → `preview` (datos en sesión) → `confirm` (commit transaccional).
`import_template.php` genera `.xlsx` con hoja de instrucciones y ejemplos.

---

## 8. Flujos Principales

Esta sección combina **diagramas de secuencia** (visiones técnicas de los actores y servicios) con **narrativas paso a paso** (acompañadas de mockups SVG en `docs/manuales/`).

---

### 8.1. Flujo: Crear observación manual (Registrador)

#### Diagrama de secuencia

```
Registrador  │   Browser   │   index.php   │  api/observations.php  │  models/Observation  │  BD MySQL
             │             │               │                        │  models/Asignacion   │
   1: GET /observaciones ──▶ index.php (auth OK, rol=registrador) ──▶ views/observaciones.php
             │             │               │                        │                       │
   2: ──▶ click "+ Nueva"   │               │                        │                       │
   3: ──▶ submit form ──────────────────────────────────────────▶ validarCsrfToken() ✅
             │             │               │                        │                       │
   4:                                              ────────────────▶ tieneAsignacionParaMes(mes)
             │             │               │                        │  ◀──── SELECT ─────▶
   5:                                              ◀──────────── ok  │                       │
   6:                                              ────────────────▶ INSERT observaciones + historial_estados
             │             │               │                        │  ◀──── INSERT ─────▶
   7: ◀─── 201 + JSON {ok:true, id} ──────────────────────────────────│                       │
   8: toast.success() + reload tabla ◀─                              │                       │
```

#### Narrativa paso a paso

1. Registrador autenticado navega a **Observaciones** desde el sidebar.
2. Pulsa **+ Nueva Observación** → se abre el modal con tabs (Datos, Detalle, Respuesta).
3. Completa los campos requeridos (`mes`, `establecimiento` (filtrado por sus asignaciones), `serie`, `hoja`, `tipo_error`, `plazo`, `validador`, `detalle`).
4. Al enviar, el frontend añade el token CSRF desde `<meta name="csrf-token">` y hace `POST /api/observations.php`.
5. Backend: valida CSRF → consulta `Asignacion::tieneAsignacionParaMes(usuario, establecimiento, anio, mes)` → 403 si no tiene.
6. Backend: inicia transacción → inserta en `observaciones` → inserta en `historial_estados` (`estado_anterior=null`, `estado_nuevo=pendiente`) → commit.
7. Frontend recibe 201 → toast verde → recarga la tabla → cierra el modal.

**Mockup:** `docs/manuales/observaciones.md` (vista principal + modal).

---

### 8.2. Flujo: Importación masiva desde Excel (Registrador)

#### Diagrama de secuencia

```
Registrador │  Browser  │  api/import.php        │  PhpSpreadsheet  │  BD (preview en sesión)  │  BD (commit)
            │           │                        │                  │                          │
   1: POST (multipart .xlsx) ─▶ validarCsrf + validar mime ───▶ leer + validar
            │           │                        │                  │                          │
   2: ◀─── 200 {preview:[{fila, ok|error, ...}], stats} ────────│                          │
   3: muestra modal preview (errores resaltados) │                  │                          │
   4: POST {action:confirm, payload} ──────────▶ iniciar transacción                                          │
            │           │                        │                  │                          │
   5:                                             INSERT masivo en observaciones ────────────────────────▶
            │           │                        │                  │                          │
   6: ◀─── 200 {insertados:N, omitidos:M} ──────────────────────────────────────────────────────── commit │
```

#### Narrativa paso a paso

1. En `views/observaciones.php` el usuario abre el panel **Importar** y sube un `.xlsx` o `.xls`.
2. `api/import.php` valida CSRF, valida MIME, lee con `PhpSpreadsheet\IOFactory`, valida fila por fila:
   - Mapeo de establecimiento por **código** (prioridad) o **nombre** (fallback).
   - Compatibilidad hacia atrás con nombres de columna antiguos (`tipo_error`, `codigo_serie`, `codigo_hoja`).
3. Devuelve un `preview` con cada fila marcada como `ok` / `error` (motivo).
4. El usuario revisa y confirma → segundo POST con `action=confirm`.
5. Backend abre transacción, inserta masivamente, registra historial, commit.
6. Toast con `insertados: N, omitidos: M`.

**Mockup:** `docs/manuales/importacion.md`.

---

### 8.3. Flujo: Aprobar con selector S/OBSERVACION/ERROR (Supervisor)

#### Diagrama de secuencia

```
Supervisor  │  Browser  │  api/supervision.php  │  models/Observation  │  models/UserAudit   │  BD
            │           │                       │                      │                     │
   1: selecciona N observaciones (checkboxes)                            │                     │
   2: click "Aprobar" → modal con selector "estado_resultante"        │                     │
            │  (sin_observacion | error) + clasificacion + detalle_error                     │
   3: POST {action:approve, ids:[..], estado_resultante, ...} ─▶ validarCsrf + permiso rol   │
            │           │                       │                      │                     │
   4:                                       bulkUpdateStatus(extraData)                      │
            │           │                       │   UPDATE estado_actual + extraData          │
            │           │                       │   INSERT historial_estados (x N)            │
            │           │                       │   logAction(supervisor) ──────────────────▶
            │           │                       │                      │                     │
   5: ◀─── 200 {actualizadas:N, omitidas:M} ──│                      │                     │
```

#### Narrativa paso a paso

1. Supervisor entra a **Supervisión**, aplica filtros (estado, mes, comuna, establecimiento, registrador, búsqueda).
2. Selecciona una o varias observaciones con checkbox.
3. Pulsa **Aprobar** → se abre el modal con:
   - Selector `estado_resultante`: `Sin Observación` (→ `aprobado` + `S/OBSERVACION`) o `Error` (→ `error` + `ERROR`).
   - Si eligió `Error`: combo `clasificacion` (Corregido / Error / Sin respuesta / Respuesta incorrecta) + textarea `detalle_error`.
4. POST a `api/supervision.php?action=approve` con token CSRF y payload.
5. Backend valida CSRF + rol, ejecuta `bulkUpdateStatus` con `extraData`, registra historial, registra auditoría.
6. Frontend recibe respuesta, actualiza la tabla, toast con conteo.

**Mockup:** `docs/manuales/supervision.md` y `docs/manuales/supervision-aprobar.svg`.

---

### 8.4. Flujo: Asignación anual + reasignación temporal (Supervisor)

#### Diagrama de secuencia

```
Supervisor │  api/assignments.php  │  models/EstablecimientoAsignacion  │  BD
           │                       │                                    │
   1: GET ?action=list ──────────▶ getEstadisticasAsignaciones(anio) ──▶
   2: ◀─── {registrador: {anual, temporales, conflictos}} ─────────────│
   3: POST {action:asignar, usuario, establecimiento, anio, meses, tipo} │
           │                       │                                    │
   4:                       validar solapamiento (temporales) ─────────▶
           │                       │                                    │
   5:                       INSERT con prioridad: temporales sobre anuales │
           │                       │                                    │
   6: ◀─── 201 {ok, asignacion_id} ─│                                    │
   7: Para remover parcial: POST {action:remover, meses:[3,4]}          │
   8:                       UPDATE con resta de meses ────────────────▶
```

#### Narrativa paso a paso

1. Supervisor abre **Asignaciones** y selecciona el año.
2. Ve cards por registrador con conteo de anuales y temporales.
3. Para asignar: elige registrador + establecimiento + meses (checkboxes) + tipo (`anual` o `temporal`).
   - **Validación:** temporales del mismo período no pueden solaparse entre sí; las temporales pueden solapar con la anual.
   - Si ya existe asignación del mismo tipo, **fusiona** los meses.
4. Para remover: elegir asignación → marcar meses a remover → backend hace resta de meses.
5. "Copiar año anterior" toma todas las asignaciones (anuales + temporales) y las replica en el año destino.
6. "Ver temporales activas" muestra quién está cubriendo y quién es el titular anual.

**Mockup:** `docs/manuales/asignaciones.md`.

---

### 8.5. Flujo: Informe de Errores REM trimestral (Supervisor)

```
Supervisor │  Browser  │  api/informe_errores.php  │  models/Observation  │  TCPDF
          │           │                            │                      │
  1: GET ?tipo=trimestral&trimestre=2&anio=2026&format=pdf ─▶ getErroresInforme()
          │           │                            │                      │
  2:                                              SELECT JOIN observaciones + establecimientos + comunas
          │           │                            │                      │
  3: ◀────── stream PDF (vertical, logo SSO, jerárquico) ────────────── renderInformePDF()
```

Pasos: supervisor en dashboard → clic en **Informe de Errores** → elige trimestre/año/formato → recibe PDF vertical con tabla jerárquica **Comuna→Establecimiento**, código de colores por estado, **Serie/Hoja REM** en azul institucional (#005288) y sección de firma.

**Mockup:** `docs/manuales/reportes-exportacion.md`.

---

### 8.6. Flujo: Papelera → Restaurar (Supervisor)

1. Supervisor entra a **Eliminadas** (papelera).
2. Ve observaciones con `motivo_eliminacion` y `fecha_eliminacion`.
3. Selecciona una o varias → **Restaurar**.
4. `api/deleted.php?action=restore` reinserta en `observaciones`, reinserta fila en `historial_estados`, elimina de papelera.
5. Toast con confirmación y conteo.

**Mockup:** `docs/manuales/papelera-eliminadas.md`.

---

### 8.7. Flujo: Snapshot + Rollback (Supervisor)

1. **Crear versión:** `POST /api/versioning.php?action=create&descripcion=...` → backend genera `vXXX`, copia archivos del proyecto a `uploads/versiones/vXXX/`, calcula MD5 de cada archivo, guarda manifest.
2. **Listar:** `GET ?action=list` devuelve versiones cronológicas con autor.
3. **Rollback:** `POST ?action=rollback&id=N` → restaura archivos desde snapshot + crea nueva versión de registro.

**Mockup:** `docs/manuales/versionado.md` + `versionado-crear.svg` + `versionado-lista.svg`.

---

## 9. Lógica de Negocio (Modelos PHP)

Catálogo de **métodos públicos** por modelo. Útil como referencia rápida; las firmas completas están en el código fuente.

### `Database` (`models/Database.php`)
| Método | Firma | Propósito |
|--------|-------|-----------|
| `getInstance()` | `: Database` | Singleton PDO |
| `query` | `(string $sql, array $params=[]): array` | SELECT múltiple |
| `queryOne` | `(string $sql, array $params=[]): ?array` | SELECT único |
| `execute` | `(string $sql, array $params=[]): int` | INSERT/UPDATE/DELETE (filas afectadas) |
| `lastInsertId` | `(): string` | Último ID |
| `beginTransaction` / `commit` / `rollback` | `(): bool` | Transacciones |

### `User` (`models/User.php`)
| Método | Firma | Propósito |
|--------|-------|-----------|
| `authenticate` | `(string $u, string $p): ?array` | Login con `password_verify` |
| `getById` / `getAll` / `getByRole` | `(...): ?array / array` | Consultas |
| `isActive` | `(int $id): bool` | Estado activo |
| `create` | `(string $u, string $p, string $n, string $rol): int` | Crea con `password_hash` |
| `update` | `(int $id, string $n, string $rol): bool` | Actualiza datos |
| `updatePassword` | `(int $id, string $p): bool` | Cambia password |
| `setActive` | `(int $id, bool $a): bool` | Toggle activo |
| `delete` | `(int $id): bool` | Elimina (no a sí mismo en API) |
| `getByIdWithPassword` | `(int $id): ?array` | Para verificar password actual |
| `usernameExists` | `(string $u, ?int $exclude=null): bool` | Validar duplicado |

### `Observation` (`models/Observation.php`) — núcleo del sistema
**CRUD:** `getAll`, `getById`, `create`, `update`, `delete`, `deleteWithAudit`, `getHistorial`.
**Supervisión:** `updateStatus(extraData)`, `bulkUpdateStatus`, `getWithFilters`, `getStats`.
**Reportes generales:** `reportePorMes`, `reportePorEstablecimiento`, `reportePorComuna`, `reportePorSerie`, `reportePorPlazo`, `reportePorValidador`.
**Reportes errores:** `reporteErroresPorMes`, `…PorEstablecimiento`, `…PorComuna`, `reporteErroresPorSerie`, `reporteErroresPorHoja`.
**Reportes fuera de plazo:** `reporteFueraPlazoPorMes/Establecimiento/Comuna`.
**Reportes validador:** `reporteValidadorPorMes/Establecimiento/Comuna`.
**Reportes detalle:** `reportePorSerieDetalle`, `reportePorHojaDetalle`.
**Reportes agregados:** `reportePlazoAgregado`, `reportePlazoMensual`, `reporteValidadorAgregado`, `reporteValidadorMensual`, `reporteNoValidadorPorEstablecimiento`.
**PDF:** `reporteDetalladoPDF`, `getErroresInforme`.
**Utilidades:** `getComunasConDatos`, `getEstablecimientosConDatos`.

### `Location` (`models/Location.php`)
`getAllComunas`, `getComunaById`, `getComunaByNombre`, `getAllEstablecimientos`, `getEstablecimientosByComuna`, `getEstablecimientoById`, `getAllEstablecimientosConInactivos`, `searchEstablecimientos`, `createComuna`, `createEstablecimiento`, `updateEstablecimiento`, `toggleEstablecimiento`, `codigoEstablecimientoExiste`.

### `EstablecimientoAsignacion` (`models/EstablecimientoAsignacion.php`) — núcleo de reglas
`getAllRegistradores`, `getAllEstablecimientos`, `getEstablecimientosByRegistrador`, `getEstablecimientosConAsignacion`, **`asignar(usuario, establecimiento, anio, meses, tipo)`** (anual o temporal con prioridad sobre anual + fusión de meses), `asignarMultiple`, `remover` (completa o parcial por meses), `removerTodas`, **`tieneAsignacionParaMes(usuario, establecimiento, anio, mes)`** (validación mensual), `tieneAsignaciones`, `getRegistradoresSinAsignaciones` (alerta dashboard), `getEstadisticasAsignaciones`, `copiarAsignaciones` (anual + temporal), `getAsignacionesTemporalesActivas`, `getTitularAnual`, `getReferentes`, `getReferentesMultiple`.

### `Exporter` (`models/Exporter.php`)
`exportToExcel(data, filename, headers)`, `exportToPDF(data, filename, headers, title)`, `exportToCSV(data, filename, headers)` (BOM UTF-8, separador `;`), `exportDetalladoPDF(data, filename, filters)` (Comuna→Establecimiento→Mes, rowspan, colores), `exportErroresExcel`, `exportInformeErroresPDF(data, periodo, filename)` (vertical institucional con logo SSO + firma). Helpers: `prepareObservationsData`, `getObservationsHeaders`.

### `DeletedObservation` (`models/DeletedObservation.php`)
`moveToTrash(observacionId, supervisorId, reason)` (copia a papelera + borra original), `getAll(filters)`, `restore(deletedId, supervisorId)`, `permanentDelete(deletedId)`, `getStats(year)`.

### `Version` (`models/Version.php`)
`createVersion(descripcion, userId)` (genera `vXXX`, copia a `uploads/versiones/vXXX/`, manifest MD5), `getAllVersions`, `getVersionDetails(id)`, `rollback(versionId, userId)`.

### `ReportQueue` (`models/ReportQueue.php`)
`enqueue(userId, tipoReporte, formato, parametros)`, `getUserReports(userId)`, `getNextPending()` (con `SELECT FOR UPDATE`), `updateStatus`, `markProcessing`, `markReady`, `markError`.

### `UserAudit` (`models/UserAudit.php`)
`logAction(userId, action, details)`, `getHistory(userId)`.

### Modelos auxiliares

`Comuna`, `Establecimiento`, `Asignacion`, `Referente`, `HistorialEstado`, `HistorialUsuario`, `PapeleraEliminada`, `VersionSistema`, `Usuario`, `Observacion` — duplicados o wrappers del núcleo para consumo en vistas/APIs. Su contrato es un subconjunto de los modelos principales.

---

## 10. Configuración del Entorno

Toda la configuración centralizada en `config/`.

### `config/config.php`

```php
define('ENVIRONMENT', 'production');  // o 'development'

$dbConfig = [
    'production'  => ['host'=>'10.8.152.199','port'=>'3306',
                      'name'=>'observaciones_rem','user'=>'root','pass'=>'estadi2021',
                      'charset'=>'utf8mb4'],
    'development' => ['host'=>'localhost','port'=>'3306',
                      'name'=>'observaciones_rem','user'=>'root','pass'=>'',
                      'charset'=>'utf8mb4'],
];
```

Define: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`, `BASE_PATH`, `UPLOAD_PATH`, `ASSETS_PATH`, `APP_NAME`, `APP_VERSION` (2.0.0 en archivo; el README documenta 2.3.0), `SESSION_NAME` (`rem_session`).

**Zona horaria:** `America/Santiago`.
**Sesión:** `cookie_httponly=1`, `use_only_cookies=1`, `cookie_secure=0` (cambiar a 1 bajo HTTPS).
**Inicio de sesión:** `session_name(SESSION_NAME); session_start();` si no hay sesión activa.

### `config/constants.php`

- **Estados:** `ESTADO_PENDIENTE`, `ESTADO_APROBADO`, `ESTADO_RECHAZADO`, `ESTADO_ERROR`, `ESTADO_JUSTIFICADO`.
- **Roles:** `ROL_REGISTRADOR`, `ROL_SUPERVISOR`.
- **Plazo:** `PLAZO_DENTRO`, `PLAZO_FUERA`.
- **Validador:** `USA_VALIDADOR_SI`, `USA_VALIDADOR_NO`.
- **`$TIPOS_ERROR`:** `['S/OBSERVACION','ERROR','REVISAR','F/PLAZO']`.
- **`$SERIES_REM`:** `['SERIE A','SERIE BS','SERIE BM','SERIE P','SERIE ANEXO','SERIE D']`.
- **`$HOJAS_POR_SERIE`:** array asociativo serie→lista de `['codigo','nombre']`.
- **`$COLORES_ESTADOS`, `$COLORES_SSO`, `$MESES`, …** (paleta, etiquetas, meses del año).

### Variables de entorno

El sistema **no usa `.env`** por diseño (es PHP vanilla sobre XAMPP). El "entorno" se controla con `define('ENVIRONMENT', ...)` en `config/config.php`. Para producción real, se recomienda migrar a `.env` con `vlucas/phpdotenv`.

---

## 11. Instalación y Despliegue

### Requisitos

- PHP ≥ 7.4 con extensiones: `pdo_mysql`, `mbstring`, `gd`, `zip` (para PhpSpreadsheet).
- MySQL ≥ 5.7 o MariaDB ≥ 10.3.
- Apache 2.4 con `mod_rewrite` (XAMPP sirve ambos).
- Composer 2.x.
- npm 8+ (solo para `@tabler/core`; el resto de JS está preempaquetado en `assets/libs/`).

### 1. Clonar y posicionar en XAMPP

```bash
cd C:\xampp\htdocs
git clone <repo> ObservacionesREM_V2
```

### 2. Configurar base de datos

Editar `config/config.php` y poner `define('ENVIRONMENT', 'development')` para entorno local.

### 3. Crear esquema y datos semilla

```bash
mysql -h localhost -u root -p < config/init_db.sql
```

### 4. Aplicar migraciones en orden

```bash
mysql -h localhost -u root -p observaciones_rem < config/create_asignaciones_table.sql
mysql -h localhost -u root -p observaciones_rem < config/sprint3_migration.sql
mysql -h localhost -u root -p observaciones_rem < config/update_establecimientos.sql
mysql -h localhost -u root -p observaciones_rem < config/migration_2026_05_08_limpieza_comunas.sql
mysql -h localhost -u root -p observaciones_rem < config/migration_2026_05_08_reportes.sql
mysql -h localhost -u root -p observaciones_rem < config/migrations/add_tipo_asignacion.sql
```

### 5. Instalar dependencias

```bash
composer install
npm install
```

### 6. Permisos de escritura

`uploads/` debe ser escribible por Apache (en Windows con XAMPP suele ser por defecto).

### 7. Primer acceso

`http://localhost/ObservacionesREM_V2/`

**Usuarios semilla (cambiar inmediatamente en producción):**
- `supervisor1` / `admin123` (Cecilia)
- `registrador1` / `admin123` (Rodrigo Garcés)
- `registrador2` / `admin123` (Victoria Martínez)
- `registrador3` / `admin123` (Roxana Mancilla)
- `registrador4` / `admin123` (Marcelo Horstmeier)

### 8. Worker de reportes (opcional, producción)

Configurar cron cada minuto:

```cron
* * * * * php /var/www/ObservacionesREM_V2/worker_reportes.php
```

### 9. Despliegue a producción

```bash
git archive --format=zip --output=release.zip main
# o git pull en el servidor
```

Etiquetar versiones:

```bash
git tag -a v2.3.0 -m "Versión 2.3.0"
git push origin v2.3.0
```

---

## 12. Seguridad y Auditoría

### Mecanismos implementados

- **Autenticación:** sesiones PHP con cookie `httponly` y `use_only_cookies`.
- **CSRF:** token de 32 bytes hex (`bin2hex(random_bytes(32))`), emitido en `<meta name="csrf-token">` y consumido vía header `X-CSRF-TOKEN`. Validado por `includes/csrf.php` (`CSRF::validateRequest()`) en **todos** los POST/PUT/DELETE.
- **Contraseñas:** `password_hash(PASSWORD_DEFAULT)` (bcrypt) — irreversible. Política: 8+ chars, 1 mayúscula, 1 número. Generación aleatoria de 12 caracteres disponible al crear usuarios.
- **SQL Injection:** todas las queries usan PDO con `emulate_prepares=false` (consultas preparadas nativas).
- **Validación de asignación backend:** `api/observations.php` consulta `Asignacion::tieneAsignacionParaMes(...)` antes de permitir crear/editar; responde 403 si no aplica.
- **API_BASE dinámica:** calculado desde `window.location.pathname` (sin rutas hardcodeadas — bug corregido en v2.2).
- **Roles:** verificados en `index.php` (whitelist + guard por página) y en cada endpoint de `api/`.
- **Reportes de error:** `error_reporting(E_ERROR | E_PARSE)` en APIs de producción.

### Auditoría (T-040, 2026-06-02 — `docs/auditoria-seguridad.md`)

- **24 endpoints API auditados**, **20 modelos** analizados, configuración global revisada.
- **8 problemas críticos** encontrados → todos corregidos.
- **5 problemas medios** y **3 menores** documentados.
- Correcciones CSRF aplicadas a: `api/users.php`, `api/assignments.php`, `api/versioning.php`, `api/update_estado.php`.

### Tablas de auditoría

- `historial_usuarios` — toda acción sobre usuarios (CREACION / ACTIVACION / DESACTIVACION / CAMBIO_PASSWORD / …) con timestamp y detalles.
- `historial_estados` — todo cambio de estado de observación (estado_anterior, estado_nuevo, usuario, comentario).
- `logs` — log general con IP y user agent.

---

## 13. Manuales de Usuario

Doce manuales con mockups SVG/ASCII en `docs/manuales/`. Cada uno aplica a uno o más roles:

| Manual | Ruta | Rol | Mockup |
|--------|------|-----|--------|
| Autenticación y sesión | `docs/manuales/auth-sesion.md` | Ambos | (embebido en markdown) |
| Dashboard | `docs/manuales/dashboard.md` | Ambos | (embebido) |
| Observaciones | `docs/manuales/observaciones.md` | Registrador (escritura) / Supervisor (lectura) | (tabla ASCII) |
| Supervisión (lista) | `docs/manuales/supervision.md` | Supervisor | `docs/manuales/supervision-lista.svg` |
| Supervisión (aprobar) | `docs/manuales/supervision.md` | Supervisor | `docs/manuales/supervision-aprobar.svg` |
| Reportes y exportación | `docs/manuales/reportes-exportacion.md` | Ambos | (embebido) |
| Importación | `docs/manuales/importacion.md` | Registrador | (embebido) |
| Papelera | `docs/manuales/papelera-eliminadas.md` | Supervisor | (embebido) |
| Asignaciones | `docs/manuales/asignaciones.md` | Supervisor | (embebido) |
| Establecimientos | `docs/manuales/establecimientos.md` | Supervisor | (embebido) |
| Usuarios | `docs/manuales/usuarios.md` | Supervisor | (embebido) |
| Versionado (lista) | `docs/manuales/versionado.md` | Supervisor | `docs/manuales/versionado-lista.svg` |
| Versionado (crear) | `docs/manuales/versionado.md` | Supervisor | `docs/manuales/versionado-crear.svg` |

**Estándar de mockups:** los archivos `.svg` se generan desde los `.md` con bloques ASCII-art y se versionan juntos. Cada manual inicia con "Acceso desde el menú", describe la vista principal, columnas/acciones, modal(es) y casos borde.

---

## 14. Convenciones y Patrones de Código

Reglas formales con ejemplos de "qué sí" vs "qué no" para los cuatro lenguajes del proyecto.

### 14.1. PHP

#### Naming
- ✅ Clases en `PascalCase` (`EstablecimientoAsignacion`, `ReportQueue`).
- ❌ `establecimientoAsignacion` para clases; `establecimiento_asignacion` (snake_case) para clases.
- ✅ Métodos en `camelCase` (`getEstablecimientosByRegistrador`).
- ❌ `get_establecimientos_by_registrador` (snake_case).
- ✅ Constantes en `SCREAMING_SNAKE_CASE` (`ESTADO_PENDIENTE`, `ROL_SUPERVISOR`).
- ❌ `estadoPendiente` para constantes globales.

#### Modelos
- ✅ Un modelo por tabla principal + lógica de negocio relacionada.
- ❌ Mezclar acceso a datos de dos dominios distintos en un mismo modelo.

#### Seguridad
- ✅ `password_hash($p, PASSWORD_DEFAULT)` y `password_verify($p, $hash)`.
- ❌ `md5($password)` o `sha1($password)`.
- ✅ Consultas con `?` y `execute([...])`.
- ❌ Concatenar variables en el SQL (`"WHERE id = $id"`).
- ✅ Validar CSRF en cada endpoint POST/PUT/DELETE: `CSRF::validateRequest()`.
- ❌ Confiar en que el frontend "ya verificó".

#### Respuestas JSON
- ✅ Estructura uniforme `{ok: bool, data: ..., error: string}`.
- ❌ Mezclar HTML inline en respuestas que espera `fetch()`.

#### Buenas prácticas
- ✅ Iniciar transacción al hacer operaciones múltiples (`beginTransaction`).
- ❌ Asumir rollback automático en errores.
- ✅ Devolver 403/404 con cuerpo JSON.
- ❌ Lanzar `die('...')` desde un endpoint.

### 14.2. JavaScript

#### Naming y estilo
- ✅ Funciones y variables en `camelCase` (`fetchAPI`, `renderTabla`).
- ❌ `snake_case` o `kebab-case` para variables JS.
- ✅ Constantes en `SCREAMING_SNAKE_CASE` (`API_BASE`, `CSRF_TOKEN`).
- ✅ Comillas simples por defecto, dobles solo en HTML inline.
- ✅ Punto y coma al final de cada sentencia (consistencia con Tabler).
- ❌ `var`; usar `const` y `let`.

#### AJAX
- ✅ Usar helper `fetchAPI(url, options)` (en `assets/js/app.js`) que adjunta CSRF automáticamente.
- ❌ `fetch(url).then(r=>r.json()).then(...)` directo sin CSRF.
- ✅ Manejar errores con `try/catch` y mostrar toast.
- ❌ Ignorar el `catch` y dejar UI en estado roto.

#### DOM
- ✅ Cachear selectores en variables locales al inicio de la función.
- ❌ `document.querySelector` repetido dentro de un loop.
- ✅ Delegar eventos en contenedor cuando la tabla es dinámica.
- ❌ `addEventListener` por cada fila renderizada.

### 14.3. CSS (BEM + Tabler)

#### Naming
- ✅ Bloque `sidebar`, elemento `sidebar__nav-link`, modificador `sidebar__nav-link--active`.
- ❌ Clases planas (`.nav-link-active`) sin bloque.
- ✅ Modificadores siempre con `--` (doble guion).
- ❌ Un solo guion (`-active`).

#### Especificidad
- ✅ Sobreescribir Tabler con selectores específicos en `assets/css/tabler-override.css`.
- ❌ Usar `!important` salvo en último recurso.
- ✅ Variables CSS de Tabler para tokens (`--tblr-primary`).
- ❌ Hardcodear colores en cada selector.

#### Responsive
- ✅ Mobile-first con breakpoints de Tabler (`sm`, `md`, `lg`).
- ❌ Media queries con valores arbitrarios (ej. `@media (max-width: 823px)`).

### 14.4. SQL

#### Convenciones
- ✅ Palabras reservadas en MAYÚSCULAS (`SELECT`, `FROM`, `WHERE`).
- ❌ `select * from observaciones`.
- ✅ Alias descriptivos (`obs`, `est`, `com`).
- ❌ `o`, `e`, `c` (ilegibles en queries grandes).
- ✅ Joins explícitos (`INNER JOIN establecimientos est ON ...`).
- ❌ Joins implícitos con comas en `FROM`.

#### Índices
- ✅ Crear índice compuesto en el orden de las columnas del `WHERE` más frecuente.
- ❌ Índices redundantes con prefijos que ya cubre otro (`(a)` y `(a,b)`).
- ✅ Usar los 6 índices de `migration_2026_05_08_reportes.sql` en queries de reportes.

#### Migraciones
- ✅ Un archivo por cambio semántico, nombrado `migration_YYYY_MM_DD_descripcion.sql`.
- ❌ Modificar `init_db.sql` después del primer despliegue.
- ✅ Toda migración debe ser idempotente o documentar su orden.

#### Fechas y encoding
- ✅ `created_at` y `updated_at` en todas las tablas de hechos.
- ❌ Dejar timestamps implícitos en el código.
- ✅ `utf8mb4_unicode_ci` en toda la BD.
- ❌ `latin1_swedish_ci` (default de MySQL).

### 14.5. Commits (Conventional Commits)

| Prefijo | Uso |
|---------|-----|
| `feat:` | Nueva funcionalidad visible para el usuario |
| `fix:` | Corrección de bug |
| `refactor:` | Cambio interno sin alterar comportamiento |
| `style:` | Solo formato, sin cambio lógico |
| `docs:` | Documentación |
| `chore:` | Mantenimiento (deps, configs) |
| `perf:` | Mejora de rendimiento |

---

## 15. Roadmap y Mejoras Pendientes

Lista priorizada de **deuda técnica y mejoras** identificadas por inspección del código y comentarios. Pendiente de triage por el equipo.

### P1 — Crítico (afecta seguridad o mantenibilidad)

- [ ] **Externalizar credenciales a `.env`:** hoy `config/config.php` tiene el password de producción hardcodeado (`estadi2021`). Migrar a `vlucas/phpdotenv` o variables de entorno del servidor.
- [ ] **Consolidar modelos duplicados:** existen `User`/`Usuario`, `Observation`/`Observacion`, `Location`/`Comuna`+`Establecimiento`, `DeletedObservation`/`PapeleraEliminada`, `Version`/`VersionSistema`. Definir uno canónico y deprecar el otro (rompe APIs que importan el viejo).
- [ ] **Consolidar endpoints duplicados:** conviven `api/versiones.php` y `api/versioning.php`; `api/update_estado.php` y `update_status` dentro de `api/supervision.php`. Centralizar en un único path por recurso.
- [ ] **Forzar HTTPS en producción:** `cookie_secure=0` por defecto; cambiar a `1` con reverse proxy.
- [ ] **Reemplazar contraseñas semilla** `admin123` y rotar al primer despliegue (los usuarios semilla se crean con esa password en `init_db.sql`).

### P2 — Alto (mejora UX o reduce bugs recurrentes)

- [ ] **Reemplazar Chart.js residual:** `openspec/config.yaml` aún referencia `Chart.js 4.4` como dependencia pero el código migró a ApexCharts en v2.3. Limpiar referencias y `assets/libs/`残留 (librerías que ya no se usan).
- [ ] **Quitar `assets/js/notifications.js`** (legacy reemplazado por `toasts.js`) o marcarlo claramente como deprecado.
- [ ] **Migrar assets/libs a npm real:** el repo tiene 18 librerías JS commiteadas en `assets/libs/`. Mover a `package.json` y servirlas vía bundler o CDN pinneado, para tener versionado y updates de seguridad centralizados.
- [ ] **Validación de email y teléfono** en `referentes_establecimientos` (hoy `views/establecimientos.php:215` solo documenta el formato, no valida).
- [ ] **Refactor `index.php`:** la guard de roles es una cadena de `if` repetitivos; reemplazar por un mapa `pagina → roles_permitidos`.
- [ ] **Reemplazar el `Worker` de reportes** (`worker_reportes.php`) por una alternativa sin polling (cron es aceptable, pero documentar el intervalo mínimo y el manejo de procesos concurrentes con `SELECT FOR UPDATE` ya usado).

### P3 — Medio (mejoras funcionales)

- [ ] **API REST versionada:** hoy cada endpoint es un `.php` plano. Considerar prefijo `/api/v1/...` para evolución sin romper clientes.
- [ ] **i18n:** textos hardcodeados en español en `views/`, `includes/`, `assets/js/`. Extraer a archivo de mensajes para futuro soporte bilingüe.
- [ ] **WebSockets/SSE** para notificar al usuario cuando un reporte en cola queda LISTO (hoy requiere polling manual).
- [ ] **Modo oscuro** (Tabler lo soporta nativamente; solo falta el toggle y la persistencia).
- [ ] **Filtros guardados** en reportes (los analistas repiten combinaciones año/trimestre/comuna frecuentemente).
- [ ] **Búsqueda full-text** en observaciones (hoy es `LIKE '%texto%'`; evaluar índice FULLTEXT o Meilisearch externo).
- [ ] **Firma digital** en el Informe de Errores PDF (hoy usa imagen de rúbrica estática vía `Signature Pad`).
- [ ] **Paginación cursor-based** en `api/supervision.php?action=get_filtered` (hoy es offset; con muchos registros degrada).

### P4 — Bajo (nice-to-have)

- [ ] **Dashboard configurable por usuario** (drag-and-drop de widgets).
- [ ] **Exportación de papelera a Excel** (solo disponible en JSON).
- [ ] **Notificaciones por email** cuando se asigna un establecimiento o se recibe una observación.
- [ ] **Auditoría inmutable con hash chain** para `historial_estados` y `historial_usuarios`.
- [ ] **Tests automatizados** (ver §14 — el repo no incluye suite de tests).

### Cambio SpecKit en curso

- **`002-mejorar-reportes-analiticos`:** generar 5 categorías independientes de reportes con filtros compartidos y exportación individual. Spec, plan, data-model, contracts y quickstart ya generados; pendiente `tasks.md` final y ejecución. Restricción AGENTS.md: **no modificar esquema BD** y tratar como sistema desde 0.

### Cambios en `openspec/changes/` no archivados

- `arreglos-post-rediseno` (correcciones menores tras el rediseño Tabler).
- `boxed-layout-tabler`, `migrar-tabler-dashboard`, `rediseno-tabler-admin`, `refactor-observation-modal` (cambios de UI pendientes de archivo o descarte).

---

## 16. Historial de Versiones

| Versión | Fecha | Resumen |
|---------|-------|---------|
| **v2.3.0** | Junio 2026 | Selector de estado en aprobación (S/OBSERVACION/ERROR), Informe de Errores PDF institucional, reportes 5 tabs, asignación anual+temporal, referentes por establecimiento, copia anual, cola de reportes, auditoría de usuarios, migración a ApexCharts y toasts. |
| **v2.2.0** | Mayo 2026 | Limpieza de archivos de desarrollo, fix de ruta API dinámica (404), nueva vista de establecimientos, migración de unificación de comunas, 6 índices de reportes. |
| **v2.1.0** | Mayo 2026 | Reportes con interfaz tabbed (6 vistas), PDF detallado jerárquico con rowspan, 15 nuevas dimensiones de reporte, fix `session_start()` en `api/export.php`. |
| **v2.0.0** | Base | CRUD de observaciones, supervisión básica, asignación de establecimientos, importación Excel, dashboard con Chart.js, exportación Excel/PDF, autenticación con roles, protección CSRF. |

> El changelog completo (bullets) está disponible en `git log --oneline --decorate` y en los `proposal.md` de cada cambio archivado en `openspec/changes/archive/`.

---

## 17. Solución de Problemas

Catálogo de errores frecuentes con diagnóstico y solución paso a paso.

---

### 🔴 Error de conexión a base de datos

**Síntoma:** pantalla en blanco, mensaje `SQLSTATE[HY000] [2002]`, o el dashboard no carga observaciones.

**Diagnóstico:**
1. ¿MySQL está corriendo? `mysqladmin -uroot -p ping` (Linux/XAMPP shell).
2. ¿Las credenciales en `config/config.php` (sección `production` o `development`) coinciden con el servidor?
3. ¿Existe la base de datos `observaciones_rem`? `mysql -uroot -p -e "SHOW DATABASES"`.

**Solución:**
- XAMPP: iniciar MySQL desde el panel.
- Corregir `host` (en producción se usa `10.8.152.199`; en local, `localhost`).
- Ajustar `define('ENVIRONMENT', 'development')` para apuntar a local.
- Si es primera instalación: ejecutar `config/init_db.sql` y luego las 6 migraciones en orden (§11).

---

### 🔴 Página en blanco sin error visible

**Síntoma:** `http://localhost/ObservacionesREM_V2/` muestra página vacía.

**Diagnóstico:** `error_reporting` está silenciado en producción.

**Solución:**
1. Temporal: editar `config/config.php` y poner `define('ENVIRONMENT', 'development')`.
2. Revisar `error_log` de Apache (`xampp/apache/logs/error.log`).
3. Errores típicos: extensión `pdo_mysql` no habilitada (en `php.ini` quitar `;` de `extension=pdo_mysql`), archivo `.htaccess` con reglas inválidas, sesión no escribible.

---

### 🔴 "No autenticado" al exportar

**Síntoma:** la descarga de Excel/PDF devuelve JSON `{"error":"no autenticado"}` en vez del archivo.

**Diagnóstico:** `session_start()` llamado **después** de incluir `config.php` o de operaciones que requieren sesión.

**Solución:** verificar que `api/export.php` y todos los endpoints hagan `session_start()` antes de cualquier `$_SESSION[...]` o `header(...)`. (Corregido en v2.1; verificar versión del archivo).

---

### 🔴 Error 404 al consumir `/api/...`

**Síntoma:** todas las llamadas AJAX devuelven 404.

**Diagnóstico:** ruta de la API hardcodeada en `assets/js/app.js` (versión anterior a v2.2).

**Solución:** verificar que `app.js` calcule `API_BASE` con:
```js
const API_BASE = window.location.pathname.replace(/\/[^/]*$/, '/api/');
```
No debe haber `const API_BASE = '/api';` ni similar hardcodeado.

---

### 🔴 Token CSRF inválido (error 403 "Invalid CSRF token")

**Síntoma:** formularios o acciones devuelven 403 tras unos minutos de uso.

**Diagnóstico:** token CSRF expirado o no rotado tras login.

**Solución:** verificar que `includes/csrf.php` regenere el token tras `login` exitoso. Si el problema persiste, limpiar cookies del navegador y reintentar. Si está en HTTPS, confirmar que `cookie_secure=1` y que el sitio está detrás de HTTPS (no HTTP).

---

### 🔴 Registrador no puede crear observación (403 "Sin asignación para el mes")

**Síntoma:** un registrador intenta crear observación para un establecimiento al que está asignado, pero recibe 403.

**Diagnóstico:** la asignación es **anual** con meses específicos, y el mes que intenta crear no está en `meses`.

**Solución:** en **Asignaciones** (supervisor), revisar la fila del registrador y:
- Si la asignación es anual con `meses=ALL`, debería funcionar todo el año.
- Si tiene meses específicos, ampliar la lista o cambiar a `ALL`.
- Verificar que no hay una **reasignación temporal** activa que haya movido ese establecimiento a otro registrador en ese mes.

---

### 🔴 Importación Excel marca todas las filas como "no se encontró establecimiento"

**Síntoma:** preview de importación muestra 0 filas válidas aunque los nombres coinciden.

**Diagnóstico:** las cabeceras del Excel son antiguas (lowercase, con espacios) o los códigos no coinciden con `establecimientos.codigo_establecimiento`.

**Solución:**
1. Descargar la **plantilla oficial** desde "Importar → Descargar plantilla" (asegura cabeceras correctas).
2. Si la columna de código viene vacía, completar con el código DEIS del establecimiento.
3. Si se importan nombres, verificar coincidencia exacta con `establecimientos.nombre` (sin abreviaciones).

---

### 🔴 Informe de Errores PDF en blanco o con caracteres rotos

**Síntoma:** PDF se genera pero sin contenido o con `?` en vez de tildes.

**Diagnóstico:** charset de la consulta o del template TCPDF mal configurado.

**Solución:** verificar que la query y TCPDF usen `utf8mb4`. Si TCPDF pierde tildes, forzar en el constructor: `$pdf->SetFont('helvetica', '', 10);` y luego pasar los strings con `utf8_decode` o `iconv('UTF-8','Windows-1252', $texto)` según el caso. (El sistema usa TCPDF 6.10 con codificación por defecto `UTF-8`; debería funcionar, pero si la fuente instalada en el servidor no soporta el carácter,会发生 fallback.)

---

### 🔴 Worker de reportes no procesa la cola

**Síntoma:** los reportes quedan eternamente en estado `PENDIENTE`.

**Diagnóstico:** el cron no está configurado o el script falla.

**Solución:**
1. `crontab -l` debe contener la línea `* * * * * php /ruta/worker_reportes.php`.
2. Ejecutar manualmente para ver el error: `php worker_reportes.php` desde la raíz del proyecto.
3. Revisar permisos: el usuario que corre el cron debe tener acceso de lectura a todos los modelos y escritura a `uploads/`.

---

## 18. Recursos Adicionales

### Documentación interna

- **Manual HTML extenso:** `MANUAL_REGISTRO_OBSERVACIONES.html` (~67 KB, manual operativo clásico).
- **Resumen en texto plano:** `MÓDULOS PRINCIPALES DEL SISTEMA.txt` — listado rápido de los 12 módulos.
- **Auditoría de seguridad:** `docs/auditoria-seguridad.md` (T-040, 2026-06-02).
- **Manuales de usuario:** `docs/manuales/` — 12 manuales con mockups SVG.

### Especificaciones SpecKit y Spec

- **Índice maestro de specs:** `specs/INDICE.md` — 11 módulos con descripción breve y link a la spec de cada uno.
- **Specs por módulo:** `specs/mod-auth.md`, `specs/obs-modulo.md`, `specs/obs-crear-observacion.md`, `specs/mod-supervision.md`, `specs/mod-asignaciones.md`, `specs/mod-establecimientos.md`, `specs/mod-importacion.md`, `specs/mod-exportacion.md`, `specs/mod-usuarios.md`, `specs/mod-eliminadas.md`, `specs/versiones.md`.
- **Cambio activo SpecKit:** `specs/002-mejorar-reportes-analiticos/` (spec.md, plan.md, data-model.md, research.md, quickstart.md, contracts/, checklists/, tasks.md).
- **Cambio histórico (unificación de specs):** `specs/001-unificar-specs/`.

### OpenSpec (changelog estructurado)

- **Configuración:** `openspec/config.yaml` — reglas de proposal y tasks.
- **Cambios archivados (13):** `openspec/changes/archive/2026-05-25-…` a `…/2026-06-01-dashboard-tabler-features/`.
- **Cambios en curso:** `openspec/changes/arreglos-post-rediseno/`, `boxed-layout-tabler/`, `migrar-tabler-dashboard/`, `rediseno-tabler-admin/`, `refactor-observation-modal/`.
- **Specs aprobadas (delta):** `openspec/specs/` (generadas al archivar cambios).

### SpecKit framework

- **Templates:** `.specify/templates/` — `spec-template.md`, `plan-template.md`, `tasks-template.md`, `constitution-template.md`, `checklist-template.md`.
- **Comandos opencode:** `.opencode/commands/` — `speckit.constitution.md`, `speckit.specify.md`, `speckit.plan.md`, `speckit.tasks.md`, `speckit.implement.md`, `speckit.clarify.md`, `speckit.checklist.md`, `speckit.analyze.md`, `speckit.git.*`, `opsx-*` (4 comandos).
- **AGENTS.md:** `AGENTS.md` — bloque SPECKIT que apunta al cambio activo y a las restricciones del proyecto.

### Skills de opencode

- `openspec-apply-change`, `openspec-archive-change`, `openspec-explore`, `openspec-propose` (en `.opencode/skills/`).
- `leer-rem-anexo-excel` (en `~/.agents/skills/`) — lectura y validación de archivos Excel REM Serie Anexo.

### Dependencias clave (versiones exactas)

- `composer.json` → `phpoffice/phpspreadsheet ^5.4`, `tecnickcom/tcpdf ^6.10`.
- `package.json` → `@tabler/core ^1.4.0`.
- Ver `composer.lock` y `package-lock.json` para hashes exactos.

### Convenciones SpecKit (recordatorio)

- **AGENTS.md del proyecto** declara:
  - BD existente y poblada — **no** modificar esquema ni migraciones para el cambio activo.
  - Sistema siempre desde 0 — ignorar código existente al implementar el cambio.
  - Cada módulo incluye manual de usuario con mockups en `docs/manuales/`.

---

## 📌 Cierre del Descubrimiento

Este README es el resultado del comando `/speckit.discover` aplicado al sistema **ObservacionesREM_V2 v2.3.0** mediante ingeniería inversa. Captura la visión completa del sistema tal como existe en producción y constituye la **fuente de la verdad** para los siguientes comandos de SpecKit.

> **¿Deseas usar este README.md como base para iniciar el comando `/speckit.constitution`?**
> Si la respuesta es sí, el siguiente paso generará la constitución del proyecto (principios, valores, restricciones y governanza técnica) tomando este documento como input.
