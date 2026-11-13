# Subida del Sistema de Observaciones REM a GitHub (rama `main`)

> **Archivo:** `SUBIR-A-GITHUB.md`
> **Propósito:** Documentar el proceso de convertir esta copia local en la fuente de verdad de `main` en GitHub, para poder trabajar desde cualquier máquina (incluida tu casa).
> **Fecha:** 2026-11-13

---

## 1. Contexto

Esta carpeta (`C:\xampp\htdocs\www\respaldo_observaciones`) es una **copia de respaldo local**, no un clone de git. No tiene directorio `.git`, por lo que no se puede hacer `git pull`, `git status` ni `git push` directamente desde aquí.

El objetivo es:
- Convertir esta copia local en el **proyecto madre** en la rama `main` de GitHub.
- Que no existan otras ramas salvo que sea estrictamente necesario.
- Poder clonar este repositorio en tu casa y trabajar desde allí.

---

## 2. Repositorio destino

| Dato | Valor |
|------|-------|
| URL | https://github.com/rogarces85/Sistema_ObservacionesV3 |
| Rama destino | `main` |
| Usuario propietario | `rogarces85` |

---

## 3. Proceso de subida (paso a paso)

### Paso A: Ejecutar el script local

Desde PowerShell, en la carpeta `C:\xampp\htdocs\www\respaldo_observaciones`:

```powershell
.\SUBIR-A-GITHUB.ps1
```

El script hará lo siguiente:

1. **Inicializa** un repositorio git local (`git init`) si no existe.
2. **Agrega** todos los archivos al staging, respetando `.gitignore`.
3. **Crea un commit** con todo el contenido actual.
4. **Agrega** el remote `origin` apuntando a GitHub.
5. **Renombra** la rama local a `main`.
6. **Pide confirmación** antes del push (escribe `s` y presiona Enter).
7. **Ejecuta `git push -u origin main --force`**, sobrescribiendo el contenido remoto con el local.

### Paso B: Credenciales de GitHub

Cuando el script haga el `push`, Git te pedirá:
- **Usuario:** tu usuario de GitHub
- **Contraseña:** un **Personal Access Token (PAT)**, no tu contraseña de cuenta

Si no tienes un PAT, créalo aquí: https://github.com/settings/tokens (scope `repo`).

### Paso C: Clonar en tu casa

Una vez el push termine, desde cualquier otra máquina:

```powershell
git clone https://github.com/rogarces85/Sistema_ObservacionesV3.git
cd Sistema_ObservacionesV3
composer install
```

---

## 4. Qué se sube y qué NO se sube

### ✅ Se sube (código fuente, documentación, configuración)

- `api/`, `models/`, `views/`, `includes/`, `config/` (salvo `.env.local.php`)
- `assets/` (CSS, JS, imágenes, libs)
- `specs/`, `docs/`, `deploy/`, `tools/`
- `02-DOCS/` (wiki del proyecto, rsc-harness)
- `01-TOOLS/` (conexiones a herramientas externas, rsc-harness)
- `.rsc.json`, `AGENTS.md`, `CLAUDE.md`, `README.md`, `CHANGELOG.md`
- `composer.json`, `package-lock.json`
- `.gitignore`
- `SUBIR-A-GITHUB.ps1` (script de utilidad, incluido en el repo para referencia)
- `SUBIR-A-GITHUB.md` (este archivo)

### ❌ NO se sube (ya está en `.gitignore`)

| Ruta | Razón |
|------|-------|
| `config/.env.local.php` | Credenciales de base de datos de producción |
| `vendor/` | Dependencias de Composer (`composer install` las instala en casa) |
| `uploads/` | Archivos de runtime del sistema |
| `*.bak` | Respaldos locales (ej. `views/reportes.php.bak`) |
| `logs/`, `*.log` | Registros del sistema |
| `.rsc/` | Estado interno de máquina de rsc-harness (opcional; si se quiere compartir el harness, subir `.rsc.json`, `02-DOCS/`, `01-TOOLS/`, `AGENTS.md`) |

---

