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

// Hojas REM por Serie
$HOJAS_POR_SERIE = [
    'SERIE A' => [
        'A01',
        'A02',
        'A03',
        'A04',
        'A05',
        'A06',
        'A07',
        'A08',
        'A09',
        'A11',
        'A11a',
        'A19a',
        'A19b',
        'A21',
        'A23',
        'A24',
        'A25',
        'A26',
        'A27',
        'A28',
        'A29',
        'A30',
        'A30ar',
        'A31',
        'A32',
        'A33',
        'A34',
        'Renombre archivo'
    ],
    'SERIE BS' => [
        'B',
        'B17',
        'Hoja Control',
        'Renombre archivo'
    ],
    'SERIE BM' => [
        'BM18',
        'BM18a',
        'Hoja Control',
        'Renombre archivo'
    ],
    'SERIE P' => [
        'P01',
        'P02',
        'P03',
        'P04',
        'P05',
        'P06',
        'P07',
        'P09',
        'P11',
        'P12',
        'P13',
        'Hoja Control',
        'Renombre archivo'
    ],
    'SERIE ANEXO' => [
        'Hoja Parto_RN',
        'Hoja S_Infancia',
        'Hoja I.T.S',
        'Hoja Rechazos',
        'Hoja Farmacia',
        'Hoja S_Mental',
        'Hoja S_Adolescencia',
        'Hoja Laboratorio',
        'Hoja Intercultural',
        'Hoja S_Familiar',
        'Hoja Control',
        'Renombre archivo'
    ],
    'SERIE D' => [
        'D15',
        'D16',
        'Hoja Control',
        'Renombre archivo'
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
