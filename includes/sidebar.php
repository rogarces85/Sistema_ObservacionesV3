<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$userRole = $_SESSION['rol'] ?? '';
$currentYear = $_SESSION['anio_trabajo'] ?? date('Y');

$navGroups = [
    [
        'title' => 'Dashboard',
        'items' => [
            [
                'id' => 'dashboard',
                'title' => 'Panel de Control',
                'icon' => 'home',
                'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]
            ]
        ]
    ],
    [
        'title' => 'Gestión',
        'items' => [
            [
                'id' => 'observaciones',
                'title' => 'Observaciones',
                'icon' => 'file-text',
                'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]
            ],
            [
                'id' => 'importacion',
                'title' => 'Importar desde Excel',
                'icon' => 'upload',
                'roles' => [ROL_REGISTRADOR]
            ],
            [
                'id' => 'supervision',
                'title' => 'Supervisión',
                'icon' => 'eye',
                'roles' => [ROL_SUPERVISOR]
            ]
        ]
    ],
    [
        'title' => 'Reportes',
        'items' => [
            [
                'id' => 'reportes',
                'title' => 'Reportes',
                'icon' => 'chart-bar',
                'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]
            ]
        ]
    ],
    [
        'title' => 'Configuración',
        'items' => [
            [
                'id' => 'usuarios',
                'title' => 'Usuarios',
                'icon' => 'users',
                'roles' => [ROL_SUPERVISOR]
            ],
            [
                'id' => 'asignaciones',
                'title' => 'Asignar Establecimientos',
                'icon' => 'package',
                'roles' => [ROL_SUPERVISOR]
            ],
            [
                'id' => 'establecimientos',
                'title' => 'Establecimientos',
                'icon' => 'building',
                'roles' => [ROL_SUPERVISOR]
            ],
            [
                'id' => 'eliminadas',
                'title' => 'Eliminadas',
                'icon' => 'trash',
                'roles' => [ROL_SUPERVISOR]
            ],
            [
                'id' => 'perfil',
                'title' => 'Mi Perfil',
                'icon' => 'user',
                'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]
            ]
        ]
    ]
];
?>
<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-brand navbar-brand-autodark">
            <a href="?pagina=dashboard" class="text-white text-decoration-none">
                <span class="fs-4 fw-bold">Sistema REM</span>
                <small class="d-block text-secondary">Servicio de Salud</small>
            </a>
        </div>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-4" data-bs-toggle="mobile-nav">
                <?php foreach ($navGroups as $group): ?>
                    <?php
                    $hasVisibleItems = false;
                    foreach ($group['items'] as $item) {
                        if (in_array($userRole, $item['roles'])) {
                            $hasVisibleItems = true;
                            break;
                        }
                    }
                    if (!$hasVisibleItems) continue;
                    ?>
                    <li class="nav-item">
                        <small class="nav-subtitle text-secondary px-3 pb-1 d-block text-uppercase">
                            <?php echo $group['title']; ?>
                        </small>
                    </li>
                    <?php foreach ($group['items'] as $item): ?>
                        <?php if (in_array($userRole, $item['roles'])): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($currentPage === $item['id']) ? 'active' : ''; ?>"
                                    href="?pagina=<?php echo $item['id']; ?>&anio=<?php echo $currentYear; ?>">
                                    <span class="nav-link-icon"><?php echo tablerIcon($item['icon']); ?></span>
                                    <span class="nav-link-title"><?php echo $item['title']; ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="mt-auto px-3 py-3 d-none d-lg-block">
            <small class="text-secondary">Versión <?php echo APP_VERSION; ?></small>
        </div>
    </div>
</aside>
