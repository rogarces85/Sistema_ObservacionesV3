# Especificación: OBS-003 — Crear Observación

## Historia de Usuario

> **Como** registrador del sistema Observaciones,
> **necesito** registrar una nueva observación para un establecimiento que me ha sido asignado,
> **para** documentar incidencias encontradas en los reportes REM durante el mes correspondiente.

---

## Descripción General

Esta función permite a los usuarios con rol de **Registrador** crear una nueva observación en el sistema. Al registrarse, la observación queda automáticamente en estado **"Pendiente"** y se genera una entrada inicial en el historial de cambios.

El sistema valida que el establecimiento seleccionado esté asignado al registrador y que todos los campos obligatorios estén completos. **No se valida la duplicidad de datos**; se confía en el criterio del registrador para discriminar si un registro es válido o repetido.

El año de la observación se toma automáticamente del año activo en la sesión del usuario.

---

## Flujos de Trabajo

### FT-003-1: Registrar Nueva Observación

```
┌─────────────┐     ┌──────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│ 1. El       │     │ 2. El sistema    │     │ 3. El sistema    │     │ 4. El sistema   │
│ registrador │────>│ valida campos    │────>│ valida que el    │────>│ crea la         │
│ completa el │     │ obligatorios     │     │ establecimiento  │     │ observación     │
│ formulario  │     │                  │     │ esté asignado    │     │ con estado      │
└─────────────┘     └──────────────────┘     └──────────────────┘     │ "Pendiente"    │
                                                                      └─────────────────┘
                                                                              │
                                                                              ▼
                                                                   ┌──────────────────┐
                                                                   │ 5. El sistema    │
                                                                   │ registra entrada │
                                                                   │ en historial     │
                                                                   │ "Registro inicial│
                                                                   └──────────────────┘
                                                                              │
                                                                              ▼
                                                                   ┌──────────────────┐
                                                                   │ 6. El sistema    │
                                                                   │ confirma éxito   │
                                                                   │ y muestra ID     │
                                                                   └──────────────────┘
```

**Pasos detallados:**

1. El registrador accede a la sección "Observaciones" y completa el formulario con los datos requeridos.
2. El sistema valida que los campos obligatorios estén presentes: `mes`, `establecimiento_id`, `codigo_serie`, `codigo_hoja`, `tipo_error`, `detalle_observacion`, `plazo_entrega`, `usa_validador`.
3. El sistema verifica que el `establecimiento_id` seleccionado esté en la lista de establecimientos asignados al registrador para el año activo.
4. Si las validaciones pasan, el sistema inserta el registro en la base de datos con estado `PENDIENTE` y el año tomado de la sesión.
5. El sistema crea automáticamente una entrada en `historial_estados` con comentario "Registro inicial".
6. El sistema responde con éxito y el ID de la nueva observación.

---

## Gestión de Sesiones y Cuentas

### Permisos

| Acción | Registrador | Supervisor |
|--------|:-----------:|:----------:|
| Crear observación | ✅ (solo establecimientos asignados) | ❌ No permitido |

### Reglas de Acceso

- Solo usuarios autenticados con rol **Registrador** pueden ejecutar esta función.
- Si un usuario con rol Supervisor intenta crear una observación, el sistema rechaza la petición con error 403.
- El año de la observación se deriva de `$_SESSION['year']`.

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-003-001 | Observación creada | `Observación registrada exitosamente. ID: [ID]` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-003-101 | Campo obligatorio vacío | `Error: El campo [nombre_del_campo] es requerido.` |
| MSG-003-102 | Establecimiento no asignado | `Error: El establecimiento seleccionado no está asignado a su usuario para el año activo.` |
| MSG-003-103 | Rol no autorizado | `Error: No tiene permisos para registrar observaciones.` |
| MSG-003-104 | Error de base de datos | `Error: No se pudo registrar la observación. Intente nuevamente.` |
| MSG-003-105 | CSRF inválido | `Error: Token de seguridad inválido. Recargue la página.` |

---

## Escenarios BDD (Gherkin)

### Escenario 1: Registrador crea observación exitosamente

