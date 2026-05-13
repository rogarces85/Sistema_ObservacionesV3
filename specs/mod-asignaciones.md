# Especificación: MOD-ASN — Asignaciones de Establecimientos

## Historia de Usuario

> **Como** Supervisor del sistema,
> **necesito** gestionar las asignaciones de establecimientos a los registradores,
> **para** asegurar que cada centro de salud sea monitoreado por la persona correcta, incluyendo reasignaciones temporales por vacaciones o licencias.

---

## Descripción General

Este módulo permite a los Supervisores definir qué establecimientos puede registrar cada usuario. Las asignaciones son **anuales** por defecto, pero el sistema soporta **reasignaciones temporales por meses** (ej. cubrir vacaciones) sin perder la asignación original del año.

El sistema valida que no existan duplicados en la relación (Registrador + Establecimiento + Año/Mes) y permite operaciones masivas para facilitar la carga inicial o cambios de personal.

---

## Funciones del Módulo

### ASN-001: Listar Asignaciones

**Descripción**: Muestra un resumen de qué establecimientos tiene asignados cada registrador para el año seleccionado.

**Reglas de Negocio**:
- **Filtro**: Se visualiza por año.
- **Detalle**: Muestra el nombre del registrador y la lista de establecimientos asignados.

### ASN-002: Asignar (Individual y Múltiple)

**Descripción**: Vincula uno o varios establecimientos a un registrador.

**Reglas de Negocio**:
- **Selección**: Permite seleccionar múltiples establecimientos mediante checkboxes.
- **Alcance**: Por defecto asigna para **todo el año**.
- **Temporalidad**: Permite seleccionar **meses específicos** si la asignación es temporal (ej. cubrir un reemplazo). Si se seleccionan meses, la asignación es válida solo para esos meses en ese año.
- **Unicidad**: No permite asignar dos veces la misma combinación (Registrador + Establecimiento + Año/Mes).

### ASN-003: Remover Asignación

**Descripción**: Elimina la vinculación entre un registrador y un establecimiento.

**Reglas de Negocio**:
- **Impacto**: Si es una asignación anual, se elimina para todo el año. Si es temporal, se elimina solo para los meses especificados.
- **Datos**: No se borran ni el usuario ni el establecimiento, solo el registro de la relación.

### ASN-004: Copiar Asignaciones de Año

**Descripción**: Replica las asignaciones de un año origen a un año destino.

**Reglas de Negocio**:
- **Acumulativo**: Agrega las asignaciones al año destino. No borra las que ya existían.
- **Duplicados**: Ignora las asignaciones que ya existan en el año destino (no falla, simplemente no las duplica).

### ASN-005: Ver Referentes

**Descripción**: Muestra la información de contacto de las personas responsables en un establecimiento.

**Reglas de Negocio**:
- **Consulta**: Solo lectura. Útil para que el supervisor sepa a quién notificar sobre observaciones.

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Ver Asignaciones | ✅ Solo las propias (lectura) | ✅ Todas (Gestión completa) |
| Asignar / Reasignar | ❌ No permitido | ✅ |
| Remover | ❌ No permitido | ✅ |
| Copiar Año | ❌ No permitido | ✅ |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-ASN-001 | Asignación creada | `Establecimiento(s) asignado(s) exitosamente.` |
| MSG-ASN-002 | Asignación removida | `Asignación removida exitosamente.` |
| MSG-ASN-003 | Año copiado | `Se copiaron [N] asignaciones del año [Origen] al [Destino].` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-ASN-101 | Duplicado | `Error: El registrador ya tiene asignado este establecimiento para el periodo seleccionado.` |
| MSG-ASN-102 | Est. Inactivo | `Error: No se pueden asignar establecimientos inactivos.` |
| MSG-ASN-103 | Sin selección | `Error: Debe seleccionar al menos un establecimiento.` |
| MSG-ASN-104 | Permiso denegado | `Error: No tiene permisos para gestionar asignaciones.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Asignación múltiple para todo el año
```gherkin
Dado que soy un Supervisor autenticado
Y estoy en la pantalla de Asignaciones
Cuando selecciono el registrador "Juan Pérez"
Y selecciono los establecimientos "Hospital Norte" y "CESFAM Sur"
Y dejo la opción de meses en "Todo el año"
Y presiono "Asignar"
Entonces ambos establecimientos quedan asignados a "Juan Pérez" para todo el año actual
```

### Escenario: Reasignación temporal por vacaciones
```gherkin
Dado que soy un Supervisor autenticado
Y "María López" tiene asignado el "Hospital Central" para todo el año
Cuando "María López" sale de vacaciones en Marzo
Y selecciono al registrador "Pedro Gómez"
Y selecciono el establecimiento "Hospital Central"
Y marco específicamente el mes "Marzo"
Y presiono "Asignar"
Entonces "Pedro Gómez" puede registrar en el "Hospital Central" solo durante Marzo
Y la asignación original de "María López" se mantiene para el resto del año
```

### Escenario: Copiar asignaciones de año anterior
```gherkin
Dado que soy un Supervisor autenticado
Y existen 50 asignaciones configuradas para el año 2025
Cuando uso la función "Copiar asignaciones" de 2025 a 2026
Entonces el sistema crea esas 50 asignaciones para el año 2026
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
|  | Juan Pérez       | [x] Hospital Norte                                  |  |
|  |                  | [x] CESFAM Sur                                      |  |
|  |                  | [ ] Hospital Central (Solo Marzo)                   |  |
|  +------------------+-----------------------------------------------------+  |
|  | María López      | [x] Hospital Central                                |  |
|  |                  | [x] CESFAM Este                                     |  |
|  +------------------+-----------------------------------------------------+  |
|                                                                              |
+==============================================================================+
```

### Formulario de Nueva Asignación

```
+==============================================================================+
|  NUEVA ASIGNACIÓN                                                            |
+==============================================================================+
|                                                                              |
|  Registrador:        [ Juan Pérez ▼ ]                                        |
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
|  ( ) Todo el año 2026                                                        |
|  ( ) Meses específicos:                                                      |
|      [ ] Ene  [x] Feb  [x] Mar  [ ] Abr  [ ] May  [ ] Jun                   |
|      [ ] Jul  [ ] Ago  [ ] Sep  [ ] Oct  [ ] Nov  [ ] Dic                   |
|                                                                              |
|                          [ Cancelar ]    [ Asignar ]                        |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Rol exclusivo | ✅ Aceptada → Solo Supervisores gestionan |
| 2 | Alcance temporal | ✅ Modificada → **Anuales por defecto, con reasignación temporal por meses** |
| 3 | Unicidad | ✅ Aceptada → No duplicados (Registrador+Est+Periodo) |
| 4 | Referentes | ✅ Aceptada → Consulta de contactos |
| 5 | Copiar año | ✅ Aceptada → Acumulativo, no destructivo |
| 6 | Remoción | ✅ Aceptada → Solo quita la relación |
| 7 | Est. inactivos | ✅ Aceptada → Bloqueado asignar inactivos |
| 8 | Asignación múltiple | ✅ Aceptada → Selección manual (checkboxes) |
