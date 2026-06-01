# Manual de Usuario - Gestión de Establecimientos

## Descripción

El módulo de **Gestión de Establecimientos** permite al rol **Supervisor** administrar los establecimientos de salud del sistema, incluyendo la creación, edición, activación/desactivación y gestión de referentes (personas de contacto) para cada establecimiento.

## Acceso

1. Iniciar sesión con credenciales de **Supervisor**
2. En el menú lateral, hacer clic en **"Establecimientos"**
3. El rol **Registrador** no tiene acceso a este módulo (redirige al Dashboard)

---

## Vista Principal: Lista de Establecimientos

### Mockup

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Agregar, editar y activar/desactivar establecimientos de salud         │
│  Gestión de Establecimientos                    [+ Nuevo Establecimiento]│
├─────────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                  │
│  │      45      │  │      5       │  │      50      │                  │
│  │   Activos    │  │  Inactivos   │  │    Total     │                  │
│  └──────────────┘  └──────────────┘  └──────────────┘                  │
├─────────────────────────────────────────────────────────────────────────┤
│  Buscar: [Hospital, 101...]  Comuna: [Todas ▼]  ☐ Mostrar inactivos    │
├─────────────────────────────────────────────────────────────────────────┤
│  Listado de Establecimientos                                            │
│  ┌────────┬──────────────────┬──────────┬──────────┬────────┬─────────┐│
│  │Código  │Nombre            │N.Corto   │Comuna    │Estado  │Acciones ││
│  ├────────┼──────────────────┼──────────┼──────────┼────────┼─────────┤│
│  │101     │Hospital Base...  │HBSJO     │Osorno    │Activo  │👤 ✏️ ▶️ ││
│  │102     │CESFAM Rahue...   │CESFAM R. │Osorno    │Activo  │👤 ✏️ ⏸️ ││
│  │103     │Posta Río Bueno   │P RBueno  │Río Bueno │Inactivo│👤 ✏️ ▶️ ││
│  └────────┴──────────────────┴──────────┴──────────┴────────┴─────────┘│
└─────────────────────────────────────────────────────────────────────────┘
```

### Columnas de la Tabla

| Columna | Descripción |
|---------|-------------|
| **Código DEIS** | Código numérico único del establecimiento (orden ascendente) |
| **Nombre** | Nombre completo del establecimiento |
| **Nombre Corto** | Abreviatura del establecimiento |
| **Comuna** | Comuna a la que pertenece |
| **Estado** | Badge verde (Activo) o gris (Inactivo) |
| **Referentes** | Cantidad de referentes activos asociados |
| **Acciones** | Botones: Gestionar Referentes, Editar, Activar/Desactivar |

### Filtros Disponibles

1. **Búsqueda por nombre o código**: Escribe para filtrar en tiempo real (búsqueda backend con LIKE)
2. **Filtro por comuna**: Selecciona una comuna específica del dropdown
3. **Mostrar inactivos**: Checkbox para incluir establecimientos desactivados

---

## Crear un Nuevo Establecimiento

### Mockup del Modal

```
┌──────────────────────────────────────────────┐
│  Nuevo Establecimiento                    [X] │
├──────────────────────────────────────────────┤
│  Código de Establecimiento *                  │
│  [101                                    ]    │
│  Código numérico único del establecimiento    │
│                                              │
│  Nombre Completo *                            │
│  [Hospital Base San José de Osorno       ]    │
│                                              │
│  Nombre Corto *                               │
│  [HBSJO                                  ]    │
│                                              │
│  Comuna *                                     │
│  [Osorno                                 ▼]   │
│                                              │
├──────────────────────────────────────────────┤
│               [Cancelar]  [Crear Establecim.] │
└──────────────────────────────────────────────┘
```

### Pasos

1. Hacer clic en el botón **"+ Nuevo Establecimiento"** (esquina superior derecha)
2. Completar el formulario:
   - **Código de Establecimiento**: Número único DEIS (obligatorio, no editable después)
   - **Nombre Completo**: Nombre oficial del establecimiento (obligatorio)
   - **Nombre Corto**: Abreviatura (máximo 50 caracteres, obligatorio)
   - **Comuna**: Seleccionar del dropdown (obligatorio)
3. Hacer clic en **"Crear Establecimiento"**
4. El sistema valida:
   - Campos obligatorios completos
   - Código DEIS no duplicado
5. Si es exitoso, el establecimiento se crea como **Activo** por defecto

---

## Editar un Establecimiento

### Pasos

1. En la tabla, hacer clic en el botón **✏️ (Editar)** del establecimiento deseado
2. Se abre el mismo modal con los datos precargados
3. El **Código DEIS** aparece deshabilitado (no se puede modificar)
4. Modificar los campos necesarios:
   - Nombre Completo
   - Nombre Corto
   - Comuna
5. Hacer clic en **"Guardar Cambios"**
6. El sistema valida:
   - Campos obligatorios completos
   - Si se cambiara el código (no permitido), validaría duplicados

---

## Activar / Desactivar un Establecimiento

### Pasos

1. En la tabla, hacer clic en el botón **▶️ (Activar)** o **⏸️ (Desactivar)**
2. Aparece un modal de confirmación: *"¿Está seguro de activar/desactivar este establecimiento?"*
3. Confirmar la acción
4. El estado cambia y la tabla se actualiza

### Importante

- Un establecimiento **Inactivo** no permite registrar nuevas observaciones
- Las observaciones existentes se mantienen intactas
- El establecimiento inactivo aparece con texto tachado en la tabla

---

## Gestión de Referentes

Cada establecimiento puede tener múltiples **referentes** (personas de contacto para el sistema de observaciones REM).

### Acceder a la Gestión de Referentes

1. En la tabla de establecimientos, hacer clic en el botón **👤 (Gestionar referentes)**
2. Se abre un modal con la lista de referentes del establecimiento

### Mockup del Modal de Referentes

```
┌──────────────────────────────────────────────────────────────────────┐
│  Referentes - Hospital Base San José de Osorno                  [X] │
├──────────────────────────────────────────────────────────────────────┤
│  Personas de contacto para observaciones REM    [+ Nuevo Referente] │
│                                                                      │
│  ┌────────────────┬──────────────────────┬──────────┬──────────────┐│
│  │Nombre          │Cargo                 │Teléfono  │Email         ││
│  ├────────────────┼──────────────────────┼──────────┼──────────────┤│
│  │María González  │Encargado Estadísticas│+569123.. │mgonzalez@..  ││
│  │Juan Pérez      │Digitador Estadísticas│+569456.. │jperez@..     ││
│  │Ana Silva       │Administrativo        │-         │asilva@..     ││
│  └────────────────┴──────────────────────┴──────────┴──────────────┘│
│                                                                      │
│  Acciones por fila: ✏️ Editar | ▶️ Activar/Desactivar | 🗑️ Eliminar │
└──────────────────────────────────────────────────────────────────────┘
```

### Orden de Visualización

Los referentes se muestran en este orden específico:
1. **Encargado de Estadísticas** (primero)
2. **Digitador de Estadísticas** (segundo)
3. **Otros cargos** (orden alfabético por nombre)

### Crear un Nuevo Referente

1. Dentro del modal de referentes, hacer clic en **"+ Nuevo Referente"**
2. Completar el formulario:

```
┌──────────────────────────────────────────────┐
│  Nuevo Referente                        [X] │
├──────────────────────────────────────────────┤
│  Nombre Completo *                           │
│  [María González López                   ]   │
│                                              │
│  Cargo *                                     │
│  [Encargado de Estadísticas              ▼]  │
│                                              │
│  Teléfono                                    │
│  [+56912345678                           ]   │
│  Formato: +569XXXXXXXX o XXXXXXXX            │
│                                              │
│  Email                                       │
│  [maria.gonzalez@ssor.cl                 ]   │
│                                              │
├──────────────────────────────────────────────┤
│               [Cancelar]  [Crear Referente]  │
└──────────────────────────────────────────────┘
```

3. Campos:
   - **Nombre Completo**: Obligatorio
   - **Cargo**: Seleccionar del dropdown (obligatorio)
     - Encargado de Estadísticas
     - Digitador de Estadísticas
     - Jefe de Servicio
     - Administrativo
     - Otro
   - **Teléfono**: Opcional, formato chileno validado
   - **Email**: Opcional, formato validado
4. Hacer clic en **"Crear Referente"**

### Validaciones de Referentes

| Campo | Validación Frontend | Validación Backend |
|-------|---------------------|-------------------|
| Nombre | Obligatorio | Obligatorio |
| Cargo | Obligatorio | Obligatorio |
| Teléfono | Formato: +569XXXXXXXX o XXXXXXXX | mismo |
| Email | Formato email válido | mismo |

### Editar un Referente

1. Hacer clic en **✏️ (Editar)** junto al referente
2. Modificar los campos necesarios
3. Hacer clic en **"Guardar Cambios"**

### Activar / Desactivar un Referente

1. Hacer clic en **▶️ (Activar/Desactivar)**
2. Confirmar la acción

### Eliminar un Referente

1. Hacer clic en **🗑️ (Eliminar)**
2. Confirmar la acción (advertencia: no se puede deshacer)
3. El referente se elimina permanentemente de la base de datos

---

## Flujo Completo de Trabajo

### Escenario: Incorporar un nuevo establecimiento con sus referentes

1. **Crear establecimiento**:
   - Clic en "+ Nuevo Establecimiento"
   - Completar datos → Crear

2. **Agregar referentes**:
   - Clic en 👤 del nuevo establecimiento
   - Clic en "+ Nuevo Referente"
   - Agregar Encargado de Estadísticas
   - Agregar Digitador de Estadísticas
   - Agregar contactos adicionales si es necesario

3. **Verificar**:
   - El contador de referentes muestra el número correcto
   - Los referentes aparecen en el orden correcto

### Escenario: Desactivar un establecimiento que ya no opera

1. **Desactivar establecimiento**:
   - Clic en ⏸️ (Desactivar)
   - Confirmar

2. **Efectos**:
   - No se pueden registrar nuevas observaciones
   - Los referentes se mantienen (pueden desactivarse si es necesario)
   - El establecimiento aparece como "Inactivo" con texto tachado

---

## Notas Técnicas

- **Orden de la tabla**: Por código DEIS ascendente
- **Búsqueda**: Backend con LIKE (nombre, nombre corto, código)
- **CSRF**: Token validado en todas las operaciones POST
- **Permisos**: Solo rol Supervisor (403 para Registrador)
- **Respuesta JSON**: Formato `{"success": true|false, "data": ..., "error": "...", "code": 200|400|401|403|404|500}`

---

## Solución de Problemas

| Problema | Causa Posible | Solución |
|----------|---------------|----------|
| "Ya existe un establecimiento con ese código" | Código DEIS duplicado | Verificar código existente |
| "No se pueden registrar observaciones en un establecimiento inactivo" | Establecimiento desactivado | Activar el establecimiento primero |
| "El formato del email no es válido" | Email mal formateado | Usar formato usuario@dominio.cl |
| "El formato del teléfono no es válido" | Teléfono mal formateado | Usar +569XXXXXXXX o XXXXXXXX |
| Botones de acción no responden | Sesión expirada | Recargar página y volver a iniciar sesión |
