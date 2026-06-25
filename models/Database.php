<?php
/**
 * Clase Database
 * Manejo de conexión PDO y operaciones de base de datos
 */

require_once __DIR__ . '/../config/config.php';

class Database
{
    private static $instance = null;
    private $connection;

    /**
     * Constructor privado para patrón Singleton
     */
    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . DB_CHARSET;
            }

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (Throwable $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos. Por favor, contacte al administrador.");
        }
    }

    /**
     * Obtener instancia única de la base de datos (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Ejecutar una consulta SELECT y retornar todos los resultados
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en query: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta.");
        }
    }

    /**
     * Ejecutar una consulta SELECT y retornar un solo resultado
     */
    public function queryOne($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en queryOne: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta.");
        }
    }

    /**
     * Ejecutar una consulta INSERT/UPDATE/DELETE
     */
    public function execute($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error en execute: " . $e->getMessage());
            throw new Exception("Error al ejecutar la operación.");
        }
    }

    /**
     * Obtener el ID del último registro insertado
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback()
    {
        return $this->connection->rollBack();
    }

    /**
     * Prevenir clonación del objeto
     */
    private function __clone()
    {
    }

    /**
     * Prevenir deserialización del objeto
     */
    public function __wakeup()
    {
        throw new Exception("No se puede deserializar un singleton.");
    }
}
