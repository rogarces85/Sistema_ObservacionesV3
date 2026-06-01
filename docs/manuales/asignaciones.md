# Manual de Usuario - Asignación de Establecimientos

## Descripción

El módulo de **Asignación de Establecimientos** permite al rol **Supervisor** gestionar la asignación de establecimientos de salud a los registradores, tanto de forma **anual** como **temporal**. También permite consultar los referentes (personas de contacto) de cada establecimiento y copiar asignaciones entre años.

## Acceso

1. Iniciar sesión con credenciales de **Supervisor**
2. En el menú lateral, hacer clic en **"Asignaciones"**
3. El rol **Registrador** no tiene acceso a este módulo (redirige al Dashboard)

---

## Vista Principal

### Mockup

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│  Gestione los establecimientos y referentes por año                                     │
│  Asignación de Establecimientos                          [Año: 2026▼] [Copiar Año Ant.] │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐               │
│  │      5       │  │      3       │  │      2       │  │      2       │               │
│  │ Registradores│  │Con Asignación│  │Temp. Activas │  │ Sin Asignar  │               │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘               │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  ┌──────────────────────────┐  ┌──────────────────────────────────────────────────┐    │
│  │  Registradores           │  │  Establecimientos y Contactos     [+ Asignar]    │    │
│  │  ┌────────────────────┐  │  │                                                    │    │
│  │  │ 👤 María González  │  │  │  Osorno                                            │    │
│  │  │   @mgonzalez       │  │  │  ┌────────────┬────────┬──────────┬──────┬───────┐ │    │
│  │  ├────────────────────┤  │  │  │HBSJO       │Todo el │ Anual    │  👤  │   ✕   │ │    │
│  │  │ 👤 Juan Pérez      │  │  │  │CESFAM Rahue│Ene-Mar │Temporal  │  👤  │   ✕   │ │    │
│  │  │   @jperez          │  │  │  └────────────┴────────┴──────────┴──────┴───────┘ │    │
│  │  └────────────────────┘  │  │                                                    │    │
│  └──────────────────────────┘  └──────────────────────────────────────────────────┘    │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  Reasignaciones Temporales Activas                                                      │
│  ┌──────────────┬────────┬────────┬──────────────────┬────────────────┬──────────┐     │
│  │Establecim.   │Comuna  │Meses   │Registrador Temp. │Titular Anual   │Fecha     │     │
│  ├──────────────┼────────┼────────┼──────────────────┼────────────────┼──────────┤     │
│  │CESFAM Rahue  │Osorno  │Ene-Mar │Juan Pérez        │María González  │15/01/2026│     │
│  └──────────────┴────────┴────────┴──────────────────┴────────────────┴──────────┘     │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

### Componentes de la Vista

| Componente | Descripción |
|------------|-------------|
| **Selector de Año** | Permite cambiar el año de trabajo (2020 hasta año siguiente) |
| **Copiar Año Anterior** | Botón para duplicar todas las asignaciones del año anterior |
| **Estadísticas Rápidas** | 4 tarjetas: Registradores, Con Asignación, Temporales Activas, Sin Asignar |
| **Panel de Registradores** | Lista de registradores activos con avatar e iniciales |
| **Panel de Establecimientos** | Tabla agrupada por comuna con estado de asignación |
| **Temporales Activas** | Tabla con todas las reasignaciones temporales vigentes |

---

## Flujo de Asignación Anual

### Paso 1: Seleccionar un Registrador

1. En el panel izquierdo, hacer clic en el nombre del registrador deseado
2. El registrador seleccionado se resalta con fondo azul
3. El panel derecho muestra los establecimientos asignados a ese registrador

### Paso 2: Asignar Establecimientos

1. Hacer clic en el botón **"+ Asignar"** (panel derecho, esquina superior)
2. Se abre el modal de asignación

### Mockup del Modal de Asignación

```
┌──────────────────────────────────────────────────────────────────────┐
│  Asignar Establecimientos                                       [X] │
│  Asignando a: María González — Año 2026                             │
├──────────────────────────────────────────────────────────────────────┤
│  Tipo de Asignación                                                  │
│  ☑ Anual — Asignación base para todo el año                         │
│  ○ Temporal — Reasignación por meses específicos                    │
│                                                                      │
│  Buscar Establecimiento                                              │
│  [Hospital, CESFAM, Osorno...]                                       │
│                                                                      │
│  Establecimientos Disponibles                                        │
│  ┌────────────────────────────────────────────────────────────────┐  │
│  │ Osorno                                                         │  │
│  │ ☐ Hospital Base San José de Osorno              [Asignado]     │  │
│  │ ☑ CESFAM Rahue                                                 │  │
│  │ ☑ CESFAM Puerto Octay                                         │  │
│  │ ☐ Posta Río Bueno                                              │  │
│  └────────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  Periodo de validez                                                  │
│  ☑ Todo el año 2026                                                  │
│  ○ Meses específicos                                                 │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│                    [Cancelar]  [Guardar Asignaciones]                │
└──────────────────────────────────────────────────────────────────────┘
```

