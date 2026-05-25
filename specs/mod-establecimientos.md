# Especificación: MOD-LOC — Establecimientos y Comunas

## Historia de Usuario

> **Como** Supervisor del sistema,
> **necesito** gestionar el catálogo de establecimientos y sus comunas asociadas,
> **para** mantener actualizada la información geográfica y operativa del sistema.

---

## Descripción General

Este módulo permite administrar la entidad "Establecimiento", la cual representa los centros de salud o instituciones monitoreadas. Cada establecimiento pertenece obligatoriamente a una **Comuna**.

El sistema distingue entre establecimientos **Activos** (disponibles para selección y asignación) e **Inactivos** (mantienen historial pero no permiten nuevas operaciones). No se permite la eliminación física de establecimientos, solo su desactivación lógica.

---

## Funciones del Módulo

### LOC-001: Listar Comunas

**Descripción**: Muestra el catálogo de comunas disponibles.

**Reglas de Negocio**:
- **Solo Lectura**: Las comunas son datos de referencia; no se pueden crear, editar ni eliminar desde esta interfaz.
- **Ordenamiento**: Alfabético por nombre.

### LOC-002: Listar Establecimientos

**Descripción**: Muestra la lista de establecimientos registrados.

**Reglas de Negocio**:
- **Filtros**: Por Comuna y por Estado (Activo/Inactivo).
- **Visualización**: Por defecto muestra solo los activos. El Supervisor puede alternar para ver inactivos.

### LOC-003: Crear Establecimiento

**Descripción**: Registra un nuevo establecimiento en el sistema.

**Reglas de Negocio**:
- **Campos Obligatorios**: `codigo_establecimiento`, `nombre`, `comuna_id`.
- **Nombre Corto**: Opcional. Si se deja vacío, el sistema utilizará el nombre completo o una versión truncada en vistas compactas.
- **Unicidad**: El `codigo_establecimiento` debe ser único en todo el sistema.

### LOC-004: Editar Establecimiento

**Descripción**: Modifica los datos de un establecimiento existente.

**Reglas de Negocio**:
- **Campos Editables**: `nombre`, `nombre_corto`, `comuna_id` (si aplica), `codigo_establecimiento`.
- **Validación**: Al cambiar el código, se valida nuevamente la unicidad.

### LOC-005: Cambiar Estado (Activar/Desactivar)

**Descripción**: Alterna el estado del establecimiento entre Activo e Inactivo.

**Reglas de Negocio**:
- **Impacto**:
  - **Desactivar**: El establecimiento deja de aparecer en listas desplegables de nuevas observaciones y asignaciones.
  - **Activar**: Vuelve a estar disponible.
- **Historial**: Las observaciones y asignaciones pasadas se mantienen intactas aunque el establecimiento esté inactivo.

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Ver Comunas | ✅ | ✅ |
| Ver Establecimientos | ✅ (Solo activos) | ✅ (Todos) |
| Crear Establecimiento | ❌ No permitido | ✅ |
| Editar Establecimiento | ❌ No permitido | ✅ |
| Cambiar Estado | ❌ No permitido | ✅ |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-LOC-001 | Creación exitosa | `Establecimiento creado exitosamente.` |
| MSG-LOC-002 | Edición exitosa | `Establecimiento actualizado exitosamente.` |
| MSG-LOC-003 | Estado cambiado | `Estado del establecimiento actualizado.` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-LOC-101 | Campos vacíos | `Error: El código, nombre y comuna son obligatorios.` |
| MSG-LOC-102 | Código duplicado | `Error: Ya existe un establecimiento con ese código.` |
| MSG-LOC-103 | Permiso denegado | `Error: No tiene permisos para gestionar establecimientos.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Crear establecimiento válido
```gherkin
Dado que soy un Supervisor autenticado
Y estoy en la pantalla de creación de establecimientos
Cuando ingreso el código "EST-001", nombre "Hospital Central" y selecciono la comuna "Santiago"
Y presiono "Guardar"
Entonces el establecimiento se crea con estado "Activo"
Y el sistema muestra el mensaje "Establecimiento creado exitosamente"
```

### Escenario: Intentar crear con código duplicado
```gherkin
Dado que soy un Supervisor autenticado
Y ya existe un establecimiento con código "EST-001"
Cuando intento crear otro establecimiento con el mismo código "EST-001"
Entonces el sistema muestra el error "Error: Ya existe un establecimiento con ese código"
Y no se crea el registro
```

### Escenario: Desactivar establecimiento con historial
```gherkin
Dado que soy un Supervisor autenticado
Y el establecimiento "Clínica Sur" tiene observaciones registradas
Cuando cambio el estado de "Clínica Sur" a "Inactivo"
Entonces el establecimiento ya no aparece en las listas de selección para nuevas observaciones
Y las observaciones históricas de "Clínica Sur" siguen siendo visibles en reportes y listados antiguos
```

---

## Mockup ASCII

### Listado de Establecimientos

```
+==============================================================================+
|  GESTIÓN DE ESTABLECIMIENTOS                                                 |
+==============================================================================+
|  [ + Nuevo Establecimiento ]      [ Ver Inactivos (Toggle) ]                  |
|                                                                              |
|  Filtros: [ Comuna: Todas ▼ ]                                                |
|                                                                              |
|  +----------------+---------------------------+----------------+----------+  |
|  | Código         | Nombre                    | Comuna         | Estado   |  |
|  +----------------+---------------------------+----------------+----------+  |
|  | EST-001        | Hospital Central          | Santiago       | Activo   |  |
|  | EST-002        | CESFAM Norte              | Recoleta       | Activo   |  |
|  | EST-003        | Clínica Los Andes         | Las Condes     | Inactivo |  |
|  +----------------+---------------------------+----------------+----------+  |
|                                                                              |
+==============================================================================+
```

### Formulario de Nuevo Establecimiento

```
+==============================================================================+
|  NUEVO ESTABLECIMIENTO                                                       |
+==============================================================================+
|                                                                              |
|  Código Establecimiento: *                                                   |
|  [________________]                                                          |
|                                                                              |
|  Nombre Completo: *                                                          |
|  [________________]                                                          |
|                                                                              |
|  Nombre Corto (Opcional):                                                    |
|  [________________]                                                          |
|  (Si se deja vacío, se usará el nombre completo)                             |
|                                                                              |
|  Comuna: *                                                                   |
|  [ Santiago ▼ ]                                                              |
|                                                                              |
|                          [ Cancelar ]    [ Guardar ]                        |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Jerarquía Geográfica | ✅ Aceptada → Establecimiento pertenece a Comuna obligatoriamente |
| 2 | Unicidad del Código | ✅ Aceptada → Código único |
| 3 | Estado Activo/Inactivo | ✅ Aceptada → Inactivos ocultos en selección, visibles en historial |
| 4 | Gestión Exclusiva | ✅ Aceptada → Solo Supervisores |
| 5 | Gestión de Comunas | ✅ Aceptada → Solo lectura |
| 6 | Nombre Corto | ✅ Modificada → **Opcional** |
| 7 | Eliminación Lógica | ✅ Aceptada → Solo desactivación |
| 8 | Métodos auxiliares | ✅ Nuevos → `searchEstablecimientos()` (búsqueda por nombre), `codigoEstablecimientoExiste()` (validación unicidad), `getComunaById()`, `getComunaByNombre()`, `getAllEstablecimientosConInactivos()` (solo Supervisor) |
