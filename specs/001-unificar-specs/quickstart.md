# Quickstart: Sistema Observaciones REM

## Requisitos

- XAMPP (PHP 7.4+, MySQL 5.7+, Apache)
- Navegador moderno (Chrome/Firefox/Edge)

## Instalación

1. Clonar el repositorio en `C:\xampp\htdocs\respaldo_observaciones\`
2. La base de datos ya existe y está poblada en `localhost` (MySQL)
3. Verificar conexión en `config/database.php`
4. Iniciar Apache y MySQL desde XAMPP Control Panel
5. Acceder a `http://localhost/respaldo_observaciones/`

## Usuarios Iniciales (Seed)

| Username | Nombre | Rol | Contraseña |
|----------|--------|-----|------------|
| supervisor1 | Cecilia | Supervisor | admin123 |
| registrador1 | Rodrigo | Registrador | admin123 |
| registrador2 | Victoria | Registrador | admin123 |
| registrador3 | Roxana | Registrador | admin123 |
| registrador4 | Marcelo | Registrador | admin123 |

> Todos los usuarios tienen `password_reset_required = 1`. Al primer inicio de sesión, el sistema forzará el cambio de contraseña.

## Estructura del Proyecto

```text
/
├── index.php                 # Router principal
├── config/database.php       # Conexión PDO
├── views/                    # Vistas PHP (Tabler HTML)
├── api/                      # API REST endpoints
├── models/                   # Capa de datos (PDO)
├── assets/js/                # JavaScript modules
├── assets/css/               # Estilos override
└── uploads/versiones/        # Snapshots
```

## Comandos de Desarrollo

- El sistema no tiene build tools. Editar archivos directamente.
- Los cambios de esquema BD requieren migración SQL manual en `config/`.
- Para crear un snapshot del sistema: Módulo Versionado → Crear Snapshot.

## Mockups de Referencia

Los manuales de usuario con mockups se encuentran en `docs/manuales/` por módulo. Ver tareas de implementación.
