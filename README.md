# Sistema de Observaciones REM

Sistema de gestión de observaciones del Resumen Estadístico Mensual (REM) para el Servicio de Salud Osorno.

## Tecnologías

| Categoría | Tecnologías |
|-----------|-------------|
| **Backend** | PHP 7.4+, PDO MySQL |
| **Base de Datos** | MySQL 5.7+ |
| **Frontend** | HTML5, CSS3, JavaScript ES6+ |
| **Librerías** | PhpSpreadsheet, TCPDF |
| **Servidor** | Apache (XAMPP) |

## Estructura del Proyecto

```
ObservacionesREM_V2/
├── api/                           # Endpoints REST
│   ├── auth.php                   # Autenticación y login
│   ├── observations.php           # CRUD de observaciones
│   ├── locations.php              # Comunas y establecimientos
│   ├── import.php                 # Importación masiva Excel
│   ├── import_template.php        # Generación plantilla Excel
│   ├── supervision.php            # Aprobación/rechazo observaciones
│   ├── users.php                  # Gestión de usuarios
│   └── export.php                 # Exportación datos
├── assets/
│   ├── css/
│   │   └── styles.css             # Estilos globales (BEM)
│   └── js/
│       ├── app.js                 # Lógica principal
│       ├── charts.js              # Gráficos dashboard
│       └── notifications.js       # Sistema de notificaciones
├── config/
│   ├── config.php                 # Configuración BD
│   ├── constants.php              # Constantes del sistema
│   ├── init_db.sql                # Script inicialización DB
│   ├── migration_2026_02_06.sql   # Migración de datos
│   └── update_establecimientos.sql
├── includes/                      # Componentes reutilizables
│   ├── header.php                 # Header con navegación
│   ├── footer.php                 # Footer y scripts JS
│   ├── sidebar.php                # Menú lateral
│   └── csrf.php                   # Protección CSRF
├── models/                        # Modelos de datos
│   ├── Database.php               # Conexión PDO
│   ├── User.php                   # Modelo usuario
│   ├── Observation.php            # Modelo observaciones
│   ├── Location.php               # Modelo ubicaciones
│   └── Exporter.php               # Exportación datos
├── views/                         # Vistas del sistema
│   ├── login.php                  # Página inicio sesión
│   ├── dashboard.php              # Panel de control
│   ├── observaciones.php          # Lista y gestión observaciones
│   ├── supervision.php            # Panel supervisión (supervisor)
│   ├── reportes.php               # Reportes y exportación
│   ├── usuarios.php               # Gestión usuarios (supervisor)
│   └── perfil.php                 # Perfil usuario
├── controllers/                   # (Reservado para futuros controladores)
├── uploads/                       # (Reservado para archivos importados)
├── vendor/                        # Dependencias PHP (Composer)
├── index.php                      # Punto de entrada
├── db_check.php                   # Verificación conexión BD
├── composer.json                  # Dependencias PHP
├── .gitignore
└── README.md                      # Documentación
```

## Funcionalidades

### Gestión de Observaciones
- Crear, editar y eliminar observaciones REM
- Campos: clasificación, detalle error, establecimiento, fecha, serie, hoja, tipo de error, plazo de entrega, uso de validador
- Importación masiva desde archivo Excel (.xlsx) con validación previa
- Filtros por estado, mes, establecimiento y búsqueda de texto
- Exportación a Excel
- Historial de cambios de estado por observación

### Roles de Usuario
- **Supervisor**: Panel exclusivo, aprobar/rechazar observaciones, gestionar usuarios, ver todas las observaciones
- **Registrador**: Crear y editar observaciones propias, ver solo sus observaciones

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
- Visibilidad según rol (registrador solo ve las suyas)

### Seguridad
- Autenticación con sesiones PHP
- Permisos basados en roles (supervisor/registrador)
- Protección CSRF
- Contraseñas hasheadas (password_hash bcrypt)
- Consultas preparadas (PDO)

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
```

### 4. Instalar dependencias PHP
```bash
composer install
```

### 5. Acceder al sistema
`http://localhost/ObservacionesREM_V2/`

## Usuarios de Prueba

| Usuario | Rol | Contraseña |
|---------|-----|------------|
| supervisor1 | Supervisor | admin123 |
| registrador1 | Registrador | admin123 |

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

## Solución de Problemas

### Error de conexión
1. Verificar MySQL ejecutándose
2. Revisar credenciales en `config/config.php`

### Página en blanco
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Licencia

Sistema desarrollado para el Servicio de Salud Osorno.
