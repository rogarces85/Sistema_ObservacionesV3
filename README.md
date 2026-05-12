# Sistema de Observaciones REM

Sistema de gestión de observaciones del Resumen Estadístico Mensual (REM) para el Servicio de Salud Osorno.

**Versión:** 2.2.0 — **Última actualización:** Mayo 2026

## Tecnologías

| Categoría | Tecnologías |
|-----------|-------------|
| **Backend** | PHP 7.4+, PDO MySQL |
| **Base de Datos** | MySQL 5.7+ |
| **Frontend** | HTML5, CSS3, JavaScript ES6+, Chart.js 4.4 |
| **Librerías** | PhpSpreadsheet 5.4, TCPDF 6.10 |
| **Servidor** | Apache (XAMPP) |

## Estructura del Proyecto

```
ObservacionesREM_V2/
├── api/                           # Endpoints REST
│   ├── auth.php                   # Autenticación, login, logout, check sesión
│   ├── observations.php           # CRUD de observaciones
│   ├── reports.php                # Datos agregados para reportes (20+ dimensiones)
│   ├── export.php                 # Exportación Excel/PDF/CSV
│   ├── locations.php              # Comunas y establecimientos
│   ├── import.php                 # Importación masiva Excel
│   ├── import_template.php        # Generación plantilla Excel
│   ├── supervision.php            # Aprobación/rechazo observaciones
│   ├── users.php                  # Gestión de usuarios
│   ├── assignments.php            # Asignación de establecimientos
│   └── deleted.php                # Papelera de observaciones eliminadas
├── assets/
│   ├── css/
│   │   └── styles.css             # Estilos globales (BEM) + tabs de reportes
│   └── js/
│       ├── app.js                 # Lógica principal (fetchAPI, modals, logout)
│       ├── charts.js              # Gráficos dashboard
│       └── notifications.js       # Sistema de notificaciones toast
├── config/
│   ├── config.php                 # Configuración BD y sesión
│   ├── constants.php              # Constantes (estados, roles, series, hojas)
│   ├── init_db.sql                # Script inicialización DB
│   ├── migration_2026_02_06.sql   # Migración de campos
│   ├── migration_2026_05_08_reportes.sql  # Índices de optimización reportes
│   ├── migration_2026_05_08_limpieza_comunas.sql  # Limpieza comunas
│   ├── create_asignaciones_table.sql
│   └── update_establecimientos.sql
├── includes/                      # Componentes reutilizables
│   ├── header.php                 # Header con navegación y CSRF meta
│   ├── footer.php                 # Footer y scripts JS
│   ├── sidebar.php                # Menú lateral con grupos
│   └── csrf.php                   # Protección CSRF
├── models/                        # Modelos de datos
│   ├── Database.php               # Conexión PDO singleton
│   ├── User.php                   # Modelo usuario
│   ├── Observation.php            # Modelo observaciones (32 métodos)
│   ├── Location.php               # Modelo ubicaciones
│   ├── Exporter.php               # Exportación Excel/PDF/CSV + PDF detallado
│   ├── EstablecimientoAsignacion.php  # Asignaciones
│   └── DeletedObservation.php     # Papelera soft-delete
├── views/                         # Vistas del sistema
│   ├── login.php                  # Página inicio sesión
│   ├── dashboard.php              # Panel de control con alertas
│   ├── observaciones.php          # Lista y gestión observaciones
│   ├── supervision.php            # Panel supervisión (supervisor)
│   ├── reportes.php               # Reportes con tabs y 6 vistas
│   ├── usuarios.php               # Gestión usuarios (supervisor)
│   ├── perfil.php                 # Perfil usuario
│   ├── asignaciones.php           # Asignar establecimientos (supervisor)
│   ├── eliminadas.php             # Papelera (supervisor)
│   └── establecimientos.php       # Gestión establecimientos y referentes (supervisor)
├── uploads/                       # Archivos importados (gitignored)
├── vendor/                        # Dependencias PHP (Composer)
├── index.php                      # Punto de entrada / router
├── composer.json                  # Dependencias PHP
├── .gitignore
└── README.md                      # Documentación
```

