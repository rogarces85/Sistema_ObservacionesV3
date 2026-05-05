# Manual de Flujo del Sistema de Observaciones REM

**Sistema de Observaciones REM — Servicio de Salud Osorno**  
**Versión:** 2.0.0  
**Fecha:** Mayo 2026

---

## 1. Introducción

Este documento describe el flujo de trabajo completo del **Sistema de Observaciones REM**, dividido por rol de usuario: **Supervisor** y **Registrador**. El sistema permite registrar, supervisar y reportar observaciones de inconsistencias en los envíos REM de los establecimientos de salud del Servicio de Salud Osorno.

### Roles del Sistema

| Rol | Descripción | Usuarios |
|-----|-------------|----------|
| **Supervisor** | Gestiona usuarios, asigna establecimientos, supervisa observaciones y genera reportes. | Cecilia (supervisor1) |
| **Registrador** | Registra observaciones únicamente sobre los establecimientos que le fueron asignados. | Rodrigo, Victoria, Roxana, Marcelo |

---

## 2. Flujo para el Supervisor

### 2.1 Acceso al Sistema

1. Ingresar a la URL del sistema.
2. Iniciar sesión con credenciales de supervisor.
3. El sistema valida credenciales contra la base de datos remota (`10.8.152.199`).
4. Se redirige automáticamente al **Dashboard**.

### 2.2 Dashboard del Supervisor

Al ingresar, el supervisor visualiza:

- **Estadísticas del año actual:** total de observaciones, pendientes, aprobados y con problemas.
- **Gráfico de observaciones por mes.**
- **Top tipos de error.**
- **Últimas observaciones registradas.**
- **Acceso rápido** a: Nueva Observación, Descargar Plantilla, Generar Reportes, Supervisar.

#### Alertas de Asignación

El sistema muestra una **alerta roja** en el dashboard si existen registradores que **no tienen establecimientos asignados** para el año en curso. La alerta incluye:

- Número de registradores afectados.
- Nombre y usuario de cada registrador sin asignaciones.
- Enlace directo a la sección **"Asignación de Establecimientos"**.

> **Ejemplo:** *"3 registrador(es) sin establecimientos asignados: Rodrigo Garcés, Victoria Martínez, Roxana Mancilla. → Ir a Asignación de Establecimientos"*

### 2.3 Asignación de Establecimientos

Esta es la funcionalidad clave para habilitar el trabajo de los registradores.

#### Paso 1: Seleccionar Año
- En la parte superior se selecciona el año (por defecto el año en curso).
- Existe la opción de **"Copiar Año Anterior"** para replicar las asignaciones del año previo.

#### Paso 2: Seleccionar Registrador
- Panel izquierdo con la lista de registradores activos.
- Cada registrador muestra un contador con la cantidad de establecimientos que tiene asignados para el año seleccionado.

#### Paso 3: Asignar / Reasignar Establecimientos
- Al hacer clic en **"Asignar / Reasignar"** se abre un modal con todos los establecimientos activos del sistema.
- Los establecimientos se muestran agrupados por comuna.
- Cada establecimiento tiene un indicador visual:
  - **Libre:** fondo blanco, checkbox vacío, disponible para asignar.
  - **Asignado al registrador actual:** fondo azul claro, checkbox checkeado, badge *"Asignado a ti"*.
  - **Asignado a otro registrador:** fondo rojo claro, checkbox checkeado y deshabilitado, badge *"Asignado a: [Nombre]"*.
- El supervisor marca los establecimientos deseados y presiona **"Guardar Asignaciones"**.
- El sistema **reemplaza** las asignaciones previas del registrador para ese año, manteniendo solo las seleccionadas.

> **Restricción:** Un establecimiento no puede ser asignado a más de un registrador para el mismo año.

#### Paso 4: Remover Asignación Individual
- En el panel derecho, junto a cada establecimiento asignado, existe un botón **✕** para removerlo del registrador.
- Al removerlo, queda libre para ser asignado a otro registrador.

