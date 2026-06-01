# Manual de Usuario - Papelera de Eliminadas

## Descripción General

El módulo de **Papelera de Eliminadas** permite a los supervisores gestionar las observaciones que fueron eliminadas del sistema. Desde aquí es posible:

- **Restaurar** observaciones: las mueve de vuelta a la tabla principal de observaciones con estado pendiente
- **Eliminar permanentemente**: borra definitivamente la observación del sistema (acción irreversible)
- **Operaciones masivas**: restaurar o eliminar múltiples observaciones simultáneamente
- **Consultar estadísticas**: total de eliminadas, distribución por estado, mes y usuario eliminador

Este módulo es exclusivo del rol **Supervisor** y no está disponible para Registradores.

### Características Principales

- **Restaurar (MOVE)**: copia la observación a la tabla `observaciones` y la elimina de `observaciones_eliminadas`
- **Eliminar permanente**: borrado directo de `observaciones_eliminadas`
- **Operaciones masivas no transaccionales**: cada registro se procesa individualmente, se reportan fallos por ID
- **Protección contra concurrencia**: si otro usuario ya restauró/eliminó un registro → error 404
- **Paginación**: 50 registros por página
- **Filtros avanzados**: año, mes, comuna, establecimiento, registrador, búsqueda de texto

---

## 1. Acceso al Módulo

### Requisitos
- Rol: **Supervisor** (los Registradores obtienen acceso denegado)

### Navegación
- Menú lateral → **Gestión** → **Eliminadas**

---

## 2. Vista Principal - Listado de Observaciones Eliminadas

### Mockup: Lista de Papelera

```
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│  Observaciones Eliminadas                                     [↺ Restaurar] [🗑 Eliminar Perm.]│
│  Papelera de reciclaje — Restaurar o eliminar permanentemente                                   │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│  [🗑 Total: 47]  [📊 Por Estado: Pendiente: 30, Error: 17]  [📅 Por Mes: 8 meses]  [👤 Mayor] │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│  Filtros                                                                                        │
│  [Mes ▼] [Comuna ▼] [Establecimiento ▼] [Registrador ▼] [Buscar en detalles, motivo...]        │
│  [🔍 Aplicar Filtros] [✗ Limpiar]                                                               │
├─────────────────────────────────────────────────────────────────────────────────────────────────┤
│  ☐ Seleccionar todas                                                                            │
├──────┬────┬──────────────────┬────────────┬──────────────┬────────┬──────────┬──────────────────┤
│  ☐   │ ID │ Fecha Eliminación│ Establecim.│ Serie/Hoja   │ Mes    │ Estado   │ Acciones         │
│      │Orig│                  │            │              │        │ Original │                  │
├──────┼────┼──────────────────┼────────────┼──────────────┼────────┼──────────┼──────────────────┤
│  ☐   │#123│ 15/01/2026 10:30│ CESFAM     │ SERIE A / A01│ Enero  │ Pendiente│ [↺] [🗑]         │
│      │    │                  │ Osorno     │              │        │          │                  │
│      │    │                  │ Osorno     │              │        │          │                  │
├──────┴────┴──────────────────┴────────────┴──────────────┴────────┴──────────┴──────────────────┤
│  Mostrando 1-50 de 47                    [< 1] [2] [3] [4] [5] >                                │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘
```

### Columnas de la Tabla

| Columna | Descripción |
|---------|-------------|
| ☐ Checkbox | Seleccionar para operaciones masivas |
| ID Original | ID que tenía la observación en la tabla principal |
| Fecha Eliminación | Fecha y hora en que fue eliminada |
| Establecimiento | Nombre corto y comuna del establecimiento |
| Serie / Hoja | Serie REM y hoja específica |
| Mes | Mes de la observación |
| Estado Original | Estado que tenía la observación al momento de ser eliminada |
| Eliminado Por | Nombre del supervisor que eliminó la observación |
| Motivo | Razón de la eliminación |
| Acciones | Restaurar (↺) o Eliminar permanentemente (🗑) |

---

## 3. Estadísticas Rápidas

En la parte superior del listado se muestran 4 tarjetas con información resumida:

| Tarjeta | Icono | Descripción |
|---------|-------|-------------|
| Total Eliminadas | 🗑 | Cantidad total de observaciones en la papelera |
| Por Estado | 📊 | Distribución según estado original (pendiente, aprobado, error, etc.) |
| Por Mes | 📅 | Cantidad de meses con registros eliminados |
| Mayor Eliminador | 👤 | Supervisor que más observaciones ha eliminado |

---

## 4. Filtros

### Filtros Disponibles

| Filtro | Tipo | Descripción |
|--------|------|-------------|
| Mes | Select | Filtra por mes específico (Enero a Diciembre) |
| Comuna | Select | Filtra por comuna del establecimiento |
| Establecimiento | Select | Se habilita al seleccionar una comuna. Solo muestra establecimientos de esa comuna |
| Registrador | Select | Filtra por usuario que registró originalmente la observación |
| Búsqueda | Texto libre | Busca en detalles de corrección, motivo de eliminación, nombre de establecimiento y prestación |

### Uso de Filtros

1. Seleccione los criterios de filtrado deseados
2. Haga clic en **Aplicar Filtros** para ejecutar la búsqueda
3. Use **Limpiar** para restablecer todos los filtros y volver a la vista completa

### Filtro en Cascada: Comuna → Establecimiento

- Al seleccionar una **Comuna**, el campo **Establecimiento** se habilita automáticamente
- Solo se muestran los establecimientos pertenecientes a la comuna seleccionada
- Si no se selecciona comuna, el campo establecimiento permanece deshabilitado

---

## 5. Paginación

