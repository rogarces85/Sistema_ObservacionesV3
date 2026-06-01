# Manual de Usuario - Módulo de Observaciones

## Descripción General

El módulo de **Observaciones** permite gestionar el registro, seguimiento y control de observaciones sobre los archivos REM enviados por los establecimientos de salud. Cada observación registra un error, revisión, fuera de plazo o constancia de sin observaciones.

---

## 1. Listado de Observaciones

### Acceso
- Menú lateral → **Observaciones**

### Vista Principal

```
┌─────────────────────────────────────────────────────────────────────┐
│  Observaciones                                        [+ Nueva Obs] │
│  Gestiona y realiza seguimiento de tus registros REM                │
├─────────────────────────────────────────────────────────────────────┤
│  [Total: 245]  [Pendientes: 89]  [Aprobados: 120]  [Errores: 36]   │
├─────────────────────────────────────────────────────────────────────┤
│  Filtros                                                            │
│  [Buscar establecimiento...] [Mes ▼] [Estado ▼] [Tipo ▼] [Buscar]  │
├─────────────────────────────────────────────────────────────────────┤
│  Establecimiento     │ Mes    │ Serie/Hoja │ Tipo    │ Estado │ ... │
│  ────────────────────┼────────┼────────────┼─────────┼────────┼─────│
│  CESFAM Osorno       │ Enero  │ SERIE A    │ ERROR   │ Pend.  │ 👁 ✏️│
│  Hospital Osorno     │ Enero  │ SERIE BS   │ S/OBS   │ Aprob. │ 👁  │
│  CECOSF Rahue        │ Feb.   │ SERIE P    │ F/PLAZO │ Error  │ 👁 ✏️│
│  ...                 │ ...    │ ...        │ ...     │ ...    │ ... │
├─────────────────────────────────────────────────────────────────────┤
│  Mostrando 1-50 de 245    [< 1] [2] [3] [4] [5] >                  │
└─────────────────────────────────────────────────────────────────────┘
```

### Columnas de la Tabla

| Columna | Descripción |
|---------|-------------|
| Establecimiento | Nombre corto y comuna del establecimiento |
| Mes | Mes de la observación (Enero, Febrero, etc.) |
| Serie / Hoja | Serie REM (A, BS, BM, P, ANEXO, D) y hoja específica |
| Tipo | Tipo de observación: ERROR, S/OBSERVACION, REVISAR, F/PLAZO |
| Estado | Pendiente, Aprobado, Rechazado, Error, Justificado |
| Registrado por | Nombre del usuario que creó la observación |
| Acciones | Ver detalle, Editar (si tiene permisos), Eliminar (solo supervisor) |

### Filtros Disponibles

- **Buscar**: Texto libre que busca en establecimiento y detalle
- **Mes**: Filtra por mes específico
- **Estado**: Filtra por estado actual
- **Tipo Error**: Filtra por tipo de observación

### Paginación
- 50 registros por página
- Navegación numérica con anterior/siguiente

---

## 2. Crear Nueva Observación

### Acceso
- Botón **Nueva Observación** (solo rol Registrador con establecimientos asignados)

### Formulario

```
┌──────────────────────────────────────────────────────┐
│  Nueva Observación                              [×]  │
├──────────────────────────────────────────────────────┤
│  Mes: [Enero ▼]    Establecimiento: [CESFAM Osorno ▼]│
│                                                      │
│  Tipo: [ERROR ▼]   Serie: [SERIE A ▼]               │
│                                                      │
│  Hoja REM: [A01 ▼]  Plazo: [Dentro de Plazo ▼]      │
│                                                      │
│  Detalle:                                            │
│  ┌──────────────────────────────────────────────┐   │
│  │ Error en columna de nacidos vivos,           │   │
│  │ la suma no coincide con el total reportado   │   │
│  └──────────────────────────────────────────────┘   │
│                                                      │
│  Clasificación: [____________________________]       │
│                                                      │
│              [Cancelar]          [Guardar]           │
└──────────────────────────────────────────────────────┘
```

### Campos del Formulario

| Campo | Obligatorio | Descripción |
|-------|-------------|-------------|
| Mes | Sí | Mes al que corresponde la observación |
| Establecimiento | Sí | Establecimiento de salud (solo los asignados al registrador) |
| Tipo | Sí | ERROR, S/OBSERVACION, REVISAR, F/PLAZO |
| Serie | Sí | SERIE A, BS, BM, P, ANEXO, D |
| Hoja REM | Sí* | Hoja específica de la serie (*excepto S/OBSERVACION) |
| Detalle | Sí | Descripción textual de la observación |
| Plazo de Entrega | No | Dentro de Plazo / Fuera de Plazo |
| Clasificación | No | Clasificación de respuesta (uso supervisor) |

### Validaciones
- El establecimiento debe estar asignado al registrador para el mes seleccionado
- El establecimiento debe estar activo
- Todos los campos obligatorios deben completarse
- Para tipo S/OBSERVACION, el campo Hoja REM se oculta automáticamente

