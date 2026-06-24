<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/breadcrumbs.php';

$currentPage = $_GET['page'] ?? 'dashboard';
$currentYear = $_SESSION['year'] ?? date('Y');
$userName = $_SESSION['nombre_completo'] ?? 'Usuario';
$userRole = $_SESSION['rol'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));
$appName = defined('APP_NAME') ? APP_NAME : 'Sistema REM';
$appVersion = defined('APP_VERSION') ? APP_VERSION : '1.0';
$initialTheme = $_COOKIE['rem_theme'] ?? $_COOKIE['rem.theme'] ?? 'light';
$initialTheme = in_array($initialTheme, ['light', 'dark'], true) ? $initialTheme : 'light';

$pageTitles = [
    'dashboard' => 'Panel de Control',
    'observaciones' => 'Observaciones',
    'supervision' => 'Supervisión',
    'reportes' => 'Reportes',
    'usuarios' => 'Gestión de Usuarios',
    'asignaciones' => 'Asignación de Establecimientos',
    'establecimientos' => 'Establecimientos',
    'versionado' => 'Versionado',
    'eliminadas' => 'Observaciones Eliminadas',
    'perfil' => 'Mi Perfil',
];

$pageTitle = $pageTitles[$currentPage] ?? 'Panel de Control';
$breadcrumbHtml = renderBreadcrumb($currentPage);
?><!DOCTYPE html>
<html lang="es" data-bs-theme="<?php echo htmlspecialchars($initialTheme, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?php require_once 'includes/csrf.php'; echo CSRF::generateToken(); ?>">
    <meta name="description" content="Sistema de gestión de observaciones REM para el Servicio de Salud Osorno">
    <title><?php echo htmlspecialchars($appName); ?> - <?php echo htmlspecialchars($pageTitle); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.21.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/tabler-override.css">