### 2.4 Registro de Observaciones (también puede hacerlo el supervisor)

El supervisor puede registrar observaciones sobre **cualquier establecimiento** del sistema, sin restricciones de asignación.

1. Ir a **Observaciones** → **Nueva Observación**.
2. Completar el formulario:
   - Mes, Establecimiento, Serie REM, Hoja REM.
   - Tipo de error, detalle de la observación.
   - Plazo de entrega, uso de validador.
   - Respuesta del establecimiento (opcional).
   - Clasificación y detalle de error (opcional, se habilitan con un checkbox).
3. Guardar. La observación queda en estado **"Pendiente"**.

### 2.5 Supervisión de Observaciones

1. Ir a **Supervisión**.
2. Visualizar listado de observaciones pendientes.
3. Para cada observación, el supervisor puede:
   - **Ver detalle** completo.
   - **Aprobar:** cambia estado a *aprobado*.
   - **Rechazar:** cambia estado a *rechazado*.
   - **Marcar como error:** cambia estado a *error*.
   - **Justificar:** cambia estado a *justificado*.
4. Cada cambio de estado queda registrado en el **Historial de Estados**.

### 2.6 Gestión de Usuarios

1. Ir a **Usuarios** (solo supervisores).
2. Crear nuevos usuarios (registradores o supervisores).
3. Activar / desactivar usuarios.
4. Restablecer contraseñas.

### 2.7 Reportes

1. Ir a **Reportes**.
2. Exportar datos a **Excel** filtrando por:
   - Año, mes, estado, establecimiento, comuna.
3. Generar reportes consolidados para análisis.

---

## 3. Flujo para los Registradores

### 3.1 Acceso al Sistema

1. Ingresar a la URL del sistema.
2. Iniciar sesión con sus credenciales personales.
3. El sistema valida contra la base de datos remota.
4. Se redirige al **Dashboard**.

### 3.2 Dashboard del Registrador

Al ingresar, el registrador visualiza:

- **Estadísticas de sus observaciones** para el año actual.
- **Gráfico de observaciones por mes** (solo las suyas).
- **Últimas observaciones registradas** por él.
- **Acceso rápido** a Nueva Observación y Generar Reportes.

#### Alerta de Sin Asignaciones

Si el registrador **no tiene establecimientos asignados** para el año en curso, aparece una **alerta amarilla** destacada:

> *"No tiene establecimientos asignados para el año 2026. Contacte a su supervisor para que le asigne los establecimientos correspondientes."*

**Mientras no tenga asignaciones:**
- No podrá crear nuevas observaciones.
- Los botones **"Nueva Observación"** e **"Importar"** están ocultos.
- En su lugar aparece un mensaje informativo explicando la situación.

### 3.3 Visualización de Observaciones

- Ir a **Observaciones**.
- Listado de todas las observaciones que él ha registrado.
- Puede filtrar por estado, mes o texto de búsqueda.
- Solo puede **editar** observaciones en estado **"Pendiente"**.
- Puede **ver detalle** de todas sus observaciones.

### 3.4 Registro de Observaciones (solo establecimientos asignados)

Si el registrador **sí tiene establecimientos asignados**:

1. Ir a **Observaciones** → **Nueva Observación**.
2. El campo **"Establecimiento"** solo muestra los establecimientos que le fueron asignados para el año actual.
3. Completar el resto del formulario (mes, serie, hoja, tipo de error, detalle, etc.).
4. Al guardar, el sistema valida en el backend que el establecimiento seleccionado realmente pertenezca a sus asignaciones.
5. Si intenta manipular la petición para usar un establecimiento no asignado, la API rechaza la operación con error **403 Forbidden**.

> **Mensaje de rechazo:** *"El establecimiento seleccionado no está asignado a su usuario."*

### 3.5 Importación Masiva

Si tiene establecimientos asignados, el registrador puede usar la función de **Importar** desde Excel:

1. Descargar la plantilla Excel desde el sistema.
2. Completar el archivo con las observaciones.
   - Columna `codigo_establecimiento` (obligatorio): código numérico del establecimiento.
   - El sistema valida que el código corresponda a uno de sus establecimientos asignados.
