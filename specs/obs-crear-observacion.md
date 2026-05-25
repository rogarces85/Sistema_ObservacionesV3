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
2. El sistema valida que los campos obligatorios estén presentes: `mes`, `establecimiento_id`, `codigo_serie`, `tipo_error`, `plazo_entrega`, `usa_validador`.
   - **Campos condicionales según `tipo_error`**:
     - `codigo_hoja`: **Requerido** para tipos ERROR, REVISAR, F/PLAZO | **Opcional** (se oculta) para S/OBSERVACION
     - `detalle_observacion`: Opcional para todos los tipos
     - `respuesta_establecimiento`: Opcional, se oculta para S/OBSERVACION
   - **Opción N/A para `usa_validador`**: Disponible para todos los tipos, se guarda como `'no'` en la base de datos
3. El sistema verifica que el establecimiento esté asignado al registrador para el **mes y año** específicos mediante validación mensual (`tieneAsignacionParaMes()`). Esto significa que incluso con asignación anual, si existe una reasignación temporal de otro usuario para ese mes, se deniega el acceso.
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
| MSG-003-102 | Establecimiento no asignado | `Error: El establecimiento no está asignado a su usuario para el mes seleccionado.` |
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

### Escenario 6: Crear observación tipo S/OBSERVACION sin hoja REM

```gherkin
Dado que soy un usuario con rol "Registrador" autenticado
Y tengo el establecimiento "Hospital San José" asignado para el año activo
Cuando completo el formulario de nueva observación con los siguientes datos:
  | Campo               | Valor                    |
  | mes                 | Marzo                    |
  | establecimiento_id  | 5                        |
  | codigo_serie        | SERIE A                  |
  | tipo_error          | S/OBSERVACION            |
  | detalle_observacion | Observación general      |
  | plazo_entrega       | dentro_plazo             |
  | usa_validador       | si                       |
Y no selecciono una hoja REM (campo oculto)
Y envío el formulario
Entonces el sistema crea la observación con estado "Pendiente"
Y el campo codigo_hoja se guarda como vacío
Y el campo respuesta_establecimiento se guarda como vacío
Y el sistema muestra el mensaje "Observación registrada exitosamente. ID: [ID]"
```

### Escenario 7: Crear observación con Usa Validador N/A

```gherkin
Dado que soy un usuario con rol "Registrador" autenticado
Y tengo el establecimiento "Hospital San José" asignado para el año activo
Cuando completo el formulario de nueva observación seleccionando "N/A" en "Usa Validador"
Y envío el formulario
Entonces el sistema convierte el valor "n/a" a "no" antes de guardar
Y el sistema crea la observación con usa_validador = "no"
Y el sistema muestra el mensaje "Observación registrada exitosamente. ID: [ID]"
```

### Escenario 8: Crear observación tipo ERROR sin hoja REM (debe fallar)

```gherkin
Dado que soy un usuario con rol "Registrador" autenticado
Y tengo el establecimiento "Hospital San José" asignado para el año activo
Cuando intento crear una observación con tipo "ERROR" sin seleccionar una hoja REM
Entonces el sistema muestra el error "Error: El campo codigo_hoja es requerido"
Y no se crea ninguna observación
```

---

## Mockup ASCII

### Formulario de Nueva Observación (Tipo ERROR/REVISAR/F/PLAZO)

```
+==============================================================================+
|  NUEVA OBSERVACIÓN                                                           |
+==============================================================================+
|                                                                              |
|  Registrado por: Juan Pérez                                                  |
|                                                                              |
|  Mes:                  [ Marzo ▼ ] *                                         |
|  Establecimiento:      [ Hospital San José ▼ ] *                             |
|                        (Solo establecimientos asignados)                     |
|                                                                              |
|  Código Est.:          [ 12345 ] (automático, solo lectura)                  |
|  Tipo:                 [ ERROR ▼ ] *                                         |
|                                                                              |
|  Serie REM:            [ SERIE A ▼ ]                                         |
|  Hoja REM:             [ A01 ▼ ] * (requerido para ERROR/REVISAR/F/PLAZO)    |
|                                                                              |
|  Detalle observación:  [_________________________________] (opcional)        |
|                                                                              |
|  Plazo de entrega:     [ Dentro de Plazo ▼ ]                                 |
|  ¿Usa validador?:      [ Sí ▼ ] (opciones: Sí / No / N/A)                    |
|                                                                              |
|  Respuesta establec.:  [_________________________________] (opcional)        |
|                                                                              |
|                          [ Cancelar ]    [ Guardar ]                         |
|                                                                              |
+==============================================================================+
```

