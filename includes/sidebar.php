<?php
$currentPage = $_GET['pagina'] ?? 'dashboard';
$userRole = $_SESSION['rol'] ?? '';
$currentYear = $_SESSION['anio_trabajo'] ?? date('Y');
$userName = $_SESSION['nombre_completo'] ?? 'Usuario';
$userInitials = strtoupper(substr($userName, 0, 2));

$navItems = [
    'dashboard' => ['title' => 'Panel de Control', 'icon' => 'home'],
    'observaciones' => ['title' => 'Observaciones', 'icon' => 'file-text'],
    'importacion' => ['title' => 'Importar Excel', 'icon' => 'upload', 'roles' => [ROL_REGISTRADOR]],
    'supervision' => ['title' => 'Supervisión', 'icon' => 'eye', 'roles' => [ROL_SUPERVISOR]],
    'reportes' => ['title' => 'Reportes', 'icon' => 'chart-bar'],
];

$configItems = [
    'usuarios' => ['title' => 'Usuarios', 'icon' => 'users', 'roles' => [ROL_SUPERVISOR]],
    'asignaciones' => ['title' => 'Asignaciones', 'icon' => 'package', 'roles' => [ROL_SUPERVISOR]],
    'establecimientos' => ['title' => 'Establecimientos', 'icon' => 'building', 'roles' => [ROL_SUPERVISOR]],
    'eliminadas' => ['title' => 'Eliminadas', 'icon' => 'trash', 'roles' => [ROL_SUPERVISOR]],
    'perfil' => ['title' => 'Mi Perfil', 'icon' => 'user'],
];
?>

<aside class="navbar-vertical">
    <a href="?pagina=dashboard" class="sidebar-brand">
        <div class="brand-icon">
            <?php echo tablerIcon('clipboard-heart'); ?>
        </div>
        <div class="brand-text">
            <span class="title">Sistema REM</span>
            <span class="subtitle">Servicio de Salud</span>
        </div>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <?php foreach ($navItems as $id => $item): ?>
                <?php if (isset($item['roles']) && !in_array($userRole, $item['roles'])) continue; ?>
                <div class="nav-item">
                    <a href="?pagina=<?php echo $id; ?>&anio=<?php echo $currentYear; ?>" 
                       class="nav-link <?php echo ($currentPage === $id) ? 'active' : ''; ?>">
                        <?php echo tablerIcon($item['icon']); ?>
                        <span><?php echo $item['title']; ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        $hasConfigItems = false;
        foreach ($configItems as $item) {
            if (!isset($item['roles']) || in_array($userRole, $item['roles'])) {
                $hasConfigItems = true;
                break;
            }
        }
        ?>
        
        <?php if ($hasConfigItems): ?>
        <div class="nav-section">
            <div class="nav-section-title">Configuración</div>
            <?php foreach ($configItems as $id => $item): ?>
                <?php if (isset($item['roles']) && !in_array($userRole, $item['roles'])) continue; ?>
                <div class="nav-item">
                    <a href="?pagina=<?php echo $id; ?>&anio=<?php echo $currentYear; ?>" 
                       class="nav-link <?php echo ($currentPage === $id) ? 'active' : ''; ?>">
                        <?php echo tablerIcon($item['icon']); ?>
                        <span><?php echo $item['title']; ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        Versión <?php echo APP_VERSION; ?>
    </div>
</aside>