### Paso 3: Seleccionar Establecimientos

1. Usar el campo de búsqueda para filtrar por nombre o comuna
2. Marcar los checkboxes de los establecimientos a asignar
3. Los establecimientos ya asignados aparecen con fondo verde y etiqueta "Asignado"

### Paso 4: Guardar

1. Hacer clic en **"Guardar Asignaciones"**
2. El sistema procesa la asignación masiva de forma transaccional
3. Si algún establecimiento tiene conflicto, se revierte toda la operación
4. Se muestra notificación de éxito o error

---

## Flujo de Asignación Temporal

### ¿Cuándo usar asignación temporal?

- Cuando un registrador necesita cubrir un establecimiento solo por ciertos meses
- Por ejemplo: licencia médica, vacaciones, rotación temporal

### Paso 1: Seleccionar Tipo Temporal

1. En el modal de asignación, seleccionar **"Temporal"**
2. Aparece la sección **"Periodo de validez"**

### Paso 2: Seleccionar Meses

1. Seleccionar **"Meses específicos"**
2. Aparecen 12 checkboxes con los meses del año (Ene a Dic)
3. Marcar los meses deseados

```
  Periodo de validez
  ○ Todo el año 2026
  ☑ Meses específicos
  
  ☑ Ene   ☑ Feb   ☑ Mar   ☐ Abr
  ☐ May   ☐ Jun   ☐ Jul   ☐ Ago
  ☐ Sep   ☐ Oct   ☐ Nov   ☐ Dic
```

### Paso 3: Validación de Solapamiento

El sistema **rechaza** la asignación temporal si:
- Ya existe una asignación temporal de **otro registrador** para el mismo establecimiento y mes
- Ejemplo: Si Juan tiene temporal Ene-Mar en CESFAM Rahue, María no puede tener temporal Feb en el mismo establecimiento

### Paso 4: Guardar

1. Hacer clic en **"Guardar Asignaciones"**
2. La asignación temporal se guarda y aparece en la sección "Reasignaciones Temporales Activas"

---

## Reglas de Fusión de Meses

Cuando se asigna un establecimiento que ya tiene una asignación previa del **mismo registrador**, se aplican estas reglas:

| Asignación Actual | Nueva Asignación | Resultado |
|-------------------|------------------|-----------|
| ALL (todo el año) | ALL (todo el año) | Sin cambios |
| ALL (todo el año) | Lista específica (1,2,3) | Se reemplaza por la nueva lista |
| Lista específica (1,2,3) | ALL (todo el año) | Se actualiza a ALL |
| Lista (1,2,3) | Lista (4,5,6) | Se fusionan: 1,2,3,4,5,6 |
| Lista (1-11) | Lista (12) | Se convierte a ALL |

**Importante**: La asignación explícita del supervisor tiene prioridad. Si se asigna una lista específica sobre una asignación ALL, la nueva lista reemplaza completamente.

---

## Copiar Asignaciones entre Años

### Pasos

1. Seleccionar el año destino en el selector de año (ej: 2026)
2. Hacer clic en **"Copiar Año Anterior"**
3. Confirmar la acción: *"¿Copiar todas las asignaciones de 2025 a 2026?"*
4. El sistema copia:
   - Todas las asignaciones **anuales**
   - Todas las asignaciones **temporales**
5. Se muestra la cantidad de asignaciones copiadas

### Importante

- Las asignaciones se copian **tal cual**, incluyendo los meses
- Si ya existen asignaciones en el año destino, se **duplican** (no se reemplazan)
- Se recomienda copiar antes de hacer modificaciones manuales

---

## Gestión de Referentes desde Asignaciones

### Acceder a los Referentes

1. En la tabla de establecimientos, hacer clic en el botón **👤 (Ver referentes)**
2. Se abre un modal con la lista de referentes del establecimiento

### Mockup del Modal de Referentes

