<?php
/**
 * Header del Sistema
 * Diseño basado en Hospital Dashboard con nomenclatura BEM
 */
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$currentPage = $_GET['page'] ?? 'dashboard';
$currentYear = $_SESSION['year'] ?? date('Y');
$userName = $_SESSION['nombre_completo'] ?? 'Usuario';
$userRole = $_SESSION['rol'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));

// Títulos de páginas
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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php require_once 'includes/csrf.php';
    echo CSRF::generateToken(); ?>">
    <meta name="description" content="Sistema de gestión de observaciones REM para el Servicio de Salud Osorno">
    <title><?php echo APP_NAME; ?> - <?php echo $pageTitles[$currentPage] ?? 'Panel de Control'; ?></title>

    <!-- Preconnect fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/notifications.js"></script>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-container">
            <header class="header" role="banner">
                <div class="header__left">
                    <!-- Botón menú móvil -->
                    <button type="button" onclick="toggleSidebar()" class="header__menu-btn" id="mobile-menu-btn"
                        aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <line x1="4" x2="20" y1="12" y2="12" />
                            <line x1="4" x2="20" y1="6" y2="6" />
                            <line x1="4" x2="20" y1="18" y2="18" />
                        </svg>
                    </button>

                    <!-- Barra de búsqueda -->
                    <div class="header__search">
                        <label for="search-input" class="visually-hidden">Buscar</label>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="header__search-icon" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                        <input type="search" id="search-input" class="header__search-input" placeholder="Buscar..."
                            autocomplete="off">
                    </div>
                </div>

                <div class="header__right">
                    <!-- Selector de año -->
                    <div class="header__year-selector">
                        <label for="year-selector" class="header__year-label">Año:</label>
                        <select id="year-selector" onchange="changeYear(this.value)" class="header__year-select"
                            aria-label="Seleccionar año">
                            <?php
                            $startYear = 2020;
                            $endYear = date('Y') + 1;
                            for ($y = $endYear; $y >= $startYear; $y--) {
                                $selected = ($y == $currentYear) ? 'selected' : '';
                                echo "<option value='{$y}' {$selected}>{$y}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Notificaciones (placeholder) -->
                    <button type="button" class="header__icon-btn" aria-label="Ver notificaciones">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                        </svg>
                    </button>

                    <!-- Mensajes (placeholder) -->
                    <button type="button" class="header__icon-btn" aria-label="Ver mensajes">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                    </button>

                    <!-- Usuario -->
                    <div class="header__user">
                        <div class="header__user-avatar" aria-hidden="true">
                            <?php echo $userInitials; ?>
                        </div>
                        <div class="header__user-info">
                            <span class="header__user-name"><?php echo htmlspecialchars($userName); ?></span>
                            <span class="header__user-role"><?php echo htmlspecialchars(ucfirst($userRole)); ?></span>
                        </div>
                        <button type="button" onclick="logout()" class="header__logout-btn" title="Cerrar sesión"
                            aria-label="Cerrar sesión">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" x2="9" y1="12" y2="12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <main class="content-container" id="main-content" role="main" tabindex="-1">