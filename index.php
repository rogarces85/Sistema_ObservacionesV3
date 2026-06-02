<?php
/**
 * Router principal - Sistema de Observaciones REM
 * Verifica autenticación, enruta páginas y valida permisos
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';

// Si no hay sesión activa, mostrar login
if (!isset($_SESSION['usuario_id']) || $_SESSION['autenticado'] !== true) {
    require_once __DIR__ . '/views/auth/login.php';
    exit;
}

// Usuario autenticado - determinar página
$pagina = $_GET['pagina'] ?? 'dashboard';
$paginasPermitidas = [
    'dashboard',
    'observaciones',
    'supervision',
    'reportes',
    'usuarios',
    'perfil',
    'asignaciones',
    'eliminadas',
    'establecimientos',
    'importacion',
    'versionado'
];

// Validar que la página existe en la lista permitida
if (!in_array($pagina, $paginasPermitidas)) {
    $pagina = 'dashboard';
}

// Verificar permisos por rol
$rolUsuario = $_SESSION['rol'] ?? '';

if ($pagina === 'supervision' && $rolUsuario !== ROL_SUPERVISOR) {
    $pagina = 'dashboard';
}
if ($pagina === 'usuarios' && $rolUsuario !== ROL_SUPERVISOR) {
    $pagina = 'dashboard';
}
if ($pagina === 'asignaciones' && $rolUsuario !== ROL_SUPERVISOR) {
    $pagina = 'dashboard';
}
if ($pagina === 'eliminadas' && $rolUsuario !== ROL_SUPERVISOR) {
    $pagina = 'dashboard';
}
if ($pagina === 'establecimientos' && $rolUsuario !== ROL_SUPERVISOR) {
    $pagina = 'dashboard';
}
if ($pagina === 'importacion' && $rolUsuario !== ROL_REGISTRADOR) {
    $pagina = 'dashboard';
}
if ($pagina === 'versionado' && $rolUsuario !== ROL_SUPERVISOR) {
    $pagina = 'dashboard';
}

// Incluir header
require_once __DIR__ . '/includes/header.php';

// Incluir la vista correspondiente
$archivoVista = __DIR__ . "/views/{$pagina}.php";
if ($pagina === 'eliminadas') {
    $archivoVista = __DIR__ . '/views/papelera.php';
}
if (file_exists($archivoVista)) {
    require_once $archivoVista;
} else {
    echo '<div class="p-6"><h2 class="text-xl font-bold text-slate-800">Página no encontrada</h2></div>';
}

// Incluir footer
require_once __DIR__ . '/includes/footer.php';
