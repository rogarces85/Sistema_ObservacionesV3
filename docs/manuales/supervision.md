# Manual de Usuario - Módulo de Supervisión

## Descripción General

El módulo de **Supervisión** permite a los supervisores revisar, aprobar, cancelar y eliminar observaciones REM registradas por los usuarios con rol de Registrador. Este módulo es exclusivo del rol **Supervisor** y no está disponible para Registradores.

### Características Principales

- **Filtrado avanzado**: Por estado, mes, tipo de error, comuna, establecimiento y registrador
- **Operaciones individuales**: Aprobar, cancelar o eliminar una observación desde la tabla
- **Operaciones masivas**: Seleccionar múltiples observaciones y aplicar la misma acción
- **Soft delete**: Las observaciones eliminadas se mueven a la papelera de reciclaje, no se borran permanentemente
- **Historial completo**: Cada cambio de estado queda registrado con usuario, fecha y comentario
- **Paginación**: 50 registros por página con navegación numérica

---

## 1. Acceso al Módulo

### Requisitos
- Rol: **Supervisor** (los Registradores obtienen acceso denegado)

### Navegación
- Menú lateral → **Gestión** → **Supervisión**

---

## 2. Vista Principal - Listado de Observaciones

### Mockup: Lista de Observaciones

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│  Panel de Supervisión                                        [✓ Aprobar] [✗ Cancelar]  │
│  Revise y gestione las observaciones registradas                [🗑 Eliminar]            │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  [📄 Total: 245]  [🕐 Pendientes: 89]  [✓ Aprobados: 120]  [⚠ Errores: 36]            │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  Filtros                                                                                │
│  [Buscar...] [Mes ▼] [Estado ▼] [Tipo ▼] [Comuna ▼] [Establecimiento ▼] [Registrador ▼]│
│  [🔍 Buscar] [Limpiar]                                                                  │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  ☐ Seleccionar todas                                                                    │
├──────┬────┬──────────────────┬────────┬────────────┬─────────┬──────────┬───────────────┤
│  ☐   │ ID │ Establecimiento  │ Mes    │ Serie/Hoja │ Tipo    │ Estado   │ Acciones      │
│  ────┼────┼──────────────────┼────────┼────────────┼─────────┼──────────┼───────────────│
│  ☐   │#45 │ CESFAM Osorno    │ Enero  │ SERIE A    │ ERROR   │ Pendiente│ 👁 ✓ ✗ 🗑     │
│      │    │ Osorno           │        │ A01        │         │          │               │
│  ☐   │#44 │ Hospital Osorno  │ Enero  │ SERIE BS   │ S/OBS   │ Aprobado │ 👁            │
│      │    │ Osorno           │        │ B          │         │          │               │
│  ☐   │#43 │ CECOSF Rahue     │ Feb    │ SERIE P    │ F/PLAZO │ Pendiente│ 👁 ✓ ✗ 🗑     │
│      │    │ Osorno           │        │ P01        │         │          │               │
├──────┴────┴──────────────────┴────────┴────────────┴─────────┴──────────┴───────────────┤
│  Mostrando 1-50 de 245          [< 1] [2] [3] [4] [5] >                                │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

### Columnas de la Tabla

| Columna | Descripción |
|---------|-------------|
| ☐ Checkbox | Seleccionar para operaciones masivas |
| ID | Identificador único de la observación |
| Establecimiento | Nombre corto y comuna del establecimiento |
| Mes | Mes de la observación |
| Serie / Hoja | Serie REM y hoja específica |
| Tipo | Tipo de observación: ERROR, S/OBSERVACION, REVISAR, F/PLAZO |
| Estado | Pendiente, Aprobado, Rechazado, Error, Justificado |
| Registrado por | Nombre del usuario que creó la observación |
| Fecha | Fecha de creación de la observación |
| Acciones | Ver detalle, Aprobar, Cancelar, Eliminar (solo en pendientes) |

### Acciones Disponibles por Estado

| Estado | Ver Detalle | Aprobar | Cancelar | Eliminar |
|--------|:-----------:|:-------:|:--------:|:--------:|
| Pendiente | ✓ | ✓ | ✓ | ✓ |
| Aprobado | ✓ | - | - | - |
| Rechazado | ✓ | - | - | - |
| Error | ✓ | - | - | - |
| Justificado | ✓ | - | - | - |

---

## 3. Filtros

### Filtros Disponibles