3. Subir el archivo y previsualizar.
4. Confirmar la importación.

### 3.6 Edición de Observaciones

- Solo puede editar observaciones **propias** y en estado **"Pendiente"**.
- No puede modificar observaciones ya supervisadas (aprobado, rechazado, error, justificado).
- Al editar, el campo establecimiento sigue restringido a sus asignaciones.

---

## 4. Flujo General del Sistema (Resumen)

```
┌─────────────────────────────────────────────────────────────────┐
│                        INICIO DEL AÑO                           │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  SUPERVISOR: Asigna establecimientos a cada registrador         │
│  (Asignación de Establecimientos → selecciona año y registrador)│
└─────────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
      ┌───────────┐   ┌───────────┐   ┌───────────┐
      │Registrador│   │Registrador│   │Registrador│
      │    1      │   │    2      │   │    N      │
      └─────┬─────┘   └─────┬─────┘   └─────┬─────┘
            │               │               │
            ▼               ▼               ▼
      ┌─────────────────────────────────────────────┐
      │ Registradores registran observaciones        │
      │ sobre sus establecimientos asignados         │
      │ (Web individual o Importación masiva)        │
      └─────────────────────┬───────────────────────┘
                            │
                            ▼
      ┌─────────────────────────────────────────────┐
      │ Estado inicial: PENDIENTE                   │
      └─────────────────────┬───────────────────────┘
                            │
                            ▼
      ┌─────────────────────────────────────────────┐
      │ SUPERVISOR: Revisa observaciones pendientes │
      │ (Supervisión → Aprobar / Rechazar / Error / │
      │  Justificar)                                │
      └─────────────────────┬───────────────────────┘
                            │
              ┌─────────────┼─────────────┐
              ▼             ▼             ▼
         ┌────────┐   ┌────────┐   ┌──────────┐
         │APROBADO│   │RECHAZADO│  │JUSTIFICADO│
         └────────┘   └────────┘   └──────────┘
                            │
                            ▼
      ┌─────────────────────────────────────────────┐
      │ Generación de Reportes y Exportación Excel  │
      └─────────────────────────────────────────────┘
```

---

## 5. Tabla de Permisos por Rol

| Funcionalidad | Supervisor | Registrador |
|---------------|:----------:|:-----------:|
| Ver Dashboard | ✅ | ✅ |
| Crear observaciones (cualquier establecimiento) | ✅ | ❌ |
| Crear observaciones (solo asignados) | — | ✅ |
| Editar observaciones propias (pendientes) | ✅ | ✅ |
| Editar observaciones de otros | ✅ | ❌ |
| Eliminar observaciones | ✅ | ❌ |
| Supervisar cambios de estado | ✅ | ❌ |
| Asignar establecimientos a registradores | ✅ | ❌ |
| Ver establecimientos asignados a otros | ✅ | ❌ |
| Gestionar usuarios | ✅ | ❌ |
| Generar reportes Excel | ✅ | ✅ |
| Importar observaciones masivas | ✅ | ✅ (solo asignados) |

---

## 6. Consideraciones Técnicas

- **Base de datos:** MySQL en servidor `10.8.152.199:3306`, base `observaciones_rem`.
- **Asignaciones por año:** Las asignaciones de establecimientos a registradores son anuales. Cada año debe reasignarse (o copiarse desde el año anterior).
- **Establecimientos activos:** Solo los establecimientos marcados como `activo = 1` aparecen en el sistema.
- **Validación de seguridad:** El backend valida en cada operación de creación/actualización que un registrador solo use establecimientos asignados, incluso si intenta saltarse la restricción del frontend.
- **Comunas actuales:** OSORNO, PURRANQUE, PUYEHUE, RIO NEGRO, PUERTO OCTAY, SAN JUAN DE LA COSTA, SAN PABLO.

---

*Documento generado automáticamente por el Sistema de Observaciones REM.*
