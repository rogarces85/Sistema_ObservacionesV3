<?php
/**
 * Clase CSRF
 * Protección contra ataques Cross-Site Request Forgery
 */

class CSRF
{
    /**
     * Generar token CSRF
     */
    public static function generateToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Validar token CSRF
     */
    public static function validateToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validar request y retornar error JSON si falla
     */
    public static function validateRequest()
    {
        $token = null;

        // Buscar token en POST o headers
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (!$token || !self::validateToken($token)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Token CSRF inválido o expirado'
            ]);
            exit;
        }

        return true;
    }
}
