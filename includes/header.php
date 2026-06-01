<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/icons.php';

$currentPage = $_GET['page'] ?? 'dashboard';
$currentYear = $_SESSION['year'] ?? date('Y');
$userName = $_SESSION['nombre_completo'] ?? 'Usuario';
$userRole = $_SESSION['rol'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));

$pageTitles = [
    'dashboard' => 'Panel de Control',
    'observaciones' => 'Observaciones',
    'supervision' => 'Supervisión',
    'reportes' => 'Reportes',
    'usuarios' => 'Gestión de Usuarios',
    'asignaciones' => 'Asignación de Establecimientos',
    'eliminadas' => 'Observaciones Eliminadas',
    'perfil' => 'Mi Perfil',
    'importacion' => 'Importar desde Excel'
];
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?php require_once 'includes/csrf.php'; echo CSRF::generateToken(); ?>">
    <meta name="description" content="Sistema de gestión de observaciones REM para el Servicio de Salud Osorno">
    <title><?php echo APP_NAME; ?> - <?php echo $pageTitles[$currentPage] ?? 'Panel de Control'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="assets/css/tabler-override.css">
</head>
<body>
    <div class="page">
        <?php include 'includes/sidebar.php'; ?>

        <div class="page-wrapper">
            <header class="navbar navbar-expand-md d-print-none">
                <div class="container-xl">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbar-menu" aria-controls="navbar-menu"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="navbar-brand navbar-brand-autodark d-md-none">
                        <a href="?page=dashboard" class="text-white text-decoration-none fs-5 fw-bold">Sistema REM</a>
                    </div>
                    <div class="navbar-nav flex-row order-md-last ms-auto">
                        <div class="nav-item dropdown d-none d-md-flex me-2">
                            <a href="#" class="nav-link px-2" data-bs-toggle="dropdown" aria-label="Ver notificaciones">
                                <?php echo tablerIcon('bell'); ?>
                            </a>
                        </div>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link d-flex align-items-center px-2" data-bs-toggle="dropdown" aria-label="Menú de usuario">
                                <span class="avatar avatar-sm" style="background:#0ea5e9"><?php echo $userInitials; ?></span>
                                <span class="ms-2 d-none d-md-inline">
                                    <span class="fw-semibold"><?php echo htmlspecialchars($userName); ?></span>
                                    <small class="d-block text-secondary"><?php echo htmlspecialchars(ucfirst($userRole)); ?></small>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <a class="dropdown-item" href="?page=perfil&year=<?php echo $currentYear; ?>">
                                    <?php echo tablerIcon('user'); ?>
                                    Mi Perfil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="logout()">
                                    <?php echo tablerIcon('logout'); ?>
                                    Cerrar sesión
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="collapse navbar-collapse" id="navbar-menu">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <div class="input-icon">
                                    <?php echo tablerIcon('search'); ?>
                                    <input type="search" class="form-control form-control-sm" placeholder="Buscar..." autocomplete="off">
                                </div>
                            </li>
                            <li class="nav-item ms-3">
                                <label class="nav-link d-flex align-items-center gap-2" style="cursor:default">
                                    <span class="text-secondary">Año:</span>
                                    <select id="year-selector" onchange="changeYear(this.value)" class="form-select form-select-sm border-0" style="width:auto;background:transparent;color:inherit">
                                        <?php
                                        $startYear = 2020;
                                        $endYear = date('Y') + 1;
                                        for ($y = $endYear; $y >= $startYear; $y--) {
                                            $selected = ($y == $currentYear) ? 'selected' : '';
                                            echo "<option value='{$y}' {$selected}>{$y}</option>";
                                        }
                                        ?>
                                    </select>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <div class="page-body">
                <div class="container-xl" id="main-content">
