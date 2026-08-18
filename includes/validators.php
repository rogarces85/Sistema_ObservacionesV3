<?php
/**
 * Validadores para inputs - Edge case protection
 * Previene: textos muy largos, XSS, SQL injection, etc.
 */

class Validators
{
    // Límites de longitud para diferentes campos
    const MAX_STRING_LENGTH = 5000;
    const MAX_FIELD_LENGTH = 255;
    const MAX_TEXT_AREA = 10000;
    const MAX_NOMBRE = 150;
    const MAX_EMAIL = 255;
    const MAX_USERNAME = 50;

    /**
     * Validar string con límite de longitud
     */
    public static function validateString($value, $maxLength = self::MAX_FIELD_LENGTH, $fieldName = 'Campo')
    {
        if (is_null($value)) {
            return true;
        }

        $value = (string) $value;

        if (strlen($value) > $maxLength) {
            throw new Exception("$fieldName excede el límite de $maxLength caracteres");
        }

        return true;
    }

    /**
     * Validar nombre (con caracteres especiales españoles permitidos)
     * Soporta: acentos (á, é, í, ó, ú), ñ, diéresis (ü), y caracteres comunes
     */
    public static function validateName($value, $fieldName = 'Nombre')
    {
        self::validateString($value, self::MAX_NOMBRE, $fieldName);

        // Permitir caracteres Unicode de letras + números + espacios + guiones + puntos
        // Esto incluye automáticamente: á, é, í, ó, ú, ñ, ü, etc.
        if (!preg_match('/^[\p{L}\p{N}\s\-\.\']+$/u', $value)) {
            throw new Exception("$fieldName contiene caracteres no permitidos");
        }

        return true;
    }

    /**
     * Validar email
     */
    public static function validateEmail($value)
    {
        if (strlen($value) > self::MAX_EMAIL) {
            throw new Exception('Email excede el límite permitido');
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }

        return true;
    }

    /**
     * Validar que no esté vacío
     */
    public static function validateRequired($value, $fieldName = 'Campo')
    {
        if (empty($value)) {
            throw new Exception("$fieldName es requerido");
        }

        return true;
    }

    /**
     * Validar número entero
     */
    public static function validateInteger($value, $fieldName = 'Número')
    {
        if (!is_numeric($value) || intval($value) != $value) {
            throw new Exception("$fieldName debe ser un número entero");
        }

        return true;
    }

    /**
     * Validar fecha en formato YYYY-MM-DD
     */
    public static function validateDate($value, $fieldName = 'Fecha')
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            throw new Exception("$fieldName debe estar en formato YYYY-MM-DD");
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new Exception("$fieldName es inválida");
        }

        return true;
    }

    /**
     * Validar año
     */
    public static function validateYear($value)
    {
        $year = intval($value);
        $currentYear = intval(date('Y'));

        if ($year < 2020 || $year > $currentYear + 1) {
            throw new Exception("Año debe estar entre 2020 y " . ($currentYear + 1));
        }

        return true;
    }

    /**
     * Sanitizar array (XSS protection)
     */
    public static function sanitizeArray($array)
    {
        $sanitized = [];

        foreach ($array as $key => $value) {
            if (is_string($value)) {
                // Trimear y escapar HTML
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitizar un string
     */
    public static function sanitizeString($string)
    {
        return htmlspecialchars(trim((string) $string), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validar y sanitizar conjunto común
     */
    public static function validateAndSanitize($data, $rules = [])
    {
        $result = [];

        foreach ($rules as $field => $rule) {
            if (!isset($data[$field])) {
                if (strpos($rule, 'required') !== false) {
                    throw new Exception("Campo '$field' es requerido");
                }
                $result[$field] = null;
                continue;
            }

            $value = $data[$field];

            // Sanitizar siempre
            if (is_string($value)) {
                $value = self::sanitizeString($value);
            }

            // Validar según reglas
            if (strpos($rule, 'required') !== false) {
                self::validateRequired($value, $field);
            }

            if (strpos($rule, 'email') !== false) {
                self::validateEmail($value);
            }

            if (strpos($rule, 'integer') !== false) {
                self::validateInteger($value, $field);
            }

            if (strpos($rule, 'date') !== false) {
                self::validateDate($value, $field);
            }

            if (strpos($rule, 'year') !== false) {
                self::validateYear($value);
            }

            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                self::validateString($value, intval($matches[1]), $field);
            }

            $result[$field] = $value;
        }

        return $result;
    }
}