</head>
<body>
    <a class="skip-link" href="#main-content">Saltar al contenido principal</a>

    <div class="page" data-sidebar="full">
        <?php include 'includes/sidebar.php'; ?>

        <div class="page-wrapper">
            <header class="navbar navbar-expand-md d-print-none">
                <div class="container-xl">
                    <button class="navbar-toggler sidebar-toggle me-2" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas"
                        aria-controls="sidebarOffcanvas" aria-label="Abrir menú lateral">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="navbar-brand navbar-brand-autodark d-md-none">
                        <a href="?page=dashboard" class="text-decoration-none fs-5 fw-bold">
                            <i class="ti ti-heart-rate-monitor me-1"></i><?php echo htmlspecialchars($appName); ?>
                        </a>
                    </div>

                    <div class="navbar-nav flex-row order-md-last ms-auto align-items-center">
                        <div class="nav-item d-none d-md-block me-2">
                            <button id="globalSearchBtn" class="btn btn-icon btn-ghost-primary" type="button"
                                data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Buscar">
                                <i class="ti ti-search"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end search-results" aria-labelledby="globalSearchBtn">
                                <div class="px-2 py-1">
                                    <div class="input-icon">
                                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                                        <input id="globalSearchInput" type="search" class="form-control"
                                            placeholder="Buscar vistas, observaciones, reportes..."
                                            autocomplete="off">
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <div id="globalSearchResults">
                                    <a class="search-result-item" href="?page=dashboard">
                                        <i class="ti ti-home"></i>
                                        <div>
                                            <div>Panel de Control</div>
                                            <div class="search-result-meta">Resumen general del sistema</div>
                                        </div>
                                    </a>
                                    <a class="search-result-item" href="?page=observaciones">
                                        <i class="ti ti-file-text"></i>
                                        <div>
                                            <div>Observaciones</div>
                                            <div class="search-result-meta">Listado y registro de observaciones REM</div>
                                        </div>
                                    </a>
                                    <a class="search-result-item" href="?page=reportes">
                                        <i class="ti ti-chart-bar"></i>
                                        <div>
                                            <div>Reportes</div>
                                            <div class="search-result-meta">Análisis y gráficos de errores</div>
                                        </div>
                                    </a>
                                    <a class="search-result-item" href="?page=perfil">
                                        <i class="ti ti-user"></i>
                                        <div>
                                            <div>Mi Perfil</div>
                                            <div class="search-result-meta">Datos y contraseña</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="nav-item dropdown me-2">
                            <button id="notifBtn" class="btn btn-icon btn-ghost-primary position-relative" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificaciones">
                                <i class="ti ti-bell"></i>
                                <span class="badge bg-danger text-white notif-dot" aria-hidden="true">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notif-dropdown" aria-labelledby="notifBtn">
                                <div class="notif-header">
                                    <strong>Notificaciones</strong>
                                    <a href="#" class="small text-decoration-none" id="markNotificationsRead">Marcar como leídas</a>
                                </div>
                                <div id="notifList">
                                    <div class="notif-item">
                                        <i class="ti ti-info-circle"></i>
                                        <div>
                                            <div class="notif-title">Sin notificaciones nuevas</div>
                                            <div class="notif-time">Aquí aparecerán los avisos relevantes del sistema.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="nav-item dropdown me-2">
                            <button id="yearBtn" class="btn btn-ghost-primary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Cambiar año de trabajo">
                                <i class="ti ti-calendar-event me-1"></i>Año: <strong><?php echo htmlspecialchars($currentYear); ?></strong>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="yearBtn">
                                <div class="dropdown-header">Cambiar año de trabajo</div>
                                <div class="dropdown-divider"></div>
                                <?php
                                $startYear = 2020;
                                $endYear = date('Y') + 1;
                                for ($y = $endYear; $y >= $startYear; $y--) {
                                    $active = ($y == $currentYear) ? ' active' : '';
                                    echo '<a class="dropdown-item' . $active . '" href="#" data-year="' . $y . '" onclick="return changeYearViaDropdown(this, event)"><i class="ti ti-calendar-stats me-2"></i>' . $y . '</a>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="nav-item dropdown">
                            <button id="userMenuBtn" class="btn btn-ghost-primary d-flex align-items-center ps-2 pe-2" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de usuario">
                                <span class="avatar-wrapper">
                                    <span class="avatar avatar-sm"><?php echo htmlspecialchars($userInitials); ?></span>
                                    <span class="avatar-status" aria-hidden="true"></span>
                                </span>
                                <span class="ms-2 d-none d-md-inline text-start">
                                    <span class="d-block fw-semibold lh-1"><?php echo htmlspecialchars($userName); ?></span>
                                    <small class="text-secondary d-block lh-1 mt-1" style="font-size:0.7rem"><?php echo htmlspecialchars(ucfirst($userRole)); ?></small>
                                </span>
                                <i class="ti ti-chevron-down ms-2 d-none d-md-inline"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuBtn">
                                <div class="dropdown-header d-flex align-items-center">
                                    <span class="avatar avatar-sm me-2"><?php echo htmlspecialchars($userInitials); ?></span>
                                    <div>
                                        <div class="fw-semibold lh-1"><?php echo htmlspecialchars($userName); ?></div>
                                        <small class="text-secondary"><?php echo htmlspecialchars(ucfirst($userRole)); ?></small>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="?page=perfil">
                                    <i class="ti ti-user me-2"></i>Mi Perfil
                                </a>
                                <a class="dropdown-item" href="?page=dashboard">
                                    <i class="ti ti-layout-dashboard me-2"></i>Panel de Control
                                </a>
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item" type="button" id="themeToggle">
                                    <i class="ti ti-moon me-2"></i>Cambiar tema
                                </button>
                                <a class="dropdown-item text-danger" href="#" onclick="logout(); return false;">
                                    <i class="ti ti-logout me-2"></i>Cerrar sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="page-body">
                <div class="container-xl" id="main-content">
                    <?php if ($breadcrumbHtml): ?>
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                            <div>
                                <?php echo $breadcrumbHtml; ?>
                                <h1 class="page-title mb-0"><?php echo htmlspecialchars($pageTitle); ?></h1>
                            </div>
                        </div>
                    <?php endif; ?>