### Formulario de Nueva Observación (Tipo S/OBSERVACION)

```
+==============================================================================+
|  NUEVA OBSERVACIÓN                                                           |
+==============================================================================+
|                                                                              |
|  Registrado por: Juan Pérez                                                  |
|                                                                              |
|  Mes:                  [ Marzo ▼ ] *                                         |
|  Establecimiento:      [ Hospital San José ▼ ] *                             |
|                        (Solo establecimientos asignados)                     |
|                                                                              |
|  Código Est.:          [ 12345 ] (automático, solo lectura)                  |
|  Tipo:                 [ S/OBSERVACION ▼ ] *                                 |
|                                                                              |
|  Serie REM:            [ SERIE A ▼ ]                                         |
|  [Hoja REM: OCULTO - No requerido para S/OBSERVACION]                        |
|                                                                              |
|  Detalle observación:  [_________________________________] (opcional)        |
|                                                                              |
|  Plazo de entrega:     [ Dentro de Plazo ▼ ]                                 |
|  ¿Usa validador?:      [ N/A ▼ ] (opciones: Sí / No / N/A)                   |
|                                                                              |
|  [Respuesta establec.: OCULTO - No requerido para S/OBSERVACION]             |
|                                                                              |
|                          [ Cancelar ]    [ Guardar ]                         |
|                                                                              |
+==============================================================================+
```

### Comportamiento Condicional del Formulario

| Campo | ERROR/REVISAR/F/PLAZO | S/OBSERVACION |
|-------|----------------------|---------------|
| Mes | ✅ Requerido | ✅ Requerido |
| Establecimiento | ✅ Requerido | ✅ Requerido |
| Tipo | ✅ Requerido | ✅ Requerido |
| Serie | ✅ Requerido | ✅ Requerido |
| Hoja REM | ✅ Requerido | ❌ Oculto (no requerido) |
| Detalle Observación |  Opcional | ⚪ Opcional |
| Plazo de Entrega |  Opcional | ⚪ Opcional |
| Usa Validador | ⚪ Opcional (Sí/No/N/A) |  Opcional (Sí/No/N/A) |
| Respuesta Establecimiento | ⚪ Opcional | ❌ Oculto |

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Campos obligatorios | ✅ Modificada → **6 campos mínimos** (`mes`, `establecimiento_id`, `codigo_serie`, `tipo_error`, `plazo_entrega`, `usa_validador`). `codigo_hoja` es condicional (requerido excepto para S/OBSERVACION). `detalle_observacion` es opcional. |
| 2 | Estado inicial | ✅ Aceptada → "Pendiente" |
| 3 | Quién puede registrar | ✅ Modificada → **Solo Registradores**, solo para establecimientos asignados |
| 4 | Duplicados | ✅ Modificada → **Permitidos sin validación**. El registrador discrimina. |
| 5 | Historial automático | ✅ Aceptada → "Registro inicial" |
| 6 | Un registro a la vez | ✅ Aceptada |
| 7 | Campos opcionales | ✅ Modificada → `detalle_observacion`, `respuesta_establecimiento`, `clasificacion`, `detalle_error`. `respuesta_establecimiento` se oculta para S/OBSERVACION. |
| 8 | Año desde sesión | ✅ Aceptada |
| 9 | Sin confirmación | ✅ Aceptada |
| 10 | Sin notificaciones | ✅ Aceptada |
| 11 | Tipo S/OBSERVACION | ✅ Nueva → **No requiere hoja REM ni respuesta del establecimiento**. Solo requiere Serie. |
| 12 | Opción N/A en Usa Validador | ✅ Nueva → Disponible para todos los tipos. Se guarda como `'no'` en BD. |
