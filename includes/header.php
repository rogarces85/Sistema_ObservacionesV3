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
    'usuarios' => 'Usuarios',
    'asignaciones' => 'Asignaciones',
    'eliminadas' => 'Eliminadas',
    'perfil' => 'Mi Perfil',
    'importacion' => 'Importar Excel'
];
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php require_once 'includes/csrf.php'; echo CSRF::generateToken(); ?>">
    <meta name="description" content="Sistema de gestión de observaciones REM">
    <title><?php echo APP_NAME; ?> - <?php echo $pageTitles[$currentPage] ?? 'Panel'; ?></title>

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
            <header class="navbar-top">
                <button class="mobile-toggle" onclick="toggleSidebar()">
                    <?php echo tablerIcon('menu'); ?>
                </button>

                <div class="nav-search">
                    <?php echo tablerIcon('search'); ?>
                    <input type="text" placeholder="Buscar...">
                </div>

                <div class="nav-year">
                    <span>Año:</span>
                    <select id="year-selector" onchange="changeYear(this.value)">
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

                <div class="nav-user dropdown">
                    <div class="user-avatar"><?php echo $userInitials; ?></div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($userRole); ?></span>
                    </div>
                    <div class="dropdown-menu" id="user-dropdown">
                        <a class="dropdown-item" href="?pagina=perfil&anio=<?php echo $currentYear; ?>">
                            <?php echo tablerIcon('user'); ?>
                            Mi Perfil
                        </a>
                        <a class="dropdown-item danger" href="#" onclick="logout()">
                            <?php echo tablerIcon('logout'); ?>
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </header>

            <div class="page-body">
                <div class="container-fluid">