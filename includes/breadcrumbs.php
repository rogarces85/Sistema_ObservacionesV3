<?php
/**
 * Helper para construir migas de pan consistentes en todas las vistas.
 * Devuelve HTML listo para imprimir dentro de <nav class="breadcrumb">.
 */

function breadcrumbItemsFor($currentPage) {
    $items = [
        ['id' => 'dashboard',     'label' => 'Panel de Control',   'icon' => 'home'],
        ['id' => 'observaciones', 'label' => 'Observaciones',      'icon' => 'file-text', 'parent' => 'dashboard'],
        ['id' => 'supervision',   'label' => 'Supervisión',        'icon' => 'eye',        'parent' => 'dashboard'],
        ['id' => 'reportes',      'label' => 'Reportes',           'icon' => 'chart-bar',  'parent' => 'dashboard'],
        ['id' => 'usuarios',      'label' => 'Usuarios',           'icon' => 'users',      'parent' => 'dashboard'],
        ['id' => 'asignaciones',  'label' => 'Asignaciones',       'icon' => 'package',    'parent' => 'dashboard'],
        ['id' => 'establecimientos','label' => 'Establecimientos',  'icon' => 'building',   'parent' => 'dashboard'],
        ['id' => 'eliminadas',     'label' => 'Eliminadas',         'icon' => 'trash',      'parent' => 'dashboard'],
        ['id' => 'perfil',         'label' => 'Mi Perfil',          'icon' => 'user',       'parent' => 'dashboard'],
    ];

    $byId = [];
    foreach ($items as $it) {
        $byId[$it['id']] = $it;
    }

    $current = $byId[$currentPage] ?? null;
    if (!$current) {
        return [];
    }

    $chain = [];
    $cursor = $current;
    while ($cursor) {
        array_unshift($chain, $cursor);
        $cursor = ($cursor['parent'] ?? null) && isset($byId[$cursor['parent']]) ? $byId[$cursor['parent']] : null;
    }
    return $chain;
}

function renderBreadcrumb($currentPage) {
    $items = breadcrumbItemsFor($currentPage);
    if (empty($items)) {
        return '';
    }
    $total = count($items);
    $html = '<nav class="breadcrumb" aria-label="Migas de pan"><ol class="breadcrumb-list list-unstyled d-flex flex-wrap m-0 p-0">';
    foreach ($items as $idx => $item) {
        $isLast = ($idx === $total - 1);
        $html .= '<li class="breadcrumb-item' . ($isLast ? ' active' : '') . '">';
        if ($isLast) {
            $html .= '<span><i class="ti ti-' . htmlspecialchars($item['icon'], ENT_QUOTES) . ' me-1"></i>' . htmlspecialchars($item['label'], ENT_QUOTES) . '</span>';
        } else {
            $html .= '<a href="?page=' . htmlspecialchars($item['id'], ENT_QUOTES) . '"><i class="ti ti-' . htmlspecialchars($item['icon'], ENT_QUOTES) . ' me-1"></i>' . htmlspecialchars($item['label'], ENT_QUOTES) . '</a>';
        }
        $html .= '</li>';
    }
    $html .= '</ol></nav>';
    return $html;
}