- **Registros por página**: 50
- **Navegación**: Numérica con botones anterior/siguiente
- **Información**: "Mostrando 1-50 de X"
- Al cambiar de página, la vista se desplaza automáticamente al inicio

---

## 6. Operaciones Individuales

### 6.1 Restaurar una Observación

**Acceso**: Botón ↺ (reloj/historial azul) en cualquier fila

#### Comportamiento

- La observación se **copia** a la tabla `observaciones` con estado **pendiente**
- Se **elimina** de la tabla `observaciones_eliminadas`
- Se registra en el historial: "Observación restaurada desde papelera de eliminadas"
- La observación vuelve a estar disponible en el módulo de Observaciones

#### Flujo

```
1. Clic en ↺ (Restaurar)
2. Modal de confirmación: "¿Restaurar esta observación?"
3. Clic en Confirmar
4. La observación desaparece de la papelera
5. Notificación: "Observación restaurada exitosamente"
```

### 6.2 Eliminar Permanentemente

**Acceso**: Botón 🗑 (basurero rojo) en cualquier fila

#### Comportamiento

- La observación se **elimina definitivamente** de `observaciones_eliminadas`
- **No se puede deshacer** esta acción
- Se requiere confirmación explícita con checkbox: "Entiendo que esta acción no se puede deshacer"
- Se registra en el historial: "Eliminación permanente desde papelera"

#### Flujo

```
1. Clic en 🗑 (Eliminar permanentemente)
2. Modal de confirmación con checkbox obligatorio
3. Marcar: "Entiendo que esta acción no se puede deshacer"
4. Clic en Confirmar
5. La observación desaparece de la papelera
6. Notificación: "Observación eliminada permanentemente"
```

---

## 7. Operaciones Masivas

### Selección Múltiple

1. Marque el checkbox **Seleccionar todas** para seleccionar todas las observaciones visibles en la página actual
2. O marque individualmente los checkboxes de cada fila
3. El contador de seleccionadas aparece en la barra superior del header

### 7.1 Restaurar Múltiples Observaciones

- Seleccione las observaciones → clic en **Restaurar**
- Se abre el modal de confirmación
- La operación es **no transaccional**: cada observación se procesa individualmente
- Al finalizar se muestra un resumen con exitosos y fallos

### 7.2 Eliminar Permanentemente Múltiples Observaciones

- Seleccione las observaciones → clic en **Eliminar Permanentemente**
- Se abre el modal de confirmación con checkbox obligatorio
- Operación no transaccional con resumen por ID

### Resumen de Operaciones Masivas

Después de cada operación masiva se muestra un resumen:

```
✓ 8 observación(es) restaurada(s) correctamente. 2 fallo(s): ID 45 (No encontrada), ID 52 (Error de base de datos)
```

| Campo | Descripción |
|-------|-------------|
| exitosos | Cantidad de observaciones procesadas correctamente |
| fallos | Lista de IDs que fallaron con el motivo del error |

---

## 8. Reglas de Negocio

### Restaurar (MOVE)

- **Copiar** los datos de `observaciones_eliminadas` a `observaciones`
- **Eliminar** el registro de `observaciones_eliminadas`
- La observación restaurada tiene estado **pendiente**
- Se mantiene la fecha de creación original

### Eliminar Permanente (DELETE)

- **Borrado directo** de `observaciones_eliminadas`
- **Irreversible**: no hay forma de recuperar el registro
- Requiere confirmación explícita del usuario

### Concurrencia

- Si un registro ya fue restaurado o eliminado por otro usuario entre el momento de carga y la acción → **error 404**
- Las operaciones masivas continúan procesando los registros restantes aunque algunos fallen

### Permisos

| Rol | Ver Papelera | Restaurar | Eliminar Permanente |
|-----|:------------:|:---------:|:-------------------:|
| Supervisor | ✓ | ✓ | ✓ |
| Registrador | ✗ (403) | ✗ | ✗ |

---

## 9. Flujo de Trabajo Típico

### Escenario: Revisión semanal de la papelera

1. **Ingresar al módulo** → Menú lateral → Eliminadas
2. **Revisar estadísticas** → Verificar cuántas observaciones hay en la papelera
3. **Aplicar filtros** → Mes: Enero, Estado: Pendiente
4. **Identificar restaurables** → Observaciones eliminadas por error
5. **Restaurar individualmente** → Clic en ↺ en cada una
6. **Limpiar la papelera** → Seleccionar las que deben eliminarse permanentemente
7. **Eliminar permanentemente** → Clic en Eliminar Permanentemente → Confirmar con checkbox
8. **Verificar resultado** → Las estadísticas se actualizan automáticamente

### Escenario: Limpieza masiva de fin de mes

1. **Filtrar por mes anterior** → Mes: Diciembre
2. **Seleccionar todas** → Checkbox "Seleccionar todas"
3. **Eliminar permanentemente** → Confirmar con checkbox de irreversibilidad
4. **Revisar resumen** → Verificar si hubo fallos y cuáles fueron los IDs

---

## 10. Notas Importantes

- **Operaciones no transaccionales**: Cada observación se procesa individualmente. Si una falla, las demás continúan procesándose.
- **Restaurar = MOVE**: La observación se copia a la tabla principal y se elimina de la papelera. No se duplica.
- **Eliminación permanente**: Es irreversible. No hay backup automático. Asegúrese antes de confirmar.
- **CSRF**: Todas las operaciones de escritura están protegidas con token CSRF.
- **404 por concurrencia**: Si otro usuario ya procesó un registro, recibirá un error 404 indicando que ya no existe.
- **Historial**: Todas las acciones (restaurar, eliminar permanente) quedan registradas en `historial_observaciones`.