## 5. Diferencias conocidas: copia local vs GitHub (antes del push)

| Elemento | Local (`respaldo_observaciones`) | GitHub (`main`, antes del push) |
|----------|----------------------------------|---------------------------------|
| Módulo Versionado | **Eliminado** (`api/versioning.php`, `views/versionado.php`, `models/Version.php` no existen) | **Presente** |
| `views/reportes.php.bak` | Existe | No existe |
| Rediseño reportes (matrices Plazo/Validador) | Presente | No presente (commit `7c60567` no incluye todo el rediseño local) |

> **⚠️ Advertencia:** Al hacer `git push --force` a `main`, el contenido local **sobrescribe completamente** lo que hay en GitHub. El módulo Versionado y cualquier otro archivo que exista en GitHub pero no localmente se eliminarán del remoto. Esto es intencional: esta copia local se convierte en el proyecto madre.

---

## 6. Flujo de trabajo recomendado desde casa

Una vez clonado el repositorio:

1. **Instalar dependencias:**
   ```powershell
   composer install
   ```

2. **Configurar credenciales locales** (crear `config/.env.local.php`, nunca subirlo):
   ```php
   <?php
   return [
       'db_host' => 'localhost',
       'db_name' => 'observaciones_rem',
       'db_user' => 'tu_usuario',
       'db_pass' => 'tu_contraseña',
       'REM_ENVIRONMENT' => 'development',
   ];
   ```

3. **Aplicar esquema de BD:** ejecutar los archivos SQL en orden (ver `README.md` sección "Instalación Local Con XAMPP"). **No usar `deploy/migrate.sh` para fresh install** (está roto).

4. **Crear rama para cambios:** como el workflow es `branches`, no hacer commits directos a `main`:
   ```powershell
   git checkout -b feature/mi-cambio
   ```

5. **Verificar sintaxis PHP antes de commitear:**
   ```powershell
   Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
   ```

6. **Subir cambios a GitHub:**
   ```powershell
   git add .
   git commit -m "descripción del cambio"
   git push origin feature/mi-cambio
   ```

---

## 7. Advertencias importantes

1. **Datos reales en producción.** El sistema está en producción con datos reales. Cualquier operación sobre la base de datos requiere respaldo verificado previo. No hay herramienta de migraciones ni suite de pruebas automatizada.
2. **`config/.env.local.php` nunca se sube a GitHub.** Contiene credenciales sensibles.
3. **`vendor/` no se sube.** Se instala con `composer install` en cada máquina.
4. **CSRF obligatorio** en todo request mutante desde el navegador.
5. **No hacer commits directos a `main`** salvo que se desbloquee explícitamente (`rsc main unlock`).
6. **`tools/seed_demo.php` escribe en la BD.** Solo desarrollo, y su ruta de `require_once` está rota (falta `..`).
7. **`deploy/migrate.sh` está roto.** No usarlo para instalaciones nuevas.

---

## 8. Estructura de documentación en el repositorio

```
02-DOCS/wiki/
├── index.md                          # Tabla de contenidos
├── sistema/resumen.md                # Resumen completo del sistema
├── sdd/constitution.md               # Constitución (reglas no negociables)
└── harness/
    ├── user-profile.md               # Perfil de usuario rsc
    ├── installation-plan.md          # Plan de rsc aceptado
    └── decisions.md                  # Decisiones del harness
```

---

## 9. Comandos rápidos de referencia

| Acción | Comando |
|--------|---------|
| Clonar en casa | `git clone https://github.com/rogarces85/Sistema_ObservacionesV3.git` |
| Instalar PHP deps | `composer install` |
| Lint PHP | `Get-ChildItem -Recurse -Filter *.php \| ForEach-Object { php -l $_.FullName }` |
| Salud de rsc | `npx @ericrisco/rsc doctor` |
| Reparar rsc | `npx @ericrisco/rsc repair` |
| Listar skills rsc | `npx @ericrisco/rsc list` |

---

*Este archivo queda en el repositorio como referencia permanente del proceso de subida y flujo de trabajo.*
