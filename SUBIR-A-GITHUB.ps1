# ============================================================================
# SCRIPT: Subir Sistema de Observaciones REM a GitHub (rama main)
# ============================================================================
# Ejecutar este archivo con PowerShell desde la carpeta del proyecto:
#   C:\xampp\htdocs\www\respaldo_observaciones> .\SUBIR-A-GITHUB.ps1
#
# Advertencias:
#   1. Esto SOBREESCRIBE el contenido de main en GitHub con lo que hay localmente.
#   2. El módulo "Versionado" (api/versioning.php, views/versionado.php,
#      models/Version.php) que existe en GitHub se ELIMINARÁ del remoto.
#   3. NO se suben: config/.env.local.php, vendor/, uploads/, *.bak, logs,
#      porque ya están en .gitignore.
# ============================================================================

$ErrorActionPreference = "Stop"
$repoUrl = "https://github.com/rogarces85/Sistema_ObservacionesV3.git"

Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  SUBIR SISTEMA REM A GITHUB - RAMA MAIN" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Verificar que estamos en la carpeta correcta
if (-not (Test-Path "config\config.php")) {
    Write-Host "ERROR: No se encuentra config\config.php. Ejecuta este script desde:" -ForegroundColor Red
    Write-Host "       C:\xampp\htdocs\www\respaldo_observaciones" -ForegroundColor Red
    exit 1
}

# 2. Configurar usuario de git si no está configurado
$userName = git config --global user.name 2>$null
$userEmail = git config --global user.email 2>$null

if (-not $userName) {
    Write-Host "Git no tiene nombre de usuario configurado." -ForegroundColor Yellow
    $name = Read-Host "Ingresa tu nombre (ej: Juan Perez)"
    git config --global user.name "$name"
}
if (-not $userEmail) {
    Write-Host "Git no tiene correo configurado." -ForegroundColor Yellow
    $email = Read-Host "Ingresa tu correo de GitHub"
    git config --global user.email "$email"
}

Write-Host ""
Write-Host "Usuario de git: $(git config --global user.name) <$(git config --global user.email)>" -ForegroundColor Green

# 3. Inicializar repositorio local si no existe
if (-not (Test-Path ".git")) {
    Write-Host "`nInicializando repositorio git local..." -ForegroundColor Cyan
    git init
} else {
    Write-Host "`nEl repositorio git local ya existe." -ForegroundColor Green
}

# 4. Agregar todos los archivos (respetando .gitignore)
Write-Host "`nAgregando archivos al staging..." -ForegroundColor Cyan
git add .

# 5. Verificar si hay algo para commitear
$status = git status --porcelain
if (-not $status) {
    Write-Host "No hay cambios para commitear. Todo ya está commitado." -ForegroundColor Yellow
} else {
    Write-Host "Creando commit..." -ForegroundColor Cyan
    git commit -m "Copia de respaldo local - Sistema de Observaciones REM (fuente de verdad para main)"
    Write-Host "Commit creado correctamente." -ForegroundColor Green
}

# 6. Agregar remote origin si no existe
$remotes = git remote
if ($remotes -notcontains "origin") {
    Write-Host "`nAgregando remote origin..." -ForegroundColor Cyan
    git remote add origin $repoUrl
} else {
    Write-Host "`nRemote origin ya existe: $(git remote get-url origin)" -ForegroundColor Green
}

# 7. Renombrar rama a main
Write-Host "`nAsegurando rama main..." -ForegroundColor Cyan
git branch -M main

# 8. Push forzado a main (sobrescribe el remoto con lo local)
Write-Host ""
Write-Host "============================================================" -ForegroundColor Yellow
Write-Host "  ADVERTENCIA: Esto empujará (push) todo el contenido local" -ForegroundColor Yellow
Write-Host "  a la rama main de GitHub, SOBRESCRIBIENDO lo que haya remoto." -ForegroundColor Yellow
Write-Host "============================================================" -ForegroundColor Yellow
Write-Host ""
$confirm = Read-Host "¿Deseas continuar? (s/n)"
if ($confirm -ne "s" -and $confirm -ne "S") {
    Write-Host "Operación cancelada por el usuario." -ForegroundColor Red
    exit 0
}

Write-Host "`nEjecutando push a main..." -ForegroundColor Cyan
git push -u origin main --force

Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host "  ¡LISTO! El repositorio está en GitHub:" -ForegroundColor Green
Write-Host "  https://github.com/rogarces85/Sistema_ObservacionesV3" -ForegroundColor Green
Write-Host "  Rama: main" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""
Write-Host "En tu casa podrás clonarlo con:" -ForegroundColor Cyan
Write-Host "  git clone https://github.com/rogarces85/Sistema_ObservacionesV3.git" -ForegroundColor Cyan
Write-Host "  cd Sistema_ObservacionesV3"
Write-Host "  composer install"
Write-Host ""
