<?php
/**
 * API de Locations
 * Endpoints para comunas y establecimientos
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Location.php';

// Función para responder en JSON
function jsonResponse($success, $data = null, $message = '', $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar autenticación (opcional para este endpoint, pero recomendado)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(false, null, 'No autorizado', 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$type = $_GET['type'] ?? $_GET['action'] ?? '';
$comunaId = $_GET['comunaId'] ?? $_GET['comuna_id'] ?? null;
$comunaNombre = $_GET['comuna_nombre'] ?? null;

try {
    $locationModel = new Location();

    if ($method === 'GET') {
        switch ($type) {
            case 'comunas':
                $comunas = $locationModel->getAllComunas();
                jsonResponse(true, $comunas, 'Comunas obtenidas exitosamente');
                break;

            case 'establecimientos':
                if ($comunaId) {
                    $establecimientos = $locationModel->getEstablecimientosByComuna($comunaId);
                } elseif ($comunaNombre) {
                    $comuna = $locationModel->getComunaByNombre($comunaNombre);
                    if ($comuna) {
                        $establecimientos = $locationModel->getEstablecimientosByComuna($comuna['id']);
                    } else {
                        $establecimientos = [];
                    }
                } else {
                    $establecimientos = $locationModel->getAllEstablecimientos();
                }
                jsonResponse(true, $establecimientos, 'Establecimientos obtenidos exitosamente');
                break;

            default:
                jsonResponse(false, null, 'Tipo no válido. Use: comunas o establecimientos', 400);
        }
    } else {
        jsonResponse(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API locations: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
