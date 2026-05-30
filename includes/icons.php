<?php

function icon_svg($paths, $class = 'icon') {
    $pathTags = '';
    foreach ($paths as $p) {
        $tag = $p['tag'] ?? 'path';
        unset($p['tag']);
        $attrs = '';
        foreach ($p as $key => $val) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars($val, ENT_QUOTES) . '"';
        }
        $pathTags .= '<' . $tag . $attrs . '/>';
    }
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . htmlspecialchars($class, ENT_QUOTES) . '">' . $pathTags . '</svg>';
}

function icon_chart_bar($class = 'icon') {
    return icon_svg([
        ['d' => 'M3 20a1 1 0 0 1-1-1v-6a1 1 0 0 1 2 0v6a1 1 0 0 1-1 1z'],
        ['d' => 'M10 20a1 1 0 0 1-1-1v-10a1 1 0 0 1 2 0v10a1 1 0 0 1-1 1z'],
        ['d' => 'M17 20a1 1 0 0 1-1-1v-14a1 1 0 0 1 2 0v14a1 1 0 0 1-1 1z'],
    ], $class);
}

function icon_clock($class = 'icon') {
    return icon_svg([
        ['tag' => 'circle', 'cx' => '12', 'cy' => '12', 'r' => '9'],
        ['tag' => 'polyline', 'points' => '12 7 12 12 15 15'],
    ], $class);
}

function icon_circle_check($class = 'icon') {
    return icon_svg([
        ['tag' => 'circle', 'cx' => '12', 'cy' => '12', 'r' => '9'],
        ['d' => 'M9 12l2 2 4-4'],
    ], $class);
}

function icon_alert_triangle($class = 'icon') {
    return icon_svg([
        ['d' => 'M12 9v4'],
        ['d' => 'M12 17'],
        ['d' => 'M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636-2.871L13.637 3.591a1.913 1.913 0 0 0-3.274 0z'],
    ], $class);
}

function icon_chart_pie($class = 'icon') {
    return icon_svg([
        ['d' => 'M12 12l-6.5 6.5'],
        ['d' => 'M12 3v9h9'],
        ['tag' => 'circle', 'cx' => '12', 'cy' => '12', 'r' => '9'],
    ], $class);
}

function icon_search($class = 'icon') {
    return icon_svg([
        ['tag' => 'circle', 'cx' => '11', 'cy' => '11', 'r' => '8'],
        ['d' => 'M21 21l-4.3-4.3'],
    ], $class);
}

function icon_zap($class = 'icon') {
    return icon_svg([
        ['tag' => 'polygon', 'points' => '13 2 4 14 12 14 11 22 20 10 12 10'],
    ], $class);
}

function icon_calendar($class = 'icon') {
    return icon_svg([
        ['tag' => 'rect', 'x' => '3', 'y' => '6', 'width' => '18', 'height' => '18', 'rx' => '2'],
        ['d' => 'M3 12h18'],
        ['d' => 'M9 3v4'],
        ['d' => 'M15 3v4'],
    ], $class);
}

function icon_clipboard_list($class = 'icon') {
    return icon_svg([
        ['d' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2'],
        ['tag' => 'rect', 'x' => '9', 'y' => '3', 'width' => '6', 'height' => '4', 'rx' => '1'],
        ['d' => 'M9 12h6'],
        ['d' => 'M9 16h4'],
    ], $class);
}

function icon_edit($class = 'icon') {
    return icon_svg([
        ['d' => 'M15.232 5.232l3.536 3.536M9 11l-3 3v3h3l8.232-8.232a2.5 2.5 0 00-3.536-3.536L9 11z'],
    ], $class);
}

function icon_download($class = 'icon') {
    return icon_svg([
        ['d' => 'M12 15V3m0 12l-4-4m4 4l4-4'],
        ['d' => 'M3 16v2a2 2 0 002 2h14a2 2 0 002-2v-2'],
    ], $class);
}

function icon_eye($class = 'icon') {
    return icon_svg([
        ['tag' => 'circle', 'cx' => '12', 'cy' => '12', 'r' => '2'],
        ['d' => 'M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7c2.667-4.667 6-7 10-7s7.333 2.333 10 7z'],
    ], $class);
}

function icon_file_text($class = 'icon') {
    return icon_svg([
        ['d' => 'M14 3v4a1 1 0 001 1h4'],
        ['d' => 'M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z'],
        ['d' => 'M9 12h6'],
        ['d' => 'M9 16h4'],
    ], $class);
}
