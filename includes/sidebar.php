<?php
/**
 * Sidebar del Sistema
 * Diseño basado en Hospital Dashboard con nomenclatura BEM
 */
$currentPage = $_GET['page'] ?? 'dashboard';
$userRole = $_SESSION['rol'] ?? '';
$currentYear = $_SESSION['year'] ?? date('Y');

// Grupos de navegación
$navGroups = [
    [
        'title' => 'Dashboard',
        'items' => [
            [
                'id' => 'dashboard',
                'title' => 'Panel de Control',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
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
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>',
                'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]
            ],
            [
                'id' => 'supervision',
                'title' => 'Supervisión',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
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
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>',
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
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                'roles' => [ROL_SUPERVISOR]
            ],
            [
                'id' => 'asignaciones',
                'title' => 'Asignar Establecimientos',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/><path d="M12 3v6"/></svg>',
                'roles' => [ROL_SUPERVISOR]
            ],
            [
                'id' => 'establecimientos',
                'title' => 'Establecimientos',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M9 17h1"/></svg>',
                'roles' => [ROL_SUPERVISOR]
            ],
            [
                'id' => 'eliminadas',
                'title' => 'Eliminadas',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>',
                'roles' => [ROL_SUPERVISOR]
            ],
            [
                'id' => 'perfil',
                'title' => 'Mi Perfil',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
                'roles' => [ROL_REGISTRADOR, ROL_SUPERVISOR]
            ]
        ]
    ]
];
?>

<aside class="sidebar" id="sidebar" role="navigation" aria-label="Navegación principal">
    <!-- Logo/Brand -->
    <div class="sidebar__header">
        <div class="sidebar__logo">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="sidebar__logo-icon">
                <path d="M3 3v18h18" />
                <path d="m19 9-5 5-4-4-3 3" />
            </svg>
            <div class="sidebar__logo-text">
                <span class="sidebar__logo-title">Sistema REM</span>
                <span class="sidebar__logo-subtitle">Servicio de Salud</span>
            </div>
        </div>
    </div>

    <!-- Navigation Groups -->
    <nav class="sidebar__nav">
        <?php foreach ($navGroups as $group): ?>
            <?php
            // Verificar si algún item del grupo es visible para el rol actual
            $hasVisibleItems = false;
            foreach ($group['items'] as $item) {
                if (in_array($userRole, $item['roles'])) {
                    $hasVisibleItems = true;
                    break;
                }
            }
            if (!$hasVisibleItems)
                continue;
            ?>

            <div class="sidebar__group">
                <span class="sidebar__group-title"><?php echo $group['title']; ?></span>
                <ul class="sidebar__menu" role="menubar">
                    <?php foreach ($group['items'] as $item): ?>
                        <?php if (in_array($userRole, $item['roles'])): ?>
                            <li class="sidebar__menu-item" role="none">
                                <a href="?page=<?php echo $item['id']; ?>&year=<?php echo $currentYear; ?>"
                                    class="sidebar__nav-link <?php echo ($currentPage === $item['id']) ? 'sidebar__nav-link--active' : ''; ?>"
                                    role="menuitem" aria-current="<?php echo ($currentPage === $item['id']) ? 'page' : 'false'; ?>">
                                    <span class="sidebar__nav-icon" aria-hidden="true">
                                        <?php echo $item['icon']; ?>
                                    </span>
                                    <span class="sidebar__nav-text"><?php echo $item['title']; ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- Footer -->
    <div class="sidebar__footer">
        <div class="sidebar__version">
            Versión <?php echo APP_VERSION; ?>
        </div>
    </div>
</aside>

<!-- Overlay para móvil -->
<div class="sidebar__overlay" id="sidebarOverlay" onclick="toggleSidebar()" aria-hidden="true"></div>

<style>
    /* =========================================
   SIDEBAR - BEM Components
   ========================================= */

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background-color: var(--color-neutral-white);
        border-right: 1px solid var(--color-neutral-200);
        display: flex;
        flex-direction: column;
        z-index: 100;
        transition: transform var(--transition-base);
    }

    /* Header/Logo */
    .sidebar__header {
        padding: var(--spacing-5) var(--spacing-6);
        border-bottom: 1px solid var(--color-neutral-100);
    }

    .sidebar__logo {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .sidebar__logo-icon {
        color: var(--color-primary);
        flex-shrink: 0;
    }

    .sidebar__logo-text {
        display: flex;
        flex-direction: column;
    }

    .sidebar__logo-title {
        font-size: var(--font-size-lg);
        font-weight: 700;
        color: var(--color-secondary);
        line-height: 1.2;
    }

    .sidebar__logo-subtitle {
        font-size: var(--font-size-xs);
        color: var(--color-neutral-500);
    }

    /* Navigation */
    .sidebar__nav {
        flex: 1;
        overflow-y: auto;
        padding: var(--spacing-4) 0;
    }

    .sidebar__group {
        margin-bottom: var(--spacing-4);
    }

    .sidebar__group-title {
        display: block;
        padding: var(--spacing-2) var(--spacing-6);
        font-size: var(--font-size-xs);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-primary);
    }

    .sidebar__menu {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sidebar__menu-item {
        margin: var(--spacing-1) var(--spacing-3);
    }

    .sidebar__nav-link {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        padding: var(--spacing-3) var(--spacing-4);
        border-radius: var(--radius-lg);
        color: var(--color-neutral-600);
        text-decoration: none;
        font-size: var(--font-size-sm);
        font-weight: 500;
        transition: all var(--transition-fast);
    }

    .sidebar__nav-link:hover {
        background-color: var(--color-neutral-100);
        color: var(--color-secondary);
    }

    .sidebar__nav-link:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .sidebar__nav-link--active {
        background-color: var(--color-primary-light);
        color: var(--color-primary);
        font-weight: 600;
    }

    .sidebar__nav-link--active .sidebar__nav-icon {
        color: var(--color-primary);
    }

    .sidebar__nav-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar__nav-icon svg {
        width: 20px;
        height: 20px;
    }

    /* Footer */
    .sidebar__footer {
        padding: var(--spacing-4) var(--spacing-6);
        border-top: 1px solid var(--color-neutral-100);
    }

    .sidebar__version {
        font-size: var(--font-size-xs);
        color: var(--color-neutral-400);
        text-align: center;
    }

    /* Overlay */
    .sidebar__overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 99;
        opacity: 0;
        transition: opacity var(--transition-base);
    }

    .sidebar__overlay--visible {
        display: block;
        opacity: 1;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar--open {
            transform: translateX(0);
        }

        .sidebar__overlay--visible {
            display: block;
        }
    }

    /* Accesibilidad - reduce motion */
    @media (prefers-reduced-motion: reduce) {

        .sidebar,
        .sidebar__nav-link,
        .sidebar__overlay {
            transition: none;
        }
    }
</style>