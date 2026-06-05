<?php
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/icons.php';

$currentPage = $_GET['pagina'] ?? 'dashboard';
$currentYear = $_SESSION['anio_trabajo'] ?? date('Y');
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

    <link rel="stylesheet" href="assets/css/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="assets/css/tabler-light.css">
</head>
<body>
    <div class="page">
        <?php include 'includes/sidebar.php'; ?>

        <div class="page-wrapper">
            <header class="navbar">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <button class="navbar-toggler sidebar-toggle d-lg-none me-2" type="button">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="navbar-brand d-md-none">
                            <a href="?pagina=dashboard" class="text-decoration-none fw-bold">Sistema REM</a>
                        </div>
                    </div>
                    <div class="navbar-nav flex-row order-md-last ms-auto">
                        <div class="nav-item dropdown d-none d-md-flex">
                            <a href="#" class="nav-link px-2" data-bs-toggle="dropdown" title="Notificaciones">
                                <?php echo tablerIcon('bell'); ?>
                            </a>
                        </div>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link d-flex align-items-center" data-bs-toggle="dropdown">
                                <span class="avatar avatar-sm"><?php echo $userInitials; ?></span>
                                <span class="d-none d-md-inline ms-2">
                                    <span class="fw-semibold"><?php echo htmlspecialchars($userName); ?></span>
                                    <span class="text-secondary d-block" style="font-size:0.7rem"><?php echo htmlspecialchars(ucfirst($userRole)); ?></span>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="?pagina=perfil&anio=<?php echo $currentYear; ?>">
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
                    <div class="navbar-collapse collapse d-none d-md-flex">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <div class="input-icon">
                                    <?php echo tablerIcon('search'); ?>
                                    <input type="search" class="form-control" placeholder="Buscar..." autocomplete="off">
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
                        <label class="d-flex align-items-center gap-2 text-secondary" style="cursor:default">
                            <span>Año:</span>
                            <select id="year-selector" onchange="changeYear(this.value)" class="form-select form-select-sm" style="width:auto">
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
                    </div>
                </div>
            </header>

            <div class="page-body">
                <div class="container-fluid" id="main-content">