# Especificación: MOD-ASN — Asignaciones de Establecimientos

## Historia de Usuario

> **Como** Supervisor del sistema,
> **necesito** gestionar las asignaciones de establecimientos a los registradores,
> **para** asegurar que cada centro de salud sea monitoreado por la persona correcta, incluyendo reasignaciones temporales por vacaciones o licencias.

---

## Descripción General

Este módulo permite a los Supervisores definir qué establecimientos puede registrar cada usuario. El sistema soporta dos tipos de asignaciones:

1. **Asignación Anual**: Asignación base para todo el año (o meses específicos). Es la asignación "titular" del establecimiento.
2. **Reasignación Temporal**: Override temporal que permite asignar un establecimiento a otro registrador para meses específicos, sin perder la asignación anual original.

**Lógica de Prioridad**: Las reasignaciones temporales tienen **prioridad** sobre las asignaciones anuales. Si un establecimiento tiene asignación anual al Registrador A, pero existe una reasignación temporal al Registrador B para los meses 1-3, entonces:
- Registrador B puede registrar en ese establecimiento durante los meses 1-3
- Registrador A NO puede registrar en ese establecimiento durante los meses 1-3
- Registrador A PUEDE registrar en ese establecimiento durante los meses 4-12

El sistema valida que no existan duplicados en la relación (Registrador + Establecimiento + Año/Mes) y permite operaciones masivas para facilitar la carga inicial o cambios de personal.

---

## Tipos de Asignación

### Asignación Anual (`tipo_asignacion = 'anual'`)
- **Propósito**: Asignación base/designada para el año
- **Alcance**: Todo el año (`meses = 'ALL'`) o meses específicos
- **Comportamiento**: Es la asignación "titular" del establecimiento
- **Visualización**: Badge azul 📅 con texto "Anual"

### Reasignación Temporal (`tipo_asignacion = 'temporal'`)
- **Propósito**: Override temporal para cubrir vacaciones, licencias, etc.
- **Alcance**: Meses específicos obligatorios (no puede ser 'ALL')
- **Comportamiento**: Tiene prioridad sobre la asignación anual
- **Visualización**: Badge ámbar ⏱️ con texto "Temporal"
- **Restricción**: No puede solaparse con otra reasignación temporal para el mismo establecimiento/periodo

---

## Funciones del Módulo

### ASN-001: Listar Asignaciones

**Descripción**: Muestra un resumen de qué establecimientos tiene asignados cada registrador para el año seleccionado.

**Reglas de Negocio**:
- **Filtro**: Se visualiza por año.
- **Detalle**: Muestra el nombre del registrador, la lista de establecimientos asignados, y el tipo de asignación (anual/temporal).

### ASN-002: Asignar (Individual y Múltiple)

**Descripción**: Vincula uno o varios establecimientos a un registrador.

**Reglas de Negocio**:
- **Selección**: Permite seleccionar múltiples establecimientos mediante checkboxes.
- **Tipo de Asignación**:
  - **Anual**: Por defecto asigna para **todo el año**. Permite seleccionar meses específicos si es necesario.
  - **Temporal**: Requiere seleccionar **meses específicos**. No puede ser para todo el año.
- **Prioridad Temporal**: Las asignaciones temporales se crean como registros separados y tienen prioridad sobre las anuales al momento de registrar observaciones.
- **Unicidad**: 
  - No permite dos asignaciones **anuales** para la misma combinación (Registrador + Establecimiento + Año)
  - No permite dos asignaciones **temporales** con meses solapados para el mismo establecimiento
  - **Sí permite** una asignación temporal sobre una anual (es el caso de uso principal)

### ASN-003: Remover Asignación

**Descripción**: Elimina la vinculación entre un registrador y un establecimiento.

**Reglas de Negocio**:
- **Tipo**: Se puede remover asignación anual o temporal por separado.
- **Impacto Anual**: Si se remueve una asignación anual, el registrador pierde el acceso completo al establecimiento.
- **Impacto Temporal**: Si se remueve una reasignación temporal, el establecimiento vuelve automáticamente al titular anual para esos meses.
- **Datos**: No se borran ni el usuario ni el establecimiento, solo el registro de la relación.

