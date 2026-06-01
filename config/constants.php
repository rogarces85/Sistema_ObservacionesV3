<?php
/**
 * Constantes del Sistema
 * Definición de estados, roles, tipos de error, y otros valores constantes
 */

// Estados de observaciones
define('ESTADO_PENDIENTE', 'pendiente');
define('ESTADO_APROBADO', 'aprobado');
define('ESTADO_RECHAZADO', 'rechazado');
define('ESTADO_ERROR', 'error');
define('ESTADO_JUSTIFICADO', 'justificado');

// Roles de usuario
define('ROL_REGISTRADOR', 'registrador');
define('ROL_SUPERVISOR', 'supervisor');

// Plazo de entrega
define('PLAZO_DENTRO', 'dentro_plazo');
define('PLAZO_FUERA', 'fuera_plazo');

// Uso de validador
define('USA_VALIDADOR_SI', 'si');
define('USA_VALIDADOR_NO', 'no');

// Tipos de observación (columna TIPO)
$TIPOS_ERROR = [
    'S/OBSERVACION',
    'ERROR',
    'REVISAR',
    'F/PLAZO'
];

// Series REM (columna SERIE)
$SERIES_REM = [
    'SERIE A',
    'SERIE BS',
    'SERIE BM',
    'SERIE P',
    'SERIE ANEXO',
    'SERIE D'
];

