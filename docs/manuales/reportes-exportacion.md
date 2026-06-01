# Manual de Usuario: Reportes y Exportación

## Descripción

El módulo de **Reportes y Exportación** permite generar reportes de las observaciones REM en diferentes formatos (Excel, PDF, CSV), visualizar una vista previa paginada de los datos filtrados y generar informes de errores trimestrales/anuales (solo supervisores).

---

## Acceso

1. Iniciar sesión en el Sistema de Observaciones REM
2. En el menú lateral, hacer clic en **Reportes**
3. Se mostrará la página principal con filtros, opciones de exportación y vista previa

---

## Filtros

La sección de filtros permite refinar los datos que se mostrarán y exportarán:

| Filtro | Descripción |
|--------|-------------|
| **Año** | Año de las observaciones (2020 en adelante) |
| **Mes** | Mes específico o todos los meses |
| **Estado** | Estado actual: Pendiente, Aprobado, Error, Rechazado |
| **Comuna** | Filtrar por comuna del establecimiento |
| **Establecimiento** | Se activa al seleccionar una comuna. Muestra solo establecimientos de esa comuna |
| **Tipo Error** | ERROR, S/Observación, Revisar, Fuera de Plazo |

### Botones

- **Aplicar Filtros**: Ejecuta la búsqueda con los filtros seleccionados
- **Limpiar**: Restablece todos los filtros a sus valores por defecto

---

## Exportación de Datos

### Formatos disponibles

| Formato | Extensión | Descripción |
|---------|-----------|-------------|
| **Excel** | `.xlsx` | Hoja de cálculo con formato, encabezados con color y bordes |
| **PDF** | `.pdf` | Documento con tabla formateada, colores por estado |
| **CSV** | `.csv` | Archivo de texto con delimitador punto y coma (`;`), codificación BOM UTF-8 |

### Tipos de Reporte

| Tipo | Descripción |
|------|-------------|
| **General** | Tabla plana con todas las columnas de las observaciones |
| **Detallado** | PDF jerárquico agrupado por Comuna → Establecimiento → Mes, con rowspan y colores por estado |

### Exportación Híbrida

El sistema utiliza un enfoque híbrido según la cantidad de registros:

| Cantidad | Comportamiento |
|----------|----------------|
| **≤ 1,000 registros** | Exportación sincrónica inmediata (descarga directa) |
| **> 1,000 registros** | Exportación asíncrona (se encola y procesa en segundo plano) |
| **> 50,000 registros** | No permitido. Refine los filtros para reducir la cantidad |

### Indicador de Conteo

Al aplicar filtros, el sistema muestra:
- **Verde**: Exportación inmediata (≤ 1,000 registros)
- **Amarillo**: Se procesará en cola asíncrona (> 1,000 registros)
- **Rojo**: Excede el límite máximo (> 50,000 registros)

---

## Vista Previa

La tabla de vista previa muestra los primeros 20 registros de los datos filtrados:

- **Paginación**: 20 registros por página
- **Columnas**: ID, Año, Mes, Comuna, Establecimiento, Serie, Hoja, Tipo, Detalle, Plazo, Estado, Clasificación
- **Badges de estado**: Colores diferenciados para cada estado
- **Navegación**: Botones de paginación con información de rango

---

## Informe de Errores REM (Solo Supervisor)

Los usuarios con rol **Supervisor** tienen acceso adicional al generador de informes de errores.

### Configuración

| Parámetro | Opciones |
|-----------|----------|
| **Tipo** | Trimestral o Anual |
| **Trimestre** | 1° (Ene-Mar), 2° (Abr-Jun), 3° (Jul-Sep), 4° (Oct-Dic) |
| **Año** | Año del informe |
| **Formato** | JSON (vista web) o PDF (descarga) |

### Contenido del Informe