### ASN-004: Copiar Asignaciones de Año

**Descripción**: Replica las asignaciones de un año origen a un año destino.

**Reglas de Negocio**:
- **Acumulativo**: Agrega las asignaciones al año destino. No borra las que ya existían.
- **Duplicados**: Ignora las asignaciones que ya existan en el año destino (no falla, simplemente no las duplica).
- **Tipos**: Copia tanto asignaciones anuales como temporales.

### ASN-005: Ver Referentes

**Descripción**: Muestra la información de contacto de las personas responsables en un establecimiento.

**Reglas de Negocio**:
- **Consulta**: Solo lectura. Útil para que el supervisor sepa a quién notificar sobre observaciones.

### ASN-006: Ver Reasignaciones Temporales Activas

**Descripción**: Muestra una tabla con todas las reasignaciones temporales activas para el año seleccionado.

**Reglas de Negocio**:
- **Visualización**: Muestra establecimiento, titular anual, registrador temporal, meses de reasignación, y fecha.
- **Acción**: Permite remover una reasignación temporal individualmente.
- **Contexto**: Al remover, el establecimiento vuelve automáticamente al titular anual.

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Ver Asignaciones | ✅ Solo las propias (lectura) | ✅ Todas (Gestión completa) |
| Asignar / Reasignar | ❌ No permitido | ✅ |
| Remover | ❌ No permitido | ✅ |
| Copiar Año | ❌ No permitido | ✅ |
| Ver Reasignaciones Temporales |  No permitido | ✅ |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-ASN-001 | Asignación anual creada | `Establecimiento(s) asignado(s) exitosamente.` |
| MSG-ASN-002 | Reasignación temporal creada | `Reasignación temporal creada exitosamente.` |
| MSG-ASN-003 | Asignación removida | `Asignación removida exitosamente.` |
| MSG-ASN-004 | Reasignación temporal removida | `Reasignación temporal removida exitosamente.` |
| MSG-ASN-005 | Año copiado | `Se copiaron [N] asignaciones del año [Origen] al [Destino].` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-ASN-101 | Duplicado anual | `Error: El registrador ya tiene asignado este establecimiento para el periodo seleccionado.` |
| MSG-ASN-102 | Duplicado temporal | `Error: Ya existe una reasignación temporal para ese periodo.` |
| MSG-ASN-103 | Est. Inactivo | `Error: No se pueden asignar establecimientos inactivos.` |
| MSG-ASN-104 | Sin selección | `Error: Debe seleccionar al menos un establecimiento.` |
| MSG-ASN-105 | Temporal sin meses | `Error: Para asignación temporal debe especificar los meses.` |
| MSG-ASN-106 | Permiso denegado | `Error: No tiene permisos para gestionar asignaciones.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Asignación múltiple anual para todo el año
```gherkin
Dado que soy un Supervisor autenticado
Y estoy en la pantalla de Asignaciones
Cuando selecciono el registrador "Juan Pérez"
Y selecciono los establecimientos "Hospital Norte" y "CESFAM Sur"
Y selecciono tipo "Anual" con opción "Todo el año"
Y presiono "Asignar"
Entonces ambos establecimientos quedan asignados a "Juan Pérez" como asignación anual para todo el año actual
```

### Escenario: Reasignación temporal sobre asignación anual
```gherkin
Dado que "María López" tiene asignación anual para "Hospital Central"
Y soy un Supervisor autenticado
Cuando selecciono al registrador "Pedro Gómez"
Y selecciono el establecimiento "Hospital Central"
Y selecciono tipo "Temporal" con meses "Enero, Febrero, Marzo"
Y presiono "Asignar"
Entonces se crea una reasignación temporal de "Hospital Central" a "Pedro Gómez" para meses 1,2,3
Y "Pedro Gómez" puede registrar en "Hospital Central" solo durante Enero, Febrero y Marzo
Y "María López" NO puede registrar en "Hospital Central" durante Enero, Febrero y Marzo
Y "María López" PUEDE registrar en "Hospital Central" durante Abril a Diciembre
```

### Escenario: Verificar prioridad temporal sobre anual al registrar
```gherkin
Dado que "María López" tiene asignación anual para "Hospital Central"
Y existe reasignación temporal de "Hospital Central" a "Pedro Gómez" para mes "Febrero"
Y "María López" intenta registrar una observación en "Hospital Central" para mes "Febrero"
Entonces el sistema deniega el acceso con error "El establecimiento no está asignado a su usuario para el mes seleccionado"
Y "Pedro Gómez" intenta registrar una observación en "Hospital Central" para mes "Febrero"
Entonces el sistema permite el registro exitosamente
```

### Escenario: Remover reasignación temporal
```gherkin
Dado que existe reasignación temporal de "Hospital Central" a "Pedro Gómez" para meses "Enero, Febrero, Marzo"
Y "María López" tiene asignación anual para "Hospital Central"
Cuando remuevo la reasignación temporal
Entonces "María López" recupera el acceso completo a "Hospital Central" para todos los meses
Y "Pedro Gómez" pierde el acceso a "Hospital Central"
```

### Escenario: Intentar crear dos reasignaciones temporales solapadas
```gherkin
Dado que existe reasignación temporal de "Hospital Central" a "Pedro Gómez" para meses "Enero, Febrero"
Y soy un Supervisor autenticado
Cuando intento crear otra reasignación temporal de "Hospital Central" a "Ana Ruiz" para meses "Febrero, Marzo"
Entonces el sistema muestra el error "Ya existe una reasignación temporal para ese periodo"
Y no se crea la segunda reasignación
```

### Escenario: Copiar asignaciones de año anterior
```gherkin
Dado que soy un Supervisor autenticado
Y existen 50 asignaciones configuradas para el año 2025 (incluyendo 5 temporales)
Cuando uso la función "Copiar asignaciones" de 2025 a 2026
Entonces el sistema crea esas 50 asignaciones para el año 2026
Y se copian tanto asignaciones anuales como temporales
Y no se eliminan las asignaciones que ya existían para 2026
```

### Escenario: Intentar asignar establecimiento inactivo
```gherkin
Dado que soy un Supervisor autenticado
Y el establecimiento "Clínica Cerrada" está marcado como inactivo
Cuando intento asignar "Clínica Cerrada" a un registrador
Entonces el sistema muestra el error "Error: No se pueden asignar establecimientos inactivos."
Y no se crea la asignación
```

---

## Mockup ASCII

### Pantalla Principal de Asignaciones

```
+==============================================================================+
|  GESTIÓN DE ASIGNACIONES - Año: [ 2026 ▼ ]                                   |
+==============================================================================+
|                                                                              |
|  [ + Nueva Asignación ]      [ Copiar desde año: 2025 -> ]                   |
|                                                                              |
|  LISTADO DE REGISTRADORES Y SUS ESTABLECIMIENTOS                             |
|  +------------------------------------------------------------------------+  |
|  | Registrador      | Establecimientos Asignados                          |  |
|  +------------------+-----------------------------------------------------+  |
|  | Juan Pérez       | [📅 Anual] Hospital Norte                           |  |
|  |                  | [ Anual] CESFAM Sur                               |  |
|  |                  | [️ Temporal] Hospital Central (Ene-Mar)            |  |
|  +------------------+-----------------------------------------------------+  |
|  | María López      | [📅 Anual] Hospital Central                         |  |
|  |                  | [📅 Anual] CESFAM Este                              |  |
|  +------------------+-----------------------------------------------------+  |
|                                                                              |
+==============================================================================+

