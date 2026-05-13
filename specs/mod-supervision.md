# Especificación: MOD-SUP — Supervisión de Observaciones

## Historia de Usuario

> **Como** Supervisor del sistema,
> **necesito** revisar, aprobar, cancelar y eliminar observaciones registradas por los usuarios,
> **para** validar la calidad de los datos, gestionar incidencias y mantener la base de datos limpia de registros erróneos.

---

## Descripción General

El módulo de Supervisión es la herramienta principal de control de calidad. Permite a los Supervisores visualizar todas las observaciones del sistema (sin restricciones de propiedad), aplicar filtros avanzados para localizar registros específicos y ejecutar acciones sobre su ciclo de vida.

Las acciones principales son **Aprobar** (validar la observación como correcta) y **Cancelar** (rechazarla como inválida). Adicionalmente, se permite la **Eliminación** (movimiento a papelera) para casos de errores de registro graves. Todas las operaciones pueden realizarse de forma individual o masiva.

---

## Funciones del Módulo

### SUP-001: Listado y Filtrado de Observaciones

**Descripción**: Consulta centralizada de todas las observaciones del sistema con capacidad de búsqueda avanzada.

**Reglas de Negocio**:
- **Visibilidad**: El Supervisor ve **todas** las observaciones de todos los registradores.
- **Filtros Disponibles**: Año, Mes, Estado (Pendiente, Aprobado, Cancelado), Establecimiento, Registrador, Búsqueda de texto (detalle/tipo).
- **Ordenamiento**: Por defecto, fecha de registro descendente.

### SUP-002: Aprobar Observación

**Descripción**: Valida una observación, marcándola como correcta.

**Reglas de Negocio**:
- **Estado Resultante**: Cambia a `ESTADO_APROBADO`.
- **Datos Adicionales**: El Supervisor puede opcionalmente ingresar:
  - `clasificación`: Categoría del error o hallazgo.
  - `detalle_error`: Explicación técnica del error encontrado.
- **Historial**: Se registra automáticamente la acción con el comentario del supervisor.
- **Operación**: Soporta selección individual y múltiple (masivo).

### SUP-003: Cancelar Observación

**Descripción**: Rechaza una observación, marcándola como inválida o descartada.

**Reglas de Negocio**:
- **Estado Resultante**: Cambia a `ESTADO_RECHAZADO`.
- **Comentario**: Se recomienda ingresar un motivo de cancelación.
- **Historial**: Se registra la acción.
- **Operación**: Soporta selección individual y múltiple (masivo).

### SUP-004: Eliminar Observación (Papelera)

**Descripción**: Mueve una observación a la papelera de reciclaje.

**Reglas de Negocio**:
- **Tipo**: Borrado Lógico (Soft Delete). El registro no se pierde, se mueve a la tabla de eliminadas.
- **Motivo**: Se puede registrar un motivo de eliminación.
- **Historial**: Se registra la eliminación.
- **Operación**: Soporta selección individual y múltiple.

### SUP-005: Ver Detalle Completo

**Descripción**: Visualización exhaustiva de una observación y su historial.

