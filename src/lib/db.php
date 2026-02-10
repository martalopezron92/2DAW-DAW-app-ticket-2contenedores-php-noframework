<?php
declare(strict_types=1);

/**
 * Clase de gestión de base de datos
 * 
 * Implementa un patrón Singleton para garantizar una única conexión
 * a la base de datos durante toda la ejecución.
 */

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    /**
     * Constructor privado (patrón Singleton)
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Obtener instancia única de la clase
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establecer conexión con la base de datos
     */
    private function connect(): void
    {
        $maxRetries = 5;
        $retryDelay = 2; // segundos

        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                return; // Conexión exitosa
            } catch (PDOException $e) {
                if ($i === $maxRetries - 1) {
                    // Último intento fallido
                    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
                    throw new Exception('No se pudo conectar a la base de datos. Por favor, inténtelo más tarde.');
                }
                // Esperar antes de reintentar
                sleep($retryDelay);
            }
        }
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Ejecutar una consulta SELECT
     * 
     * @param string $sql Consulta SQL con placeholders
     * @param array $params Parámetros para bind
     * @return array Resultados de la consulta
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en query: ' . $e->getMessage());
            throw new Exception('Error al ejecutar la consulta.');
        }
    }

    /**
     * Ejecutar una consulta que retorna una sola fila
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Error en queryOne: ' . $e->getMessage());
            throw new Exception('Error al ejecutar la consulta.');
        }
    }

    /**
     * Ejecutar una consulta INSERT, UPDATE o DELETE
     * 
     * @return int Número de filas afectadas o ID del último insert
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            // Para INSERT, retornar el último ID insertado
            if (stripos(trim($sql), 'INSERT') === 0) {
                return (int) $this->connection->lastInsertId();
            }
            
            // Para UPDATE/DELETE, retornar filas afectadas
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Error en execute: ' . $e->getMessage());
            throw new Exception('Error al ejecutar la operación.');
        }
    }

    /**
     * Prevenir clonación
     */
    private function __clone() {}

    /**
     * Prevenir deserialización
     */
    public function __wakeup()
    {
        throw new Exception("No se puede deserializar un singleton.");
    }
}