El informe incluye:
1. **Encabezado institucional**: Logo, nombre del servicio, departamento
2. **Período**: Trimestre o año seleccionado
3. **Resumen por Comuna**: Cantidad de errores por comuna
4. **Resumen por Establecimiento**: Top 20 establecimientos con más errores
5. **Detalle de Errores**: Tabla jerárquica con Comuna → Establecimiento → Mes
6. **Firma**: Espacio para firma de la Jefa de Subdepartamento

### Colores por Estado en PDF

| Estado | Color |
|--------|-------|
| Aprobado | Verde claro (#F0FDF4) |
| Pendiente | Amarillo claro (#FFFBEB) |
| Rechazado | Rojo claro (#FEF2F2) |
| Error | Rosa claro (#FCE4EC) |
| Sin respuesta | Azul claro (observaciones con respuesta del establecimiento) |

---

## Mockup de la Vista

```
┌─────────────────────────────────────────────────────────────────────┐
│  Reportes y Exportación                                             │
├─────────────────────────────────────────────────────────────────────┤
│  Filtros                                                            │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────────┐ ┌──────────┐    │
│  │ Año  │ │ Mes  │ │Estado│ │Comuna│ │Establecim│ │Tipo Error│    │
│  └──────┘ └──────┘ └──────┘ └──────┘ └──────────┘ └──────────┘    │
│  [Aplicar Filtros] [Limpiar]                                        │
├─────────────────────────────────────────────────────────────────────┤
│  Exportar Datos                                                     │
│  ┌──────────┐ ┌──────────────┐  1,234 registros - Cola asíncrona   │
│  │ Formato  │ │Tipo Reporte  │  [Exportar]                         │
│  └──────────┘ └──────────────┘                                     │
├─────────────────────────────────────────────────────────────────────┤
│  Informe de Errores REM (Solo Supervisor)                           │
│  ┌──────┐ ┌──────────┐ ┌──────┐ ┌──────────┐                       │
│  │Tipo  │ │Trimestre │ │ Año  │ │ Formato  │  [Generar Informe]    │
│  └──────┘ └──────────┘ └──────┘ └──────────┘                       │
├─────────────────────────────────────────────────────────────────────┤
│  Vista Previa de Observaciones                          1,234 reg.  │
│  ┌────┬─────┬───────┬────────┬──────────┬───────┬──────┬──────┐   │
│  │ ID │ Año │  Mes  │ Comuna │ Establec.│ Serie │ Hoja │Tipo  │   │
│  ├────┼─────┼───────┼────────┼──────────┼───────┼──────┼──────┤   │
│  │ 1  │2024 │Enero  │Osorno  │CESFAM... │ SERIE │ A01  │ERROR │   │
│  │ 2  │2024 │Enero  │Osorno  │Hospital..│ SERIE │ B17  │REVIS │   │
│  └────┴─────┴───────┴────────┴──────────┴───────┴──────┴──────┘   │
│  Mostrando 1-20 de 1,234     « 1 2 3 ... 62 »                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Preguntas Frecuentes

### ¿Por qué mi exportación tarda tanto?
Si hay más de 1,000 registros, el reporte se procesa en segundo plano. Recibirá una notificación cuando esté listo.

### ¿Puedo exportar más de 50,000 registros?
No. El límite máximo es 50,000 registros para evitar sobrecarga del servidor. Refine los filtros (año, mes, estado) para reducir la cantidad.

### ¿Qué diferencia hay entre reporte General y Detallado?
- **General**: Tabla plana con todas las columnas, ideal para análisis en Excel
- **Detallado**: PDF jerárquico con agrupación visual por Comuna → Establecimiento → Mes, ideal para presentación

### ¿Quién puede generar informes de errores?
Solo los usuarios con rol **Supervisor** pueden acceder al generador de informes de errores REM.

### ¿El CSV es compatible con Excel?
Sí. El CSV incluye BOM UTF-8 y usa punto y coma (`;`) como delimitador, lo que permite abrirlo directamente en Excel con acentos y ñ correctos.