```gherkin
Dado que soy un usuario con rol "Registrador" autenticado
Y tengo el establecimiento "Hospital San José" asignado para el año activo
Cuando completo el formulario de nueva observación con los siguientes datos:
  | Campo               | Valor              |
  | mes                 | Marzo              |
  | establecimiento_id  | 5                  |
  | codigo_serie        | REM-12             |
  | codigo_hoja         | HOJA-01            |
  | tipo_error          | ERROR              |
  | detalle_observacion | Falta información  |
  | plazo_entrega       | a_tiempo           |
  | usa_validador       | si                 |
Y envío el formulario
Entonces el sistema crea la observación con estado "Pendiente"
Y el sistema registra una entrada en el historial con comentario "Registro inicial"
Y el sistema muestra el mensaje "Observación registrada exitosamente. ID: [ID]"
```

### Escenario 2: Intentar crear observación sin campos obligatorios

```gherkin
Dado que soy un usuario con rol "Registrador" autenticado
Cuando intento crear una observación sin completar el campo "tipo_error"
Entonces el sistema muestra el error "Error: El campo tipo_error es requerido."
Y no se crea ninguna observación
```

### Escenario 3: Registrador intenta registrar para establecimiento no asignado

```gherkin
Dado que soy un usuario con rol "Registrador" autenticado
Y NO tengo el establecimiento "Clínica Los Andes" asignado
Cuando intento crear una observación seleccionando "Clínica Los Andes"
Entonces el sistema muestra el error "Error: El establecimiento seleccionado no está asignado a su usuario para el año activo."
Y no se crea ninguna observación
```

### Escenario 4: Supervisor intenta crear observación

```gherkin
Dado que soy un usuario con rol "Supervisor" autenticado
Cuando intento acceder al endpoint de creación de observaciones
Entonces el sistema responde con error 403
Y el sistema muestra el mensaje "Error: No tiene permisos para registrar observaciones."
```

### Escenario 5: Crear observación con datos similares a una existente

```gherkin
Dado que soy un usuario con rol "Registrador" autenticado
Y ya existe una observación para el mes "Marzo", establecimiento "Hospital San José", serie "REM-12"
Cuando creo una nueva observación con los mismos datos
Entonces el sistema acepta el registro sin validar duplicidad
Y el sistema crea la observación con éxito
Y el sistema muestra el mensaje "Observación registrada exitosamente. ID: [ID]"
```

---

## Mockup ASCII

### Formulario de Nueva Observación

```
+==============================================================================+
|  NUEVA OBSERVACIÓN                                                           |
+==============================================================================+
|                                                                              |
|  Año: 2026 (automático)                                                      |
|                                                                              |
|  Mes:                  [ Marzo ▼ ]                                           |
|  Establecimiento:      [ Hospital San José ▼ ]                               |
|                        (Solo establecimientos asignados)                     |
|                                                                              |
|  Serie REM:            [________________]                                    |
|  Hoja REM:             [________________]                                    |
|  Tipo:                 [ ERROR ▼ ]                                           |
|                                                                              |
|  Detalle de la observación: *                                                |
|  +------------------------------------------------------------------------+  |
|  | Falta información en la columna de egresos                             |  |
|  |                                                                        |  |
|  |                                                                        |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|  Plazo de entrega:     [ A tiempo ▼ ]                                        |
|  ¿Usa validador?:      [ Si ▼ ]                                              |
|                                                                              |
|  --- Campos opcionales ---                                                   |
|  Respuesta establec.:  [________________]                                    |
|  Clasificación:        [________________]                                    |
|  Detalle error:        [________________]                                    |
|                                                                              |
|                          [ Cancelar ]    [ Registrar Observación ]           |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Campos obligatorios | ✅ Aceptada (8 campos mínimos) |
| 2 | Estado inicial | ✅ Aceptada → "Pendiente" |
| 3 | Quién puede registrar | ✅ Modificada → **Solo Registradores**, solo para establecimientos asignados |
| 4 | Duplicados | ✅ Modificada → **Permitidos sin validación**. El registrador discrimina. |
| 5 | Historial automático | ✅ Aceptada → "Registro inicial" |
| 6 | Un registro a la vez | ✅ Aceptada |
| 7 | Campos opcionales | ✅ Aceptada → respuesta, clasificación, detalle_error |
| 8 | Año desde sesión | ✅ Aceptada |
| 9 | Sin confirmación | ✅ Aceptada |
| 10 | Sin notificaciones | ✅ Aceptada |
