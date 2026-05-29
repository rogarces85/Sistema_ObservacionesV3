<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$userRole = $_SESSION['rol'] ?? '';
$currentYear = $_SESSION['year'] ?? date('Y');

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

function tablerIcon($name) {
    $icons = [
        'home' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>',
        'file-text' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><line x1="9" x2="15" y1="9" y2="9"/><line x1="9" x2="15" y1="13" y2="13"/><line x1="9" x2="13" y1="17" y2="17"/></svg>',
        'eye' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>',
        'chart-bar' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>',
        'users' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg>',
        'package' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>',
        'building' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M3 21h18"/><path d="M5 21v-14l8 -4v18"/><path d="M19 21v-10l-6 -4"/><path d="M9 9v.01"/><path d="M9 13v.01"/><path d="M9 17v.01"/></svg>',
        'trash' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>',
        'user' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    ];
    return $icons[$name] ?? '';
}
?>
<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-brand navbar-brand-autodark">
            <a href="?page=dashboard" class="text-white text-decoration-none">
                <span class="fs-4 fw-bold">Sistema REM</span>
                <small class="d-block text-secondary" style="font-size:0.65rem">Servicio de Salud</small>
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
                        <small class="nav-subtitle text-secondary px-3 pb-1 d-block text-uppercase" style="font-size:0.65rem;letter-spacing:0.05em">
                            <?php echo $group['title']; ?>
                        </small>
                    </li>
                    <?php foreach ($group['items'] as $item): ?>
                        <?php if (in_array($userRole, $item['roles'])): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($currentPage === $item['id']) ? 'active' : ''; ?>"
                                    href="?page=<?php echo $item['id']; ?>&year=<?php echo $currentYear; ?>">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><?php echo tablerIcon($item['icon']); ?></span>
                                    <span class="nav-link-title"><?php echo $item['title']; ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="mt-auto px-3 py-3 d-none d-lg-block">
            <small class="text-secondary" style="font-size:0.65rem">Versión <?php echo APP_VERSION; ?></small>
        </div>
    </div>
</aside>