| Filtro | Tipo | Descripción |
|--------|------|-------------|
| Buscar | Texto libre | Busca en establecimiento y detalle de la observación |
| Mes | Select | Filtra por mes específico (Enero a Diciembre) |
| Estado | Select | Filtra por estado actual de la observación |
| Tipo Error | Select | Filtra por tipo de observación |
| Comuna | Select | Filtra por comuna del establecimiento |
| Establecimiento | Select | Se habilita al seleccionar una comuna |
| Registrador | Select | Filtra por usuario que registró la observación |

### Uso de Filtros

1. Seleccione los criterios de filtrado deseados
2. Haga clic en **Buscar** para aplicar los filtros
3. Use **Limpiar** para restablecer todos los filtros

### Filtro en Cascada: Comuna → Establecimiento

- Al seleccionar una **Comuna**, el campo **Establecimiento** se habilita
- Solo se muestran los establecimientos pertenecientes a la comuna seleccionada
- Si no se selecciona comuna, el campo establecimiento permanece deshabilitado

---

## 4. Operaciones Individuales

### 4.1 Ver Detalle

**Acceso**: Botón 👁 (ojo) en la fila de cualquier observación

#### Mockup: Modal de Detalle

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
│  [Cerrar]                                                        │
└──────────────────────────────────────────────────────────────────┘
```

### 4.2 Aprobar Observación

**Acceso**: Botón ✓ (check verde) en filas con estado **Pendiente**

#### Mockup: Modal de Aprobación

```
┌──────────────────────────────────────────────────────┐
│  ✓ Aprobar Observación                         [×]   │
├──────────────────────────────────────────────────────┤
│  ¿Aprobar esta observación?                          │
│                                                      │
│  Clasificación de Respuesta *                        │
│  ( ) Sin Observación    ( ) Error                    │
│                                                      │
│  Clasificación: [Sin clasificar ▼]                   │
│    - Corregido                                       │
│    - Error                                           │
│    - Sin respuesta del Establecimiento               │
│    - Respuesta incorrecta de Establecimiento         │
│                                                      │
│  Detalle Error: [____________________________]       │
│                                                      │
│  Comentario: [____________________________]          │
│                                                      │
│              [Cancelar]          [Confirmar]         │
└──────────────────────────────────────────────────────┘
```

#### Campos del Formulario

| Campo | Obligatorio | Descripción |
|-------|:-----------:|-------------|
| Clasificación de Respuesta | Sí | Determina el resultado: "Sin Observación" o "Error" |
| Clasificación | No | Categoría adicional de clasificación |
| Detalle Error | No | Descripción del error si aplica |
| Comentario | No | Comentario opcional para el historial |

#### Comportamiento

- Si selecciona **Sin Observación**: La observación pasa a estado **Aprobado**
- Si selecciona **Error**: La observación pasa a estado **Aprobado** con tipo de error actualizado
- El comentario se registra en el historial de cambios

### 4.3 Cancelar Observación

**Acceso**: Botón ✗ (X amarilla) en filas con estado **Pendiente**

```
┌──────────────────────────────────────────────────────┐
│  ✗ Cancelar Observación                        [×]   │
├──────────────────────────────────────────────────────┤
│  ¿Cancelar esta observación?                         │
│                                                      │
│  Comentario:                                         │
│  ┌──────────────────────────────────────────────┐   │
│  │ Motivo de la cancelación...                  │   │
│  └──────────────────────────────────────────────┘   │
│                                                      │
│              [Cancelar]          [Confirmar]         │
└──────────────────────────────────────────────────────┘
```

#### Comportamiento

- La observación pasa a estado **Rechazado**
- El comentario se registra en el historial de cambios
- El campo comentario es **opcional**

### 4.4 Eliminar Observación (Soft Delete)

**Acceso**: Botón 🗑 (basurero rojo) en filas con estado **Pendiente**

```
┌──────────────────────────────────────────────────────┐
│  🗑 Eliminar Observación                       [×]   │
├──────────────────────────────────────────────────────┤
│  ¿Eliminar esta observación?                         │
│  Se moverá a la papelera de reciclaje.               │
│                                                      │
│  Motivo de eliminación: *                            │
│  ┌──────────────────────────────────────────────┐   │
│  │ Motivo de la eliminación...                  │   │
│  └──────────────────────────────────────────────┘   │
│                                                      │
│              [Cancelar]          [Confirmar]         │
└──────────────────────────────────────────────────────┘
```

#### Comportamiento

- **Soft delete**: La observación se mueve a `observaciones_eliminadas`
- No se elimina físicamente de la base de datos
- El motivo de eliminación es **obligatorio**
- Se elimina el historial asociado
- La observación puede consultarse desde el módulo **Eliminadas**

---

## 5. Operaciones Masivas

### Selección Múltiple

1. Marque el checkbox **Seleccionar todas** para seleccionar todas las observaciones visibles
2. O marque individualmente los checkboxes de cada fila
3. El contador de seleccionadas aparece en la barra superior

### 5.1 Aprobar Múltiples Observaciones

- Seleccione las observaciones → clic en **Aprobar**
- Se abre el mismo modal de aprobación individual
- La operación es **no transaccional**: cada observación se procesa individualmente
- Al finalizar se muestra un resumen: `X procesadas, Y fallos`

### 5.2 Cancelar Múltiples Observaciones

- Seleccione las observaciones → clic en **Cancelar**
- Se abre el modal de cancelación
- Operación no transaccional con resumen por ID

### 5.3 Eliminar Múltiples Observaciones

- Seleccione las observaciones → clic en **Eliminar**
- Se abre el modal de eliminación (motivo obligatorio)
- Operación no transaccional con resumen por ID
- Cada observación se mueve individualmente a la papelera

### Resumen de Operaciones Masivas

Después de cada operación masiva se muestra un resumen:

```
✓ 8 observación(es) aprobada(s) correctamente. 2 fallo(s): ID 45 (No encontrada), ID 52 (Error de base de datos)
```

| Campo | Descripción |
|-------|-------------|
| procesados | Cantidad de observaciones procesadas exitosamente |
| fallos | Lista de IDs que fallaron con el motivo del error |
| total | Total de observaciones enviadas a procesar |

---

## 6. Estadísticas Rápidas

En la parte superior del listado se muestran 4 tarjetas con contadores:

| Tarjeta | Icono | Descripción |
|---------|-------|-------------|
| Total | 📄 | Cantidad total de observaciones del año |
| Pendientes | 🕐 | Observaciones en estado pendiente |
| Aprobados | ✓ | Observaciones aprobadas |
| Errores | ⚠ | Observaciones con tipo ERROR |

---

## 7. Paginación

- **Registros por página**: 50
- **Navegación**: Numérica con botones anterior/siguiente
- **Información**: "Mostrando 1-50 de 245"
- Al cambiar de página, la vista se desplaza automáticamente al inicio

---

## 8. Historial de Cambios

Cada acción de supervisión queda registrada en el historial:

| Campo | Descripción |
|-------|-------------|
| Usuario | Nombre del supervisor que realizó la acción |
| Estado anterior → nuevo | Transición de estado |
| Fecha y hora | Momento exacto del cambio |
| Comentario | Texto ingresado por el supervisor |

### Transiciones de Estado

| Acción | Estado Anterior | Estado Nuevo |
|--------|----------------|--------------|
| Aprobar | Pendiente | Aprobado |
| Cancelar | Pendiente | Rechazado |
| Eliminar | Cualquiera | (registro movido a eliminadas) |

---

## 9. Permisos y Restricciones

### Rol Supervisor

- Ve **todas** las observaciones de todos los establecimientos
- Puede aprobar, cancelar y eliminar cualquier observación
- Acceso completo al módulo de Supervisión

### Rol Registrador

- **No tiene acceso** al módulo de Supervisión
- Si intenta acceder, es redirigido al Dashboard
- Recibe mensaje: "Acceso denegado. Se requiere rol de supervisor"

---

## 10. Flujo de Trabajo Típico

### Escenario: Revisión mensual de observaciones

1. **Ingresar al módulo** → Menú lateral → Supervisión
2. **Aplicar filtros** → Mes: Enero, Estado: Pendiente
3. **Revisar observaciones** → Clic en 👁 para ver detalles
4. **Aprobar las correctas** → Seleccionar → Aprobar → "Sin Observación"
5. **Cancelar las incorrectas** → Seleccionar → Cancelar → Agregar motivo
6. **Eliminar duplicados** → Seleccionar → Eliminar → Motivo obligatorio
7. **Verificar estadísticas** → Los contadores se actualizan automáticamente

---

## 11. Notas Importantes

- **Operaciones no transaccionales**: Cada observación se procesa individualmente. Si una falla, las demás continúan.
- **Soft delete**: Las observaciones eliminadas no se pierden. Se pueden consultar en el módulo **Eliminadas**.
- **CSRF**: Todas las operaciones de escritura están protegidas con token CSRF.
- **Sin máquina de estados**: La transición de estados es libre, sin restricciones de máquina de estados.
- **Clasificación y detalle**: Son campos opcionales en la aprobación, excepto la "Clasificación de Respuesta" que es obligatoria.
