# 📚 Índice Maestro de Especificaciones - Sistema Observaciones REM

## Información General del Proyecto

- **Nombre del Sistema**: Observaciones REM V2
- **Propósito**: Gestión, supervisión y reporte de observaciones en reportes REM de establecimientos de salud.
- **Roles Principales**:
  - **Registrador**: Registra observaciones, importa datos, ve sus propios reportes.
  - **Supervisor**: Gestiona usuarios, asignaciones, supervisa observaciones, exporta reportes globales, gestiona establecimientos.

---

## 📂 Listado de Módulos

| ID | Módulo | Descripción Breve | Archivo de Especificación |
|----|--------|-------------------|---------------------------|
| **VER** | Versionado | Snapshots manuales y rollback del código fuente. | `specs/versiones.md` |
| **MOD-OBS** | Observaciones | CRUD completo de observaciones, historial y estadísticas. | `specs/obs-modulo.md`<br>`specs/obs-crear-observacion.md` |
| **MOD-USR** | Usuarios | Gestión de cuentas, roles, contraseñas y auditoría. | `specs/mod-usuarios.md` |
| **MOD-SUP** | Supervisión | Revisión, aprobación/cancelación y filtrado de observaciones. | `specs/mod-supervision.md` |
| **MOD-ASN** | Asignaciones | Vinculación de establecimientos a registradores (anual/temporal). | `specs/mod-asignaciones.md` |
| **MOD-IMP** | Importación | Carga masiva de observaciones desde Excel + plantilla descargable. | `specs/mod-importacion.md` |
| **MOD-EXP** | Exportación | Generación síncrona de reportes en Excel, PDF y CSV (20+ dimensiones). | `specs/mod-exportacion.md` |
| **MOD-LOC** | Establecimientos | Catálogo de establecimientos y comunas. | `specs/mod-establecimientos.md` |
| **MOD-DEL** | Eliminadas | Papelera de reciclaje para observaciones (Restaurar/Eliminar permanente). | `specs/mod-eliminadas.md` |
| **MOD-AUTH** | Autenticación | Login, Logout, Cambio de año y gestión de sesión. | `specs/mod-auth.md` |
| ~~LOGIN~~ | ~~Login (obsoleto)~~ | ~~Especificación antigua reemplazada por MOD-AUTH~~ | ~~`specs/login.md`~~ → Ver `specs/mod-auth.md` |

---

## 🔐 Matriz de Permisos Consolidada

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| **Autenticación** | ✅ | ✅ |
| **Crear Observación** | ✅ (Solo asignados) | ❌ |
| **Ver Observaciones** | ✅ (Solo propias) | ✅ (Todas) |
| **Supervisar (Aprobar/Cancelar)** | ❌ | ✅ |
| **Importar Masivo** | ✅ | ❌ |
| **Exportar Reportes** | ✅ (Solo propios) | ✅ (Todos) |
| **Gestionar Usuarios** | ❌ | ✅ |
| **Gestionar Asignaciones** | ❌ | ✅ |
| **Gestionar Establecimientos** | ❌ | ✅ |
| **Acceso a Papelera** | ❌ | ✅ |

---

## 📝 Reglas de Negocio Clave

1.  **Asignaciones**: Son anuales por defecto, pero permiten reasignación temporal por meses (ej. vacaciones).
2.  **Duplicados**: No se bloquea la creación de observaciones duplicadas (criterio del usuario).
3.  **Eliminación**: Híbrida. Desde API es Hard Delete, desde Supervisión es Soft Delete (Papelera).
4.  **Importación**: Solo Excel. Validación estricta de establecimiento. Flujo de dos pasos (Preview -> Confirm).
5.  **Exportación**: Asíncrona. Notificación al usuario cuando el reporte está listo.
6.  **Contraseñas**: Hashing irreversible. Política estándar (8+, mayúscula, número). Generación aleatoria disponible.
7.  **Auditoría**: Historial completo de cambios en usuarios y observaciones (cambios de estado).
8.  **Año de Trabajo**: Contexto global por sesión. Cambiable sin re-login.

---

## 🚀 Próximos Pasos Sugeridos

1.  **Revisión Final**: Validar que todas las especificaciones reflejen correctamente los requerimientos del negocio.
2.  **Diseño de Base de Datos**: Crear el esquema SQL basado en las entidades identificadas (Usuarios, Observaciones, Historial, Asignaciones, Establecimientos, etc.).
3.  **Planificación de Sprints**: Dividir el desarrollo en iteraciones lógicas (ej. Sprint 1: Auth + Usuarios + Establecimientos).
4.  **Implementación**: Comenzar el desarrollo siguiendo las especificaciones como guía técnica.

---

*Documento generado automáticamente como resumen del proceso de especificación.*