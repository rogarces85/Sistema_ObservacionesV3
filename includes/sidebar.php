<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$userRole = $_SESSION['rol'] ?? '';
$currentYear = $_SESSION['year'] ?? date('Y');
$appVersion = defined('APP_VERSION') ? APP_VERSION : '1.0';

$navGroups = [
    [
        'title' => 'Principal',
        'icon'  => 'layout-dashboard',
        'items' => [
            ['id' => 'dashboard', 'title' => 'Panel de Control', 'icon' => 'home', 'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]],
        ],
    ],
    [
        'title' => 'Gestión',
        'icon'  => 'briefcase',
        'items' => [
            ['id' => 'observaciones', 'title' => 'Observaciones', 'icon' => 'file-text', 'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]],
            ['id' => 'supervision',   'title' => 'Supervisión',   'icon' => 'eye',        'roles' => [ROL_SUPERVISOR]],
        ],
    ],
    [
        'title' => 'Analítica',
        'icon'  => 'chart-bar',
        'items' => [
            ['id' => 'reportes', 'title' => 'Reportes', 'icon' => 'chart-bar', 'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]],
        ],
    ],
    [
        'title' => 'Administración',
        'icon'  => 'settings',
        'items' => [
            ['id' => 'usuarios',       'title' => 'Usuarios',           'icon' => 'users',      'roles' => [ROL_SUPERVISOR]],
            ['id' => 'asignaciones',   'title' => 'Asignar Establec.', 'icon' => 'package',    'roles' => [ROL_SUPERVISOR]],
            ['id' => 'establecimientos','title' => 'Establecimientos',  'icon' => 'building',   'roles' => [ROL_SUPERVISOR]],
            ['id' => 'eliminadas',     'title' => 'Eliminadas',         'icon' => 'trash',      'roles' => [ROL_SUPERVISOR]],
            ['id' => 'perfil',         'title' => 'Mi Perfil',          'icon' => 'user',       'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]],
        ],
    ],
];
?>
<aside class="navbar navbar-vertical navbar-expand-lg" id="sidebarMain" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Alternar navegación">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-brand navbar-brand-autodark d-flex flex-column align-items-start ps-3 py-2">
            <a href="?page=dashboard" class="text-white text-decoration-none d-flex align-items-center gap-2">
                <span class="avatar avatar-sm" style="background: rgba(255,255,255,0.10); color: #ffffff;">
                    <i class="ti ti-heart-rate-monitor"></i>
                </span>
                <div class="d-flex flex-column lh-1">
                    <span class="fs-5 fw-bold">Sistema REM</span>
                    <small class="text-secondary" style="font-size:0.65rem; letter-spacing:0.06em; text-transform:uppercase;">Servicio de Salud</small>
                </div>
            </a>
        </div>

        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-2" role="list">
                <?php foreach ($navGroups as $group): ?>
                    <?php
                    $hasVisibleItems = false;
                    foreach ($group['items'] as $item) {
                        if (in_array($userRole, $item['roles'])) {
                            $hasVisibleItems = true;
                            break;
                        }
                    }
                    if (!$hasVisibleItems) {
                        continue;
                    }
                    ?>
                    <li class="nav-item">
                        <small class="nav-subtitle d-flex align-items-center gap-2 text-secondary px-3 pb-1 mt-3">
                            <i class="ti ti-<?php echo htmlspecialchars($group['icon'], ENT_QUOTES); ?>"></i>
                            <span class="sidebar-footer-label"><?php echo htmlspecialchars($group['title'], ENT_QUOTES); ?></span>
                        </small>
                    </li>
                    <?php foreach ($group['items'] as $item): ?>
                        <?php if (in_array($userRole, $item['roles'])): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($currentPage === $item['id']) ? 'active' : ''; ?>"
                                    href="?page=<?php echo htmlspecialchars($item['id'], ENT_QUOTES); ?>&year=<?php echo htmlspecialchars($currentYear, ENT_QUOTES); ?>"
                                    <?php if ($currentPage === $item['id']) echo 'aria-current="page"'; ?>>
                                    <span class="nav-link-icon"><i class="ti ti-<?php echo htmlspecialchars($item['icon'], ENT_QUOTES); ?>"></i></span>
                                    <span class="nav-link-title"><?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="sidebar-footer d-none d-lg-flex align-items-center gap-2">
            <span class="status status-green" aria-hidden="true"></span>
            <span class="sidebar-footer-label">v<?php echo htmlspecialchars($appVersion); ?></span>
        </div>
    </div>
</aside>