## Funcionalidades

### Gestión de Observaciones
- Crear, editar y eliminar observaciones REM
- Campos: clasificación, detalle error, establecimiento, fecha, serie, hoja, tipo de error, plazo de entrega, uso de validador
- Importación masiva desde archivo Excel (.xlsx) con validación previa (preview → confirm)
- Filtros por estado, mes, establecimiento y búsqueda de texto
- Exportación a Excel, PDF y CSV
- Historial de cambios de estado por observación
- Papelera de eliminadas con restauración (soft-delete)

### Roles de Usuario
- **Supervisor**: Panel exclusivo, aprobar/rechazar observaciones, gestionar usuarios, asignar establecimientos, gestionar referentes, ver todas las observaciones, ver eliminadas
- **Registrador**: Crear y editar observaciones propias, ver solo sus observaciones, restringido a establecimientos asignados

### Estados de Observación
| Estado | Descripción |
|--------|-------------|
| pendiente | Aguardando revisión del supervisor |
| aprobado | Revisado y aprobado |
| rechazado | No aprobado (requiere justificación) |
| justificado | Rechazo justificado por registrador |
| error | Requiere corrección |

### Dashboard
- Estadísticas en tiempo real (contadores por estado)
- Gráficos interactivos (Chart.js): distribución por estado, mes y tipo de error
- Lista de observaciones recientes
- Alertas de asignación (supervisor: registradores sin establecimientos; registrador: sin asignaciones)
- Visibilidad según rol (registrador solo ve las suyas)

### Sistema de Reportes (v2.1)
Módulo de reportes completamente renovado con interfaz de tabs:

| Tab | Contenido |
|-----|-----------|
| **General** | 6 gráficos: Mes, Comuna, Establecimiento, Serie, Plazo, Validador |
| **Errores** | 3 gráficos filtrados por tipo_error='ERROR': Mes, Comuna, Establecimiento |
| **Fuera de Plazo** | 3 gráficos filtrados por plazo_entrega='fuera_plazo': Mes, Comuna, Establecimiento |
| **Validador** | 3 gráficos filtrados por usa_validador='si': Mes, Comuna, Establecimiento |
| **Serie / Hoja** | Matriz Serie×Tipo Error + Top 15 Hojas REM más frecuentes |
| **PDF Detallado** | Generador de PDF jerárquico (Comuna→Establecimiento→Mes) con filtros |

**Exportación desde reportes:**
- Cada sub-reporte tiene botón individual de exportación a Excel
- Botón global "Excel General" para exportación completa
- Botón "PDF Detallado" para reporte jerárquico con agrupamiento visual y código de colores por estado

### Asignación de Establecimientos
- Asignación anual de establecimientos a registradores
- Copiar asignaciones del año anterior
- Validación de unicidad (un establecimiento = un registrador por año)
- Remoción individual de asignaciones

### Gestión de Establecimientos y Referentes
- Vista exclusiva para supervisores
- Listado de establecimientos con datos de contacto
- CRUD de referentes por establecimiento (cargo, nombre, teléfono, email)

### Seguridad
- Autenticación con sesiones PHP
- Permisos basados en roles (supervisor/registrador)
- Protección CSRF en formularios y APIs
- Contraseñas hasheadas (password_hash bcrypt)
- Consultas preparadas (PDO)
- Validación backend de asignaciones (403 Forbidden si intenta usar establecimiento no asignado)
- Ruta API dinámica calculada desde el cliente (evita errores 404 por hardcodeo)

## Instalación

### 1. Requisitos
- PHP >= 7.4
- MySQL >= 5.7
- Apache con mod_rewrite
- XAMPP

### 2. Configurar base de datos
Editar `config/config.php` con credenciales MySQL.

### 3. Ejecutar scripts de inicialización
```bash
mysql -h localhost -u root -p < config/init_db.sql
mysql -h localhost -u root -p observaciones_rem < config/migration_2026_05_08_reportes.sql
mysql -h localhost -u root -p observaciones_rem < config/migration_2026_05_08_limpieza_comunas.sql
```

### 4. Instalar dependencias PHP
```bash
composer install
```

### 5. Acceder al sistema
`http://localhost/ObservacionesREM_V2/`

## Usuarios del Sistema

### Supervisor

| Usuario | Nombre | Contraseña |
|---------|--------|------------|
| supervisor1 | Cecilia (Supervisor) | admin123 |

### Registradores

| Usuario | Nombre | Contraseña |
|---------|--------|------------|
| registrador1 | Rodrigo Garcés | admin123 |
| registrador2 | Victoria Martínez | admin123 |
| registrador3 | Roxana Mancilla | admin123 |
| registrador4 | Marcelo Horstmeier | admin123 |

## Manejo de Versiones y Despliegue

### Control de versiones con Git

El sistema usa Git para trackear cambios. Cada modificación debe seguir este flujo:

#### 1. Verificar cambios realizados
```bash
git status          # Archivos modificados
git diff            # Diferencias en detalle
```

#### 2. Preparar el commit
```bash
git add .           # Agregar todos los cambios
# o agregar archivos específicos:
git add assets/js/app.js api/observations.php
```

#### 3. Crear commit con mensaje descriptivo
```bash
git commit -m "tipo: descripción corta del cambio"
```

**Convención de mensajes:**
| Prefijo | Uso |
|---------|-----|
| `feat:` | Nueva funcionalidad |
| `fix:` | Corrección de bug |
| `refactor:` | Refactorización sin cambio funcional |
| `style:` | Cambos de estilo visual (CSS, formato) |
| `docs:` | Actualización de documentación |
| `chore:` | Tareas de mantenimiento, limpieza |
| `perf:` | Mejora de rendimiento |

**Ejemplos:**
```bash
git commit -m "fix: corrección de ruta API dinámica en logout"
git commit -m "feat: agregada vista de gestión de referentes"
git commit -m "docs: actualización de README con usuarios del sistema"
```

#### 4. Etiquetar versiones (releases)
```bash
git tag -a v2.3.0 -m "Versión 2.3.0 - descripción del release"
```

#### 5. Push al repositorio remoto
```bash
git push origin main          # Subir commits
git push origin v2.3.0        # Subir tag de versión
```

### Despliegue a producción

#### Opción A: Copia directa por FTP/SMB
1. Excluir del upload: `vendor/`, `uploads/`, `.git/`
2. Subir solo los archivos modificados al servidor
3. Ejecutar `composer install --no-dev` en el servidor si hay nuevas dependencias
4. Ejecutar migraciones SQL nuevas si las hay:
   ```bash
   mysql -h <host> -u <user> -p observaciones_rem < config/migration_XXXX.sql
   ```

#### Opción B: Git pull en servidor
1. Conectar al servidor por SSH
2. Ir al directorio del proyecto:
   ```bash
   cd /ruta/al/ObservacionesREM_V2
   ```
3. Actualizar código:
   ```bash
   git pull origin main
   ```
4. Instalar dependencias:
   ```bash
   composer install --no-dev
   ```
5. Ejecutar migraciones pendientes si existen

#### Opción C: Generar paquete de despliegue
```bash
# Crear ZIP sin archivos innecesarios
git archive --format=zip --output=ObservacionesREM_V2_v2.3.0.zip main
```

### Rollback (volver a versión anterior)
```bash
git log --oneline                    # Ver historial de commits
git reset --hard <hash-commit>       # Volver a commit específico
git push --force origin main         # Forzar push (con precaución)
```

O por tag de versión:
```bash
git checkout v2.2.0                  # Ir a versión 2.2.0
```

### Buenas prácticas
- **Nunca** commitear archivos con credenciales reales (usar `.gitignore`)
- Hacer commit frecuente con mensajes claros y específicos
- Etiquetar cada versión que se despliegue a producción
- Mantener backup de la base de datos antes de aplicar migraciones
- Probar en entorno de desarrollo antes de desplegar a producción

## Sistema de Diseño

### Paleta de Colores
```css
--color-primary: #17a2b8;     /* Teal */
--color-secondary: #1e3a5f;   /* Navy */
--color-accent-orange: #fd7e14;
--color-accent-coral: #dc3545;
--color-accent-teal: #20c997;
```

### Metodología BEM
```css
.sidebar { }                      /* Block */
.sidebar__nav-link { }            /* Element */
.sidebar__nav-link--active { }    /* Modifier */
```

## Series REM Soportadas

| Serie | Hojas |
|-------|-------|
| SERIE A | A01, A02, A03, A04, A05, A06, A07, A09, A11, A11a, A19a, A19b, A21, A23, A24, A25, A26, A27, A29, A30ar, A31, A32, A33, Hoja Control, Renombre archivo |
| SERIE BS | B, B17, Hoja Control, Renombre archivo |
| SERIE BM | BM18, BM18a, Hoja Control, Renombre archivo |
| SERIE P | P01, P02, P03, P04, P05, P06, P07, P09, P11, P12, P13, Hoja Control, Renombre archivo |
| SERIE ANEXO | Hoja Parto_RN, Hoja S_Infancia, Hoja I.T.S, Hoja Rechazos, Hoja Farmacia, Hoja S_Mental, Hoja S_Adolescencia, Hoja Laboratorio, Hoja Intercultural, Hoja S_Familiar, Hoja Control, Renombre archivo |
| SERIE D | D15, D16, Hoja Control, Renombre archivo |

## Tipos de Error

- S/OBSERVACION
- ERROR
- REVISAR
- F/PLAZO

## Historial de Versiones

### v2.2.0 — Mayo 2026
- **Limpieza:** Eliminación de 24 archivos de desarrollo/testing/one-time scripts
- **Bugfix logout:** Corrección de ruta API dinámica (evita 404 en entornos con ruta distinta)
- **Nueva vista:** Gestión de establecimientos y referentes (supervisor)
- **Mejora:** API_BASE calculada dinámicamente desde `window.location.pathname`

### v2.1.0 — Mayo 2026
- **Reportes:** Interfaz tabbed con 6 vistas (General, Errores, Fuera de Plazo, Validador, Serie/Hoja, PDF Detallado)
- **PDF Detallado:** Reporte jerárquico Comuna→Establecimiento→Mes con rowspan, código de colores por estado, header rojo oscuro
- **Nuevos reportes:** 15 dimensiones adicionales (errores/fuera_plazo/validador × mes/comuna/establecimiento + serie_detalle + hoja_detalle)
- **Exportación:** Botones individuales por sub-reporte + exportación específica por tipo
- **Rendimiento:** 6 índices compuestos nuevos para optimizar consultas de reportes
- **Bugfix:** Corrección de sesión en api/export.php (ini_set conflict)

### v2.0.0
- Sistema base con CRUD completo, supervisión, asignaciones, importación masiva

## Solución de Problemas

### Error de conexión
1. Verificar MySQL ejecutándose
2. Revisar credenciales en `config/config.php`

### Página en blanco
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### "No autenticado" al exportar
- Causa: `session_start()` antes de `config.php` en API. Ya corregido en v2.1.

### Error 404 en logout o APIs
- Causa: Ruta API hardcodeada. Corregido en v2.2 con cálculo dinámico desde `window.location.pathname`.

## Licencia

Sistema desarrollado para el Servicio de Salud Osorno.