+==============================================================================+
|  ⏱️ REASIGNACIONES TEMPORALES ACTIVAS                                        |
+==============================================================================+
|                                                                              |
|  +------------------------------------------------------------------------+  |
|  | Establecimiento   | Titular Anual  | Reasignado a | Meses     | Acciones |  |
|  +-------------------+----------------+--------------+-----------+----------+  |
|  | Hospital Central  | María López    | Pedro Gómez  | Ene-Mar   | [✕]      |  |
|  | CESFAM Norte      | Juan Pérez     | Ana Ruiz     | Feb       | [✕]      |  |
|  +-------------------+----------------+--------------+-----------+----------+  |
|                                                                              |
+==============================================================================+
```

### Formulario de Nueva Asignación

```
+==============================================================================+
|  ASIGNAR / REASIGNAR ESTABLECIMIENTOS                                        |
+==============================================================================+
|                                                                              |
|  Para: Juan Pérez — Año 2026                                                 |
|                                                                              |
|  Tipo de Asignación:                                                         |
|  [●]  Anual                                                                |
|      Asignación base para todo el año                                        |
|  [ ] ⏱️ Temporal                                                             |
|      Reasignación por meses específicos (override)                           |
|                                                                              |
|  Establecimientos:   (Seleccione uno o varios)                               |
|  +------------------------------------------------------------------------+  |
|  | [ ] Buscar establecimiento...                                          |  |
|  +------------------------------------------------------------------------+  |
|  | [ ] Hospital Norte           | [x] CESFAM Sur                          |  |
|  | [x] Hospital Central         | [ ] Clínica Los Andes                   |  |
|  | [ ] CESFAM Este              | [ ] ...                                 |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|  Periodo de validez:                                                         |
|  (●) Todo el año 2026                                                        |
|  ( ) Meses específicos:                                                      |
|      [ ] Ene  [ ] Feb  [ ] Mar  [ ] Abr  [ ] May  [ ] Jun                   |
|      [ ] Jul  [ ] Ago  [ ] Sep  [ ] Oct  [ ] Nov  [ ] Dic                   |
|                                                                              |
|                          [ Cancelar ]    [ Guardar Asignaciones ]           |
|                                                                              |
+==============================================================================+
```

### Formulario de Reasignación Temporal

```
+==============================================================================+
|  ASIGNAR / REASIGNAR ESTABLECIMIENTOS                                        |
+==============================================================================+
|                                                                              |
|  Para: Pedro Gómez — Año 2026                                                |
|                                                                              |
|  Tipo de Asignación:                                                         |
|  [ ]  Anual                                                                |
|      Asignación base para todo el año                                        |
|  [●] ⏱️ Temporal                                                             |
|      Reasignación por meses específicos (override)                           |
|                                                                              |
|  Establecimientos:   (Seleccione uno o varios)                               |
|  +------------------------------------------------------------------------+  |
|  | [ ] Buscar establecimiento...                                          |  |
|  +------------------------------------------------------------------------+  |
|  | [ ] Hospital Norte           | [ ] CESFAM Sur                          |  |
|  | [x] Hospital Central         | [ ] Clínica Los Andes                   |  |
|  |    Asignado a: María López                                           |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|  Periodo de validez:                                                         |
|  ( ) Todo el año 2026 (deshabilitado para temporal)                          |
|  (●) Meses específicos:                                                      |
|      [x] Ene  [x] Feb  [x] Mar  [ ] Abr  [ ] May  [ ] Jun                   |
|      [ ] Jul  [ ] Ago  [ ] Sep  [ ] Oct  [ ] Nov  [ ] Dic                   |
|                                                                              |
|                          [ Cancelar ]    [ Guardar Asignaciones ]           |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Rol exclusivo | ✅ Aceptada → Solo Supervisores gestionan |
| 2 | Tipos de asignación | ✅ Modificada → **Anuales (base) y Temporales (override)** con prioridad temporal |
| 3 | Unicidad | ✅ Modificada → No duplicados anuales, no solapamiento temporales, pero temporal sobre anual permitido |
| 4 | Referentes | ✅ Aceptada → Consulta de contactos |
| 5 | Copiar año | ✅ Aceptada → Acumulativo, no destructivo, copia ambos tipos |
| 6 | Remoción | ✅ Modificada → Se puede remover anual o temporal por separado |
| 7 | Est. inactivos | ✅ Aceptada → Bloqueado asignar inactivos |
| 8 | Asignación múltiple | ✅ Aceptada → Selección manual (checkboxes) |
| 9 | Prioridad temporal | ✅ Nueva → Las reasignaciones temporales tienen prioridad sobre anuales al registrar |
| 10 | Sección temporales | ✅ Nueva → Panel separado para ver y gestionar reasignaciones temporales activas |