```
┌─────────────────────────────────────────────────────────────────┐
│  Referentes - Hospital Base San José de Osorno             [X] │
├─────────────────────────────────────────────────────────────────┤
│  ┌────────────────┬──────────────────────┬──────────┬─────────┐│
│  │Nombre          │Cargo                 │Teléfono  │Email    ││
│  ├────────────────┼──────────────────────┼──────────┼─────────┤│
│  │María González  │Encargado Estadísticas│+569123.. │mgonz..  ││
│  │Juan Pérez      │Digitador Estadísticas│+569456.. │jperez.. ││
│  └────────────────┴──────────────────────┴──────────┴─────────┘│
│                                                                 │
│                                [Cerrar]                         │
└─────────────────────────────────────────────────────────────────┘
```

### Notas

- Los referentes se muestran en orden: Encargado de Estadísticas primero, luego Digitador
- Para **crear, editar o eliminar** referentes, usar el módulo de **Establecimientos**
- Desde asignaciones solo se pueden **consultar** los referentes

---

## Reasignaciones Temporales Activas

La sección inferior muestra todas las asignaciones temporales vigentes:

| Columna | Descripción |
|---------|-------------|
| **Establecimiento** | Nombre del establecimiento reasignado |
| **Comuna** | Comuna del establecimiento |
| **Meses** | Periodo de la reasignación temporal (badge naranja) |
| **Registrador Temporal** | Quién tiene la asignación temporal |
| **Titular Anual** | Quién tiene la asignación anual (puede ser "Sin titular") |
| **Fecha** | Fecha de creación de la reasignación |

### Indicador Visual de Temporales

En la tabla de establecimientos, si un establecimiento tiene una asignación temporal de **otro registrador**, aparece un badge amarillo con ícono de reloj:

```
🕐 Temporal
```

---

## Quitar una Asignación

### Pasos

1. En la tabla de establecimientos, identificar la asignación a quitar
2. Hacer clic en el botón **✕ (Quitar asignación)** (solo aparece en asignaciones existentes)
3. Confirmar la acción
4. La asignación se elimina y la tabla se actualiza

---

## Flujo Completo de Trabajo

### Escenario: Asignación anual completa al inicio de año

1. **Copiar del año anterior** (si aplica):
   - Seleccionar año 2026
   - Clic en "Copiar Año Anterior"
   - Confirmar

2. **Revisar y ajustar**:
   - Seleccionar cada registrador
   - Verificar establecimientos asignados
   - Quitar asignaciones que ya no correspondan

3. **Asignar nuevos establecimientos**:
   - Seleccionar registrador
   - Clic en "+ Asignar"
   - Seleccionar tipo "Anual"
   - Marcar establecimientos
   - Guardar

### Escenario: Reasignación temporal por licencia médica

1. Seleccionar el registrador que cubrirá la licencia
2. Clic en "+ Asignar"
3. Seleccionar tipo "Temporal"
4. Seleccionar meses específicos (ej: Mar, Abr, May)
5. Marcar el/los establecimiento(s) a cubrir
6. Guardar
7. Verificar que aparece en "Reasignaciones Temporales Activas"

---

## Notas Técnicas

- **Asignación masiva transaccional**: Si falla alguna asignación, se revierten todas (rollback)
- **Formato canónico de meses**: "1,2,3" orden ascendente, "ALL" como texto
- **CSRF**: Token validado en todas las operaciones POST
- **Permisos**: Solo rol Supervisor (403 para Registrador)
- **Respuesta JSON**: Formato `{"success": true|false, "data": ..., "error": "...", "code": 200|400|401|403|404|500}`
- **Temporal sobre anual**: Si un registrador tiene asignación anual y otro tiene temporal para el mismo mes, la temporal prima

---

## Solución de Problemas

| Problema | Causa Posible | Solución |
|----------|---------------|----------|
| "Ya existe una asignación temporal para ese periodo con otro registrador" | Solapamiento de meses | Seleccionar meses diferentes o quitar la temporal existente primero |
| "El establecimiento ya está asignado a otro registrador para ese periodo" | Conflicto de asignación anual | Quitar la asignación del otro registrador primero |
| "Para asignación temporal debe especificar los meses" | Temporal sin meses | Seleccionar "Meses específicos" y marcar al menos un mes |
| "Usuario y establecimiento son requeridos" | Datos incompletos | Seleccionar registrador y al menos un establecimiento |
| Botones de acción no responden | Sesión expirada | Recargar página y volver a iniciar sesión |
| No se ven los establecimientos | No hay registrador seleccionado | Hacer clic en un registrador del panel izquierdo |
