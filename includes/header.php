<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

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
    'perfil' => 'Mi Perfil'
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
    <link rel="stylesheet" href="assets/css/styles.css">
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>
                            </a>
                        </div>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link d-flex align-items-center px-2" data-bs-toggle="dropdown" aria-label="Menú de usuario">
                                <span class="avatar avatar-sm" style="background:#0ea5e9"><?php echo $userInitials; ?></span>
                                <span class="ms-2 d-none d-md-inline">
                                    <span class="fw-semibold"><?php echo htmlspecialchars($userName); ?></span>
                                    <small class="d-block text-secondary" style="font-size:0.7rem"><?php echo htmlspecialchars(ucfirst($userRole)); ?></small>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="?page=perfil&year=<?php echo $currentYear; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="logout()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/><path d="M9 12h12l-3 -3m0 6l3 -3"/></svg>
                                    Cerrar sesión
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="collapse navbar-collapse" id="navbar-menu">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <div class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                    <input type="search" class="form-control form-control-sm" placeholder="Buscar..." autocomplete="off">
                                </div>
                            </li>
                            <li class="nav-item ms-3">
                                <label class="nav-link d-flex align-items-center gap-2" style="cursor:default">
                                    <span class="text-secondary" style="font-size:0.85rem">Año:</span>
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
