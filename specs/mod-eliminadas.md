# Especificación: MOD-DEL — Observaciones Eliminadas (Papelera)

## Historia de Usuario

> **Como** Supervisor del sistema,
> **necesito** gestionar las observaciones eliminadas (papelera de reciclaje),
> **para** poder recuperar registros borrados por error o eliminarlos permanentemente para limpiar la base de datos.

---

## Descripción General

Este módulo actúa como la **Papelera de Reciclaje** del sistema. Cuando una observación se elimina desde el módulo de Supervisión, no se borra físicamente de inmediato, sino que se mueve a esta sección.

El Supervisor puede revisar el contenido de la papelera, **restaurar** observaciones (devolviéndolas al estado activo) o **eliminarlas permanentemente** (borrado irreversible). No se generan estadísticas para este módulo; su función es puramente operativa.

---

## Funciones del Módulo

### DEL-001: Listar Observaciones Eliminadas

**Descripción**: Muestra la lista de observaciones que han sido movidas a la papelera.

**Reglas de Negocio**:
- **Filtros**: Soporta los mismos filtros que el módulo de Supervisión (Año, Mes, Establecimiento, Comuna, Búsqueda de texto).
- **Datos Visibles**: Muestra la información original de la observación y, adicionalmente, la fecha de eliminación y el usuario que la eliminó.

### DEL-002: Restaurar Observación

**Descripción**: Devuelve una o varias observaciones a la tabla principal de observaciones activas.

**Reglas de Negocio**:
- **Estado**: La observación recupera su estado anterior a la eliminación (ej. si estaba "Pendiente", vuelve a estar "Pendiente").
- **Operación**: Soporta selección individual y múltiple (masivo).
- **Validación**: Si el establecimiento original fue desactivado, la observación se restaura pero quedará ligada a un establecimiento inactivo.

### DEL-003: Eliminación Permanente

**Descripción**: Borra físicamente el registro de la base de datos.

**Reglas de Negocio**:
- **Irreversibilidad**: Una vez ejecutada, la acción **no se puede deshacer**.
- **Confirmación**: Requiere una confirmación explícita del usuario (ej. checkbox "Entiendo que esto no se puede deshacer").
- **Operación**: Soporta selección individual y múltiple.

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Ver Papelera | ❌ No permitido | ✅ |
| Restaurar | ❌ No permitido | ✅ |
| Eliminar Permanentemente | ❌ No permitido | ✅ |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-DEL-001 | Restauración | `Observación(es) restaurada(s) exitosamente.` |
| MSG-DEL-002 | Eliminación Permanente | `Observación(es) eliminada(s) permanentemente.` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-DEL-101 | Sin selección | `Error: Debe seleccionar al menos una observación.` |
| MSG-DEL-102 | Error de BD | `Error: No se pudo completar la operación.` |
| MSG-DEL-103 | Permiso denegado | `Error: No tiene permisos para acceder a la papelera.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Restaurar observación eliminada por error
```gherkin
Dado que soy un Supervisor autenticado
Y estoy en la pantalla de Observaciones Eliminadas
Y veo la observación #1234 que fue eliminada ayer
Cuando selecciono la observación #1234
Y presiono "Restaurar"
Entonces la observación vuelve a aparecer en el listado principal de Supervisión
Y desaparece de la lista de Eliminadas
```

### Escenario: Eliminación permanente masiva con confirmación
```gherkin
Dado que soy un Supervisor autenticado
Y he seleccionado 10 observaciones en la papelera para borrarlas definitivamente
Cuando presiono "Eliminar Permanentemente"
Y confirmo la advertencia de irreversibilidad
Entonces los registros se borran físicamente de la base de datos
Y el sistema muestra "10 observación(es) eliminada(s) permanentemente"
```

---

## Mockup ASCII

### Pantalla de Papelera de Reciclaje

```
+==============================================================================+
|  OBSERVACIONES ELIMINADAS (PAPELERA)                                         |
+==============================================================================+
|  Filtros:                                                                    |
|  [ Año: 2026 ▼ ] [ Mes: Todos ▼ ] [ Establecimiento: Todos ▼ ]               |
|  [ Buscar texto... ]                                   [ 🔍 Filtrar ]        |
+==============================================================================+
|                                                                              |
|  [ x ] Seleccionar Todo    |   Acciones Masivas:                             |
|                            |   [ ♻️ Restaurar ] [ 🗑️ Eliminar Permanente ]   |
|                            +-------------------------------------------------+
|  +---+-----+-------+------------------+-------------+---------------------+  |
|  | [ ]| ID  | Mes   | Establecimiento  | Tipo Error  | Eliminado por / Fecha|  |
|  +---+-----+-------+------------------+-------------+---------------------+  |
|  | [x]| 1040| Feb   | Hospital Norte   | ERROR       | admin / 12/05/26    |  |
|  | [x]| 1039| Feb   | CESFAM Central   | OBSERVACIÓN | admin / 12/05/26    |  |
|  | [ ]| 1035| Ene   | Clínica Andes    | ERROR       | admin / 10/05/26    |  |
|  +---+-----+-------+------------------+-------------+---------------------+  |
|                                                                              |
|  Mostrando 1-10 de 45 registros eliminados                                   |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Acceso Exclusivo | ✅ Aceptada → Solo Supervisores |
| 2 | Restauración | ✅ Aceptada → Recupera estado y datos originales |
| 3 | Eliminación Permanente | ✅ Aceptada → Borrado físico irreversible |
| 4 | Filtros | ✅ Aceptada → Mismos filtros que Supervisión |
| 5 | Operaciones Masivas | ✅ Aceptada |
| 6 | Retención | ✅ Aceptada → Indefinida hasta acción manual |
| 7 | Estadísticas | ✅ Modificada → **Estadísticas disponibles**: total eliminadas, por estado original, por mes, por eliminador |
| 8 | Seguridad | ✅ Aceptada |
| 9 | Restauración con historial | ✅ Nueva → Al restaurar se registra entrada en `historial_estados` con comentario "Observación restaurada desde papelera" |
