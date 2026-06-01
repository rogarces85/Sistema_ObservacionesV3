<?php
/**
 * Clase Database - Singleton PDO
 * Sistema de Observaciones REM - Servicio de Salud Osorno
 */

require_once __DIR__ . '/config.php';

class Database
{
    private static $instancia = null;
    private $conexion;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];

            $this->conexion = new PDO($dsn, DB_USER, DB_PASS, $opciones);

        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos. Por favor, contacte al administrador.");
        }
    }

    public static function obtenerInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function obtenerConexion()
    {
        return $this->conexion;
    }

    public function consultar($sql, $parametros = [])
    {
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($parametros);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en consultar: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta.");
        }
    }

    public function consultarUno($sql, $parametros = [])
    {
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($parametros);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en consultarUno: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta.");
        }
    }

    public function ejecutar($sql, $parametros = [])
    {
        try {
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute($parametros);
        } catch (PDOException $e) {
            error_log("Error en ejecutar: " . $e->getMessage());
            throw new Exception("Error al ejecutar la operación.");
        }
    }

    public function ultimoIdInsertado()
    {
        return $this->conexion->lastInsertId();
    }

    public function iniciarTransaccion()
    {
        return $this->conexion->beginTransaction();
    }

    public function confirmarTransaccion()
    {
        return $this->conexion->commit();
    }

    public function revertirTransaccion()
    {
        return $this->conexion->rollBack();
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new Exception("No se puede deserializar un singleton.");
    }
}
