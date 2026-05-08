# Manual de Usuario — Registrador

**Sistema de Observaciones REM — Servicio de Salud Osorno**  
**Versión:** 2.1.0  
**Fecha:** Mayo 2026

---

## Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Acceso al Sistema](#2-acceso-al-sistema)
3. [Panel de Control (Dashboard)](#3-panel-de-control-dashboard)
4. [Gestión de Observaciones](#4-gestión-de-observaciones)
5. [Importación Masiva](#5-importación-masiva)
6. [Reportes y Estadísticas](#6-reportes-y-estadísticas)
7. [Mi Perfil](#7-mi-perfil)
8. [Preguntas Frecuentes](#8-preguntas-frecuentes)
9. [Glosario](#9-glosario)

---

## 1. Introducción

### 1.1 ¿Qué es el Sistema de Observaciones REM?

El Sistema de Observaciones REM es una herramienta digital que permite registrar, gestionar y hacer seguimiento de las inconsistencias detectadas en los envíos del **Resumen Estadístico Mensual (REM)** de los establecimientos de salud del Servicio de Salud Osorno.

### 1.2 ¿Qué puede hacer un Registrador?

Como **Registrador**, usted puede:

| Funcionalidad | Descripción |
|---------------|-------------|
| ✅ Ver estadísticas | Dashboard con resumen de sus observaciones |
| ✅ Crear observaciones | Registrar inconsistencias en establecimientos asignados |
| ✅ Editar observaciones | Modificar observaciones propias en estado "Pendiente" |
| ✅ Importar masivamente | Cargar múltiples observaciones desde Excel |
| ✅ Ver reportes | 6 tipos de reportes con gráficos interactivos |
| ✅ Exportar datos | Descargar reportes en Excel o PDF |
| ✅ Generar PDF detallado | Reporte jerárquico para impresión |
| ✅ Gestionar perfil | Actualizar datos personales y contraseña |

### 1.3 ¿Qué NO puede hacer un Registrador?

| Restricción | Explicación |
|-------------|-------------|
| ❌ Supervisar | No puede aprobar, rechazar ni cambiar estados |
| ❌ Gestionar usuarios | No puede crear ni modificar otros usuarios |
| ❌ Asignar establecimientos | Solo el supervisor asigna establecimientos |
| ❌ Ver datos de otros | Solo ve sus propias observaciones |
| ❌ Crear en cualquier establecimiento | Solo en los que le fueron asignados |

---

## 2. Acceso al Sistema

### 2.1 Iniciar Sesión

1. Abra su navegador web y vaya a la URL del sistema proporcionada por su supervisor.
2. Verá la pantalla de inicio de sesión:

```
┌─────────────────────────────────────────┐
│     Sistema de Observaciones REM        │
│     Servicio de Salud Osorno            │
│                                         │
│  Usuario:  [________________]           │
│  Clave:    [________________]           │
│  Año:      [2026 ▼]                     │
│                                         │
│  [  Iniciar Sesión  ]                   │
└─────────────────────────────────────────┘
```

3. Complete los campos:
   - **Usuario:** Su nombre de usuario asignado (ej: `registrador1`)
   - **Clave:** Su contraseña personal
   - **Año:** Seleccione el año en curso (por defecto el año actual)

4. Haga clic en **"Iniciar Sesión"**.

### 2.2 Si no puede iniciar sesión

| Problema | Solución |
|----------|----------|
| "Credenciales incorrectas" | Verifique usuario y contraseña. Contacte a su supervisor si olvidó su clave. |
| "Usuario inactivo" | Contacte a su supervisor para que active su cuenta. |
| "No tiene establecimientos asignados" | Contacte a su supervisor para que le asigne establecimientos. |

### 2.3 Cerrar Sesión

1. Haga clic en su nombre en la barra superior.
2. Seleccione **"Cerrar Sesión"**.
3. Será redirigido a la pantalla de login.

> **Importante:** Siempre cierre sesión cuando termine de usar el sistema, especialmente en computadores compartidos.

---

## 3. Panel de Control (Dashboard)

Al iniciar sesión, verá el **Panel de Control** con la siguiente información:

### 3.1 Tarjetas de Estadísticas

En la parte superior encontrará tarjetas con números clave:

```
┌──────────┐  ┌──────────┐  ┌──────────  ┌──────────┐
│   TOTAL  │  │PENDIENTES│  │ APROBADOS│  │CON ERROR │
│    150   │  │    45    │  │    80    │  │    25    │
└──────────┘  └──────────  └──────────┘  └──────────┘
```

- **Total:** Cantidad total de observaciones que usted ha registrado en el año seleccionado.
- **Pendientes:** Observaciones que aún no han sido revisadas por el supervisor.
- **Aprobados:** Observaciones que el supervisor ha aprobado.
- **Con Error:** Observaciones marcadas con estado "error" que requieren corrección.

### 3.2 Gráficos

**Distribución por Estado:**
- Barra de progreso que muestra el porcentaje de observaciones en cada estado.
- Colores: 🟡 Pendiente | 🟢 Aprobado | 🔴 Rechazado | 🔵 Error |  Justificado

**Observaciones por Mes:**
- Gráfico de barras que muestra cuántas observaciones registró cada mes.
- Útil para identificar períodos con mayor carga de trabajo.

**Top Tipos de Error:**
- Lista de los tipos de error más frecuentes en sus observaciones.
- Ayuda a identificar patrones recurrentes.

### 3.3 Últimas Observaciones

Tabla con las 10 observaciones más recientes que usted ha registrado:

| ID | Establecimiento | Serie | Hoja | Tipo Error | Estado | Fecha |
|----|----------------|-------|------|------------|--------|-------|
| 150 | CESFAM Rahue Alto | SERIE A | A03 | ERROR | Pendiente | 08/05/2026 |

### 3.4 Acciones Rápidas

Botones de acceso directo:
- **+ Nueva Observación:** Ir al formulario de registro.
- **📥 Importar:** Ir a la carga masiva desde Excel.
- **📊 Reportes:** Ir al módulo de reportes.

### 3.5 Alertas

**Alerta Amarilla (Sin Asignaciones):**
> ⚠️ *"No tiene establecimientos asignados para el año 2026. Contacte a su supervisor para que le asigne los establecimientos correspondientes."*

Si ve esta alerta:
- No podrá crear nuevas observaciones.
- Los botones "Nueva Observación" e "Importar" estarán ocultos.
- Contacte a su supervisor para resolver la situación.

---

## 4. Gestión de Observaciones

### 4.1 Ver Listado de Observaciones

1. En el menú lateral, haga clic en **"Observaciones"**.
2. Verá una tabla con todas sus observaciones del año seleccionado.

**Columnas de la tabla:**

| Columna | Descripción |
|---------|-------------|
| ID | Número único de identificación |
| Mes | Mes de la observación |
| Establecimiento | Nombre del establecimiento |
| Serie | Serie REM (A, P, D, BS, BM, ANEXO) |
| Hoja | Código de la hoja (A01, P07, etc.) |
| Tipo Error | S/OBSERVACION, ERROR, REVISAR, F/PLAZO |
| Detalle | Texto de la observación |
| Plazo | Dentro de plazo / Fuera de plazo |
| Validador | Sí / No |
| Estado | Pendiente, Aprobado, Rechazado, Error, Justificado |
| Fecha | Fecha de registro |

### 4.2 Filtrar Observaciones

Encima de la tabla encontrará filtros:

| Filtro | Uso |
|--------|-----|
| **Estado** | Seleccione un estado específico (ej: solo "Pendientes") |
| **Mes** | Filtre por mes específico |
| **Buscar** | Escriba texto para buscar en el detalle de la observación |

### 4.3 Crear una Nueva Observación

1. Haga clic en el botón **"+ Nueva Observación"** (en el dashboard o en la lista de observaciones).
2. Se abrirá un formulario modal. Complete los campos:

**Campos obligatorios (marcados con *):**

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| **Mes \*** | Mes al que corresponde la observación | Noviembre |
| **Establecimiento \*** | Seleccione de la lista (solo los asignados a usted) | CESFAM Rahue Alto |
| **Serie REM \*** | Serie del REM | SERIE A |
| **Hoja REM \*** | Hoja específica de la serie | A03 |
| **Tipo de Error \*** | Tipo de inconsistencia detectada | ERROR |
| **Detalle de la Observación \*** | Descripción completa del problema | "SERIE A A03 celda C213 (dato 16) debe ser igual a la suma celdas 214+215 (dato 1)" |
| **Plazo de Entrega \*** | ¿El envío fue dentro o fuera de plazo? | Dentro de plazo |
| **Usa Validador \*** | ¿El establecimiento usó el validador REM? | Sí |

**Campos opcionales:**

| Campo | Descripción |
|-------|-------------|
| **Respuesta del Establecimiento** | Si el establecimiento ya respondió a la observación |
| **Clasificación** | Estado de seguimiento (ej: "Corregido", "Sin respuesta del Establecimiento") |
| **Detalle de Error** | Información adicional sobre el error |

3. Haga clic en **"Guardar Observación"**.
4. La observación se crea con estado **"Pendiente"** y queda lista para revisión del supervisor.

> **Nota:** El campo "Establecimiento" solo muestra los establecimientos que su supervisor le ha asignado para el año en curso.

### 4.4 Editar una Observación

**Reglas:**
- Solo puede editar observaciones **propias**.
- Solo puede editar observaciones en estado **"Pendiente"**.
- No puede editar observaciones ya supervisadas (aprobado, rechazado, error, justificado).

**Pasos:**
1. En la lista de observaciones, ubique la observación que desea editar.
2. Haga clic en el botón **"✏️ Editar"** en la columna de acciones.
3. Se abrirá el formulario con los datos cargados.
4. Modifique los campos necesarios.
5. Haga clic en **"Guardar Cambios"**.

### 4.5 Ver Detalle de una Observación

1. En la lista de observaciones, haga clic en el botón **"👁️ Ver"** o en el ID de la observación.
2. Se abrirá un panel con toda la información:
   - Datos completos de la observación
   - Historial de cambios de estado (quién cambió el estado, cuándo y con qué comentario)
   - Información del establecimiento y comuna

### 4.6 Estados de las Observaciones

| Estado | Color | Significado | ¿Puede editar? |
|--------|-------|-------------|----------------|
| 🟡 **Pendiente** | Amarillo | Aguardando revisión del supervisor | ✅ Sí |
| 🟢 **Aprobado** | Verde | Revisado y aprobado por el supervisor | ❌ No |
| 🔴 **Rechazado** | Rojo | No aprobado, requiere justificación | ❌ No |
| 🔵 **Error** | Azul | Requiere corrección | ❌ No |
| ⚪ **Justificado** | Gris | Rechazo justificado por el registrador | ❌ No |

---

## 5. Importación Masiva

La importación masiva permite cargar múltiples observaciones desde un archivo Excel, ahorrando tiempo cuando tiene muchas observaciones para registrar.

### 5.1 Descargar la Plantilla

1. En el listado de observaciones, haga clic en **"📥 Descargar Plantilla"**.
2. Se descargará un archivo Excel (`.xlsx`) con las columnas necesarias.

**Columnas de la plantilla:**

| Columna | Obligatoria | Descripción |
|---------|:-----------:|-------------|
| `codigo_establecimiento` | ✅ | Código numérico del establecimiento (ej: 123100) |
| `mes` | ✅ | Mes en español (ej: Noviembre) |
| `tipo` | ✅ | Tipo de error (ERROR, S/OBSERVACION, REVISAR, F/PLAZO) |
| `serie` | ❌ | Serie REM (SERIE A, SERIE P, etc.) |
| `rem` | ❌ | Código de la hoja REM |
| `detalle_observacion` | ❌ | Descripción del problema |
| `plazo_entrega` | ❌ | dentro_plazo / fuera_plazo |
| `usa_validador` | ❌ | si / no |
| `clasificacion` | ❌ | Estado de seguimiento |
| `detalle_error` | ❌ | Información adicional |

### 5.2 Completar la Plantilla

1. Abra el archivo Excel descargado.
2. Complete una fila por cada observación.
3. **Importante:** El `codigo_establecimiento` debe corresponder a uno de sus establecimientos asignados.

**Códigos de establecimientos comunes:**

| Código | Establecimiento | Comuna |
|--------|----------------|--------|
| 123100 | Hospital Base San José de Osorno | OSORNO |
| 123300 | CESFAM Dr. Pedro Jáuregui | OSORNO |
| 123301 | CESFAM Dr. Marcelo Lopetegui Adams | OSORNO |
| 123303 | CESFAM Rahue Alto | OSORNO |
| 123306 | CESFAM Pampa Alegre | OSORNO |
| 123310 | CESFAM Quinto Centenario | OSORNO |
| 123101 | Hospital de Purranque | PURRANQUE |
| 123307 | CESFAM Purranque | PURRANQUE |
| 123103 | Hospital de Puerto Octay | PUERTO OCTAY |
| 123102 | Hospital de Río Negro | RIO NEGRO |
| 123304 | CESFAM Entre Lagos | PUYEHUE |

> **Consejo:** Si no conoce el código de un establecimiento, pregunte a su supervisor o revíselo en el sistema al crear una observación manualmente.

### 5.3 Subir el Archivo

1. En el listado de observaciones, haga clic en **"📥 Importar"**.
2. Se abrirá un modal de importación.
3. Haga clic en **"Seleccionar archivo"** y elija su archivo Excel.
4. El sistema procesará el archivo y mostrará una **previsualización**.

### 5.4 Revisar la Previsualización

El sistema mostrará un resumen:

```
┌─────────────────────────────────────────┐
│  Resumen de Importación                 │
│                                         │
│  Total de filas:     50                 │
│  Válidas:            47 ✅              │
│  Con errores:         3 ❌              │
│                                         │
│  Errores encontrados:                   │
│  - Fila 12: Código de establecimiento   │
│    no encontrado                        │
│  - Fila 28: Campo "mes" vacío           │
│  - Fila 35: Código no asignado a su     │
│    usuario                              │
└─────────────────────────────────────────┘
```

**Acciones posibles:**
- **Corregir el archivo:** Si hay errores, corrija el Excel y vuelva a subirlo.
- **Importar solo las válidas:** Las filas con errores se omitirán.

### 5.5 Confirmar la Importación

1. Revise que el resumen sea correcto.
2. Haga clic en **"Confirmar Importación"**.
3. El sistema insertará las observaciones válidas.
4. Verá un mensaje de confirmación con la cantidad importada.

> **Nota:** Las observaciones importadas quedan en estado **"Pendiente"**, igual que las creadas manualmente.

---

## 6. Reportes y Estadísticas

El módulo de reportes le permite analizar sus observaciones desde diferentes perspectivas.

### 6.1 Acceder a Reportes

1. En el menú lateral, haga clic en **"Reportes"**.
2. Verá una interfaz con **6 pestañas (tabs)** en la parte superior.

### 6.2 Selector de Año

En la esquina superior derecha puede cambiar el año de análisis:
- Seleccione el año deseado en el dropdown.
- Todos los gráficos y tablas se actualizarán automáticamente.

### 6.3 Pestaña: General

Muestra una visión completa de sus observaciones:

| Gráfico | Tipo | Qué muestra |
|---------|------|-------------|
| **Por Mes** | Barras | Cantidad de observaciones por mes |
| **Por Comuna** | Dona | Distribución por comuna |
| **Por Establecimiento** | Barras horizontales | Top 15 establecimientos con más observaciones |
| **Por Serie REM** | Barras | Distribución por serie (A, P, D, etc.) |
| **Por Plazo** | Dona | Dentro de plazo vs. Fuera de plazo |
| **Por Validador** | Dona | Usan validador vs. No usan |

Cada gráfico tiene una **tabla de datos** debajo con totales y porcentajes.

### 6.4 Pestaña: Errores

Muestra observaciones filtradas por **tipo_error = 'ERROR'**:

| Gráfico | Qué muestra |
|---------|-------------|
| **Errores por Mes** | Evolución mensual de errores |
| **Errores por Comuna** | Distribución geográfica de errores |
| **Errores por Establecimiento** | Ranking de establecimientos con más errores |

> **Uso:** Identifique qué establecimientos tienen más errores recurrentes para focalizar su trabajo.

### 6.5 Pestaña: Fuera de Plazo

Muestra observaciones con **plazo_entrega = 'fuera_plazo'**:

| Gráfico | Qué muestra |
|---------|-------------|
| **Fuera de Plazo por Mes** | Cuántos envíos tardíos hubo cada mes |
| **Fuera de Plazo por Comuna** | Distribución por comuna |
| **Fuera de Plazo por Establecimiento** | Establecimientos con más envíos tardíos |

> **Uso:** Identifique patrones de atraso en los envíos REM.

### 6.6 Pestaña: Validador

Muestra observaciones con **usa_validador = 'si'**:

| Gráfico | Qué muestra |
|---------|-------------|
| **Uso Validador por Mes** | Frecuencia mensual de uso del validador |
| **Uso Validador por Comuna** | Penetración del validador por comuna |
| **Uso Validador por Establecimiento** | Establecimientos que más usan el validador |

> **Uso:** Evalúe la adopción del validador REM en los establecimientos.

### 6.7 Pestaña: Serie / Hoja

Análisis detallado por componente del REM:

| Sección | Qué muestra |
|---------|-------------|
| **Por Serie REM × Tipo Error** | Matriz que cruza cada serie con cada tipo de error |
| **Por Hoja REM** | Top 15 hojas más frecuentes con su tipo de error |

> **Uso:** Identifique qué hojas REM generan más observaciones.

### 6.8 Pestaña: PDF Detallado

Genera un reporte jerárquico para impresión:

**Pasos:**
1. Seleccione los filtros deseados:
   - **Comuna:** Todas o una específica
   - **Establecimiento:** Todos o uno específico
   - **Mes:** Todos o uno específico
   - **Estado:** Todos o un estado específico
2. Haga clic en **"📄 Generar PDF Detallado"**.
3. Se descargará un archivo PDF.

**Estructura del PDF:**
```
┌──────────────────────────────────────────────────────────────┐
│  COMUNAS   │ ESTABLECIMIENTOS  │ MES  │ DETALLE │ DET. ERROR│
├──────────────────────────────────────────────────────────────┤
│  OSORNO    │ CESFAM Rahue Alto │ nov  │ SERIE A │ Corregido │
│            │                   │      │ A03 celda...│         │
│            │ CESFAM Pampa...   │ nov  │ SERIE A │ Corregido │
│            │                   │      │ A07 sin...│           │
│            │                   │ dic  │ SERIE A │ Sin resp. │
│            │                   │      │ A29 sin...│           │
├──────────────────────────────────────────────────────────────┤
│  PURRANQUE │ Hospital...       │ nov  │ SERIE A │ Corregido │
└──────────────────────────────────────────────────────────────┘
```

- **Header rojo oscuro** con los nombres de columnas.
- **Agrupamiento visual:** Comuna → Establecimiento → Mes con celdas combinadas.
- **Código de colores:** Verde = Corregido | Naranja = Sin respuesta | Rojo = Rechazado.
- **Formato horizontal** optimizado para impresión.

### 6.9 Exportar Datos

**Desde cualquier sub-reporte:**
- Cada gráfico tiene un botón **"Exportar ↓"** que descarga un Excel con los datos de ese reporte específico.

**Exportación general:**
- Botón **"📊 Excel General"**: Descarga todas sus observaciones del año en formato Excel.
- Botón **"📄 PDF Detallado"**: Genera el reporte jerárquico (mismo que la pestaña PDF Detallado).

---

## 7. Mi Perfil

### 7.1 Acceder al Perfil

1. En el menú lateral, haga clic en **"Mi Perfil"** (sección Configuración).
2. Verá un formulario con sus datos personales.

### 7.2 Editar Datos Personales

Puede modificar:
- **Nombre Completo:** Su nombre como aparece en el sistema.
- **Correo Electrónico:** (si aplica)

Haga clic en **"Guardar Cambios"** para actualizar.

### 7.3 Cambiar Contraseña

1. En la sección "Cambiar Contraseña", complete:
   - **Contraseña Actual:** Su contraseña vigente.
   - **Nueva Contraseña:** Mínimo 6 caracteres.
   - **Confirmar Contraseña:** Repita la nueva contraseña.
2. Haga clic en **"Cambiar Contraseña"**.

> **Recomendación:** Use una contraseña segura con letras, números y caracteres especiales. No comparta su contraseña con nadie.

---

## 8. Preguntas Frecuentes

### 8.1 General

**P: ¿Por qué no veo el botón "Nueva Observación"?**  
R: Probablemente no tiene establecimientos asignados para el año en curso. Contacte a su supervisor para que le asigne establecimientos.

**P: ¿Puedo ver las observaciones de otros registradores?**  
R: No. Cada registrador solo ve sus propias observaciones. Solo el supervisor puede ver todas.

**P: ¿Puedo cambiar el año de las observaciones?**  
R: Use el selector de año en la parte superior del sistema. Esto cambia el año de trabajo, no modifica las observaciones existentes.

### 8.2 Observaciones

**P: ¿Por qué no puedo editar una observación?**  
R: Solo puede editar observaciones en estado "Pendiente". Si ya fue revisada por el supervisor (aprobado, rechazado, error, justificado), no se puede modificar.

**P: ¿Puedo eliminar una observación?**  
R: No. Solo los supervisores pueden eliminar observaciones. Si necesita eliminar una, solicítelo a su supervisor.

**P: ¿Qué pasa si me equivoco al registrar una observación?**  
R: Si está en estado "Pendiente", puede editarla. Si ya fue supervisada, contacte a su supervisor para que la modifique o elimine.

### 8.3 Importación

**P: ¿Qué formato debe tener el archivo de importación?**  
R: Debe ser un archivo Excel (`.xlsx`) con las columnas de la plantilla oficial. No modifique los nombres de las columnas.

**P: ¿Puedo importar observaciones de establecimientos no asignados?**  
R: No. El sistema validará que cada código de establecimiento corresponda a uno de sus asignaciones. Las filas con códigos no asignados se marcarán como error.

**P: ¿Cuántas observaciones puedo importar a la vez?**  
R: No hay un límite estricto, pero se recomienda no superar las 500 filas por archivo para un procesamiento óptimo.

### 8.4 Reportes

**P: ¿Los reportes muestran datos de otros registradores?**  
R: No. Todos los reportes están filtrados automáticamente para mostrar solo sus propias observaciones.

**P: ¿Puedo exportar un reporte específico a Excel?**  
R: Sí. Cada sub-reporte tiene su propio botón de exportación. También puede usar "Excel General" para todas sus observaciones.

**P: ¿El PDF detallado incluye todas mis observaciones?**  
R: Incluye las observaciones que coincidan con los filtros seleccionados (comuna, establecimiento, mes, estado). Si no selecciona filtros, incluye todas.

### 8.5 Problemas Técnicos

**P: El sistema no carga o muestra error.**  
R: 
1. Verifique su conexión a internet.
2. Limpie la caché del navegador (Ctrl + Shift + Delete).
3. Intente con otro navegador (Chrome, Firefox, Edge).
4. Si persiste, contacte al soporte técnico.

**P: No puedo iniciar sesión.**  
R: 
1. Verifique que su usuario y contraseña sean correctos.
2. Asegúrese de seleccionar el año correcto.
3. Si olvidó su contraseña, solicite a su supervisor que la restablezca.

---

## 9. Glosario

| Término | Definición |
|---------|------------|
| **REM** | Resumen Estadístico Mensual. Informe mensual que envían los establecimientos de salud. |
| **Observación** | Registro de una inconsistencia detectada en un envío REM. |
| **Serie REM** | Categoría del REM (A, P, D, BS, BM, ANEXO). Cada serie contiene múltiples hojas. |
| **Hoja REM** | Sub-componente dentro de una serie (ej: A01, A03, P07). |
| **Tipo de Error** | Clasificación de la inconsistencia: ERROR, S/OBSERVACION, REVISAR, F/PLAZO. |
| **Plazo de Entrega** | Indica si el envío REM fue dentro del plazo establecido o fuera de él. |
| **Validador REM** | Herramienta que verifica la consistencia de los datos antes del envío. |
| **Clasificación** | Estado de seguimiento de la observación: "Corregido", "Sin respuesta del Establecimiento", etc. |
| **Supervisor** | Usuario con permisos para revisar, aprobar y gestionar observaciones. |
| **Registrador** | Usuario que registra observaciones solo en establecimientos asignados. |
| **Asignación** | Vínculo entre un registrador y los establecimientos que puede gestionar. |
| **Historial de Estados** | Registro de todos los cambios de estado de una observación. |

---

## Soporte Técnico

Si tiene problemas técnicos o consultas sobre el sistema:

| Contacto | Información |
|----------|-------------|
| **Supervisor del sistema** | Consulte con su supervisor directo |
| **Soporte técnico** | [Datos de contacto del equipo de TI] |
| **Horario de atención** | Lunes a Viernes, 08:00 - 17:00 |

---

*Manual de Usuario — Registrador v2.1.0 — Servicio de Salud Osorno — Mayo 2026*
