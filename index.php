<?php
/**
 * Punto de entrada del sistema
 * Determina si mostrar login o la aplicación principal
 */

require_once 'config/config.php';
require_once 'config/constants.php';

// Si no hay sesión activa, mostrar login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    include 'views/login.php';
    exit;
}

// Usuario autenticado - mostrar la aplicación
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'observaciones', 'supervision', 'reportes', 'usuarios', 'perfil', 'asignaciones', 'eliminadas', 'establecimientos'];

// Validar que la página existe
if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

// Verificar permisos por rol
$userRole = $_SESSION['rol'] ?? '';
if ($page === 'supervision' && $userRole !== ROL_SUPERVISOR) {
    $page = 'dashboard'; // Redirigir si no tiene permisos
}
if ($page === 'usuarios' && $userRole !== ROL_SUPERVISOR) {
    $page = 'dashboard'; // Solo supervisores pueden gestionar usuarios
}
if ($page === 'asignaciones' && $userRole !== ROL_SUPERVISOR) {
    $page = 'dashboard'; // Solo supervisores pueden gestionar asignaciones
}
if ($page === 'eliminadas' && $userRole !== ROL_SUPERVISOR) {
    $page = 'dashboard'; // Solo supervisores pueden ver eliminadas
}
if ($page === 'establecimientos' && $userRole !== ROL_SUPERVISOR) {
    $page = 'dashboard'; // Solo supervisores pueden gestionar establecimientos
}

// Incluir header
include 'includes/header.php';

// Incluir la vista correspondiente
$viewFile = "views/{$page}.php";
if (file_exists($viewFile)) {
    include $viewFile;
} else {
    echo '<div class="p-6"><h2 class="text-xl font-bold text-slate-800">Página no encontrada</h2></div>';
}

// Incluir footer
include 'includes/footer.php';