// Hojas REM por Serie (codigo => nombre para mostrar en select)
$HOJAS_POR_SERIE = [
    'SERIE A' => [
        ['codigo' => 'Hoja Nombre', 'nombre' => 'Hoja Nombre'],
        ['codigo' => 'A01', 'nombre' => 'A01'],
        ['codigo' => 'A02', 'nombre' => 'A02'],
        ['codigo' => 'A03', 'nombre' => 'A03'],
        ['codigo' => 'A04', 'nombre' => 'A04'],
        ['codigo' => 'A05', 'nombre' => 'A05'],
        ['codigo' => 'A06', 'nombre' => 'A06'],
        ['codigo' => 'A07', 'nombre' => 'A07'],
        ['codigo' => 'A08', 'nombre' => 'A08'],
        ['codigo' => 'A09', 'nombre' => 'A09'],
        ['codigo' => 'A11', 'nombre' => 'A11'],
        ['codigo' => 'A11a', 'nombre' => 'A11a'],
        ['codigo' => 'A19a', 'nombre' => 'A19a'],
        ['codigo' => 'A19b', 'nombre' => 'A19b'],
        ['codigo' => 'A21', 'nombre' => 'A21'],
        ['codigo' => 'A23', 'nombre' => 'A23'],
        ['codigo' => 'A24', 'nombre' => 'A24'],
        ['codigo' => 'A25', 'nombre' => 'A25'],
        ['codigo' => 'A26', 'nombre' => 'A26'],
        ['codigo' => 'A27', 'nombre' => 'A27'],
        ['codigo' => 'A28', 'nombre' => 'A28'],
        ['codigo' => 'A29', 'nombre' => 'A29'],
        ['codigo' => 'A30', 'nombre' => 'A30'],
        ['codigo' => 'A30ar', 'nombre' => 'A30AR'],
        ['codigo' => 'A31', 'nombre' => 'A31'],
        ['codigo' => 'A32', 'nombre' => 'A32'],
        ['codigo' => 'A33', 'nombre' => 'A33'],
        ['codigo' => 'A34', 'nombre' => 'A34'],
        ['codigo' => 'Hoja Control', 'nombre' => 'Hoja Control'],
        ['codigo' => 'Renombre archivo', 'nombre' => 'Renombre archivo']
    ],
    'SERIE BS' => [
        ['codigo' => 'Hoja Nombre', 'nombre' => 'Hoja Nombre'],
        ['codigo' => 'B', 'nombre' => 'B'],
        ['codigo' => 'B17', 'nombre' => 'B17'],
        ['codigo' => 'Hoja Control', 'nombre' => 'Hoja Control'],
        ['codigo' => 'Renombre archivo', 'nombre' => 'Renombre archivo']
    ],
    'SERIE BM' => [
        ['codigo' => 'Hoja Nombre', 'nombre' => 'Hoja Nombre'],
        ['codigo' => 'BM18', 'nombre' => 'BM18'],
        ['codigo' => 'BM18a', 'nombre' => 'BM18a'],
        ['codigo' => 'Hoja Control', 'nombre' => 'Hoja Control'],
        ['codigo' => 'Renombre archivo', 'nombre' => 'Renombre archivo']
    ],
    'SERIE P' => [
        ['codigo' => 'Hoja Nombre', 'nombre' => 'Hoja Nombre'],
        ['codigo' => 'P01', 'nombre' => 'P01'],
        ['codigo' => 'P02', 'nombre' => 'P02'],
        ['codigo' => 'P03', 'nombre' => 'P03'],
        ['codigo' => 'P04', 'nombre' => 'P04'],
        ['codigo' => 'P05', 'nombre' => 'P05'],
        ['codigo' => 'P06', 'nombre' => 'P06'],
        ['codigo' => 'P07', 'nombre' => 'P07'],
        ['codigo' => 'P09', 'nombre' => 'P09'],
        ['codigo' => 'P11', 'nombre' => 'P11'],
        ['codigo' => 'P12', 'nombre' => 'P12'],
        ['codigo' => 'P13', 'nombre' => 'P13'],
        ['codigo' => 'Hoja Control', 'nombre' => 'Hoja Control'],
        ['codigo' => 'Renombre archivo', 'nombre' => 'Renombre archivo']
    ],
    'SERIE ANEXO' => [
        ['codigo' => 'Hoja Nombre', 'nombre' => 'Hoja Nombre'],
        ['codigo' => 'Hoja Parto_RN', 'nombre' => 'Hoja Parto_RN'],
        ['codigo' => 'Hoja S_Infancia', 'nombre' => 'Hoja S_Infancia'],
        ['codigo' => 'Hoja I.T.S', 'nombre' => 'Hoja I.T.S'],
        ['codigo' => 'Hoja Rechazos', 'nombre' => 'Hoja Rechazos'],
        ['codigo' => 'Hoja Farmacia', 'nombre' => 'Hoja Farmacia'],
        ['codigo' => 'Hoja S_Mental', 'nombre' => 'Hoja S_Mental'],
        ['codigo' => 'Hoja S_Adolescencia', 'nombre' => 'Hoja S_Adolescencia'],
        ['codigo' => 'Hoja Laboratorio', 'nombre' => 'Hoja Laboratorio'],
        ['codigo' => 'Hoja Intercultural', 'nombre' => 'Hoja Intercultural'],
        ['codigo' => 'Hoja S_Familiar', 'nombre' => 'Hoja S_Familiar'],
        ['codigo' => 'Hoja Control', 'nombre' => 'Hoja Control'],
        ['codigo' => 'Renombre archivo', 'nombre' => 'Renombre archivo']
    ],
    'SERIE D' => [
        ['codigo' => 'Hoja Nombre', 'nombre' => 'Hoja Nombre'],
        ['codigo' => 'D15', 'nombre' => 'D15'],
        ['codigo' => 'D16', 'nombre' => 'D16'],
        ['codigo' => 'Hoja Control', 'nombre' => 'Hoja Control'],
        ['codigo' => 'Renombre archivo', 'nombre' => 'Renombre archivo']
    ]
];

// Meses del año
$MESES = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre'
];

// Colores de estados (para CSS)
$STATUS_COLORS = [
    ESTADO_PENDIENTE => 'amber',
    ESTADO_APROBADO => 'emerald',
    ESTADO_RECHAZADO => 'slate',
    ESTADO_ERROR => 'rose',
    ESTADO_JUSTIFICADO => 'sky'
];