---

## 3. Editar Observación

### Permisos
- **Registrador**: Solo puede editar sus propias observaciones en estado **Pendiente**
- **Supervisor**: Puede editar cualquier observación

### Acceso
- Botón ✏️ (editar) en la fila de la observación

### Comportamiento
- Se cargan los datos actuales en el mismo formulario de creación
- El título del modal cambia a "Editar Observación"
- Se valida **last-write-wins**: si otro usuario modificó la observación mientras se editaba, se muestra un error indicando que debe recargar los datos

---

## 4. Ver Detalle con Historial

### Acceso
- Botón 👁 (ver) en la fila de la observación

### Vista de Detalle

```
┌──────────────────────────────────────────────────────────────────┐
│  Detalle de Observación                                    [×]   │
├──────────────────────────────────────────────────────────────────┤
│  CESFAM Osorno                               [Pendiente]         │
│  Osorno                                                          │
├──────────────────────────────────────────────────────────────────┤
│  [Mes/Año: Enero 2026]  [Serie/Hoja: SERIE A / A01]  [Tipo: ERR]│
├──────────────────────────────────────────────────────────────────┤
│  Detalle de la Observación                                       │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ Error en columna de nacidos vivos, la suma no coincide     │ │
│  │ con el total reportado en la hoja A01                      │ │
│  └────────────────────────────────────────────────────────────┘ │
├──────────────────────────────────────────────────────────────────┤
│  Plazo: Dentro de Plazo    Clasificación: -                      │
│                                                                  │
│  Registrado por: Juan Pérez                                      │
│  Fecha creación: 15/01/2026 10:30                                │
│  Última actualización: 15/01/2026 10:30                          │
├──────────────────────────────────────────────────────────────────┤
│  Historial de Cambios                                            │
│                                                                  │
│  ● Inicio → Pendiente          15/01/2026 10:30                 │
│    Por: Juan Pérez                                               │
│    Registro inicial                                              │
│                                                                  │
│  ● Pendiente → Aprobado        16/01/2026 14:20                 │
│    Por: María Supervisor                                         │
│    Revisado y aprobado                                           │
├──────────────────────────────────────────────────────────────────┤
│  [Eliminar]                              [Cerrar]               │
└──────────────────────────────────────────────────────────────────┘
```

### Historial de Cambios
- Muestra cronológicamente todos los cambios de estado
- Cada entrada incluye: estado anterior → nuevo estado, fecha/hora, usuario y comentario

---

## 5. Eliminar Observación

### Permisos
- **Solo Supervisor** puede eliminar observaciones

### Acceso
- Botón 🗑️ (eliminar) en la fila de la observación, o desde el modal de detalle

### Confirmación

```
┌─────────────────────────────────────────────┐
│  Confirmar Eliminación                [×]   │
├─────────────────────────────────────────────┤
│  ¿Está seguro que desea eliminar esta       │
│  observación?                               │
│                                             │
│  Esta acción no se puede deshacer.          │
│                                             │
│              [Cancelar]    [Eliminar]       │
└─────────────────────────────────────────────┘
```

### Comportamiento
- **Eliminación física**: El registro se elimina permanentemente de la base de datos
- No existe papelera ni recuperación
- Se registra en el historial antes de eliminar

---

## 6. Estadísticas Rápidas

En la parte superior del listado se muestran 4 tarjetas con contadores:

| Tarjeta | Descripción |
|---------|-------------|
| Total | Cantidad total de observaciones del año |
| Pendientes | Observaciones en estado pendiente |
| Aprobados | Observaciones aprobadas |
| Errores | Observaciones con tipo ERROR |

---

## 7. Permisos por Rol

### Registrador
- Ve solo observaciones de establecimientos asignados
- Puede crear observaciones para sus establecimientos asignados en el mes correspondiente
- Puede editar solo sus propias observaciones pendientes
- No puede eliminar observaciones

### Supervisor
- Ve todas las observaciones de todos los establecimientos
- Puede editar cualquier observación
- Puede eliminar observaciones (eliminación física)
- Gestiona asignaciones de establecimientos

---

## 8. Estados de Observación

| Estado | Color | Descripción |
|--------|-------|-------------|
| Pendiente | Amarillo | Recién creada, pendiente de revisión |
| Aprobado | Verde | Revisada y aprobada por supervisor |
| Rechazado | Rojo | Rechazada por supervisor |
| Error | Rojo | Registrada como error |
| Justificado | Azul | Justificada por el establecimiento |

---

## 9. Tipos de Observación

| Tipo | Descripción |
|------|-------------|
| ERROR | Error encontrado en los datos del REM |
| S/OBSERVACION | Sin observaciones, todo correcto |
| REVISAR | Requiere revisión adicional |
| F/PLAZO | Fuera de plazo de entrega |