**Reglas de Negocio**:
- Muestra todos los campos, datos del establecimiento, datos del registrador y supervisor (si aplica).
- Muestra la línea de tiempo del historial de cambios de estado.

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Ver Listado General | ❌ No permitido | ✅ |
| Aprobar / Cancelar | ❌ No permitido | ✅ |
| Eliminar (Papelera) | ❌ No permitido | ✅ |
| Ver Detalle | ✅ Solo propias | ✅ Todas |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-SUP-001 | Aprobación | `Observación(es) aprobada(s) exitosamente.` |
| MSG-SUP-002 | Cancelación | `Observación(es) cancelada(s) exitosamente.` |
| MSG-SUP-003 | Eliminación | `Observación(es) movida(s) a la papelera.` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-SUP-101 | Sin selección | `Error: Debe seleccionar al menos una observación.` |
| MSG-SUP-102 | Permiso denegado | `Error: Solo los supervisores pueden realizar esta acción.` |
| MSG-SUP-103 | Error de BD | `Error: No se pudo procesar la solicitud. Intente nuevamente.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Aprobación masiva con datos opcionales
```gherkin
Dado que soy un Supervisor autenticado
Y estoy en la pantalla de Supervisión
Y he seleccionado 5 observaciones pendientes
Cuando ingreso una clasificación "Error de cálculo" (opcional)
Y presiono "Aprobar Seleccionadas"
Entonces las 5 observaciones cambian su estado a "Aprobado"
Y se registra la clasificación en cada una
Y el sistema muestra "5 observación(es) aprobada(s) exitosamente"
```

### Escenario: Cancelación individual con comentario
```gherkin
Dado que soy un Supervisor autenticado
Y estoy viendo el detalle de la observación #1234
Cuando presiono "Cancelar"
E ingreso el comentario "Datos inconsistentes con el reporte oficial"
Y confirmo la acción
Entonces la observación #1234 cambia su estado a "Rechazado"
Y el comentario se guarda en el historial
```

### Escenario: Eliminación a papelera
```gherkin
Dado que soy un Supervisor autenticado
Y selecciono una observación errónea
Cuando presiono "Eliminar" y confirmo
Entonces la observación desaparece del listado principal
Y la observación aparece en la sección de "Observaciones Eliminadas" (Papelera)
```

### Escenario: Filtrado avanzado
```gherkin
Dado que soy un Supervisor autenticado
Cuando aplico los filtros:
  | Campo          | Valor       |
  | Mes            | Marzo       |
  | Establecimiento| Hospital Sur|
  | Estado         | Pendiente   |
Entonces el sistema muestra solo las observaciones que coinciden con todos los criterios
```

---

## Mockup ASCII

### Pantalla Principal de Supervisión

```
+==============================================================================+
|  SUPERVISIÓN DE OBSERVACIONES                                                |
+==============================================================================+
|  Filtros:                                                                    |
|  [ Año: 2026 ▼ ] [ Mes: Todos ▼ ] [ Estado: Pendiente ▼ ]                   |
|  [ Establecimiento: Todos ▼ ] [ Buscar texto... ]      [ 🔍 Filtrar ]        |
+==============================================================================+
|                                                                              |
|  [ x ] Seleccionar Todo    |   Acciones Masivas:                             |
|                            |   [ ✅ Aprobar ] [ ❌ Cancelar ] [ 🗑️ Eliminar ] |
|                            +-------------------------------------------------+
|  +---+-----+-------+------------------+-------------+-----------+----------+ |
|  | [ ]| ID  | Mes   | Establecimiento  | Tipo Error  | Estado    | Fecha    | |
|  +---+-----+-------+------------------+-------------+-----------+----------+ |
|  | [x]| 1050| Marzo | Hospital Norte   | ERROR       | Pendiente | 13/05/26 | |
|  | [x]| 1049| Marzo | CESFAM Central   | OBSERVACIÓN | Pendiente | 13/05/26 | |
|  | [ ]| 1048| Feb   | Hospital Sur     | ERROR       | Aprobado  | 12/05/26 | |
|  | [ ]| 1047| Feb   | Clínica Andes    | ERROR       | Cancelado | 12/05/26 | |
|  +---+-----+-------+------------------+-------------+-----------+----------+ |
|                                                                              |
|  Mostrando 1-10 de 150 registros                                             |
+==============================================================================+
```

### Modal de Acción (Aprobar/Cancelar)

```
+==============================================================================+
|  APROBAR OBSERVACIONES (2 seleccionadas)                                     |
+==============================================================================+
|                                                                              |
|  Clasificación (Opcional):                                                   |
|  [ Error de tipeo ▼ ]                                                        |
|                                                                              |
|  Detalle del error (Opcional):                                               |
|  +------------------------------------------------------------------------+  |
|  |                                                                        |  |
|  |                                                                        |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|  Comentario general:                                                         |
|  [ Observaciones validadas y correctas                                      ]  |
|                                                                              |
|                          [ Cancelar ]    [ Confirmar Aprobación ]           |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Acciones principales | ✅ Aceptada → Aprobar y Cancelar |
| 2 | Operaciones masivas | ✅ Aceptada → Soportadas |
| 3 | Datos adicionales | ✅ Modificada → **Opcionales** al aprobar |
| 4 | Eliminación | ✅ Aceptada → Soft delete (Papelera) |
| 5 | Filtros avanzados | ✅ Aceptada |
| 6 | Historial automático | ✅ Aceptada |
| 7 | Rol exclusivo | ✅ Aceptada → Solo Supervisores |
| 8 | Visualización | ✅ Aceptada → Acceso total |
