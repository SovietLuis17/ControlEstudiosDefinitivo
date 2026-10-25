<?php
// app/Config/Database.php
// Clase que gestiona la conexion a la base de datos usando PDO
// Soporta variables de entorno en produccion con valores por defecto para desarrollo local
// Oculta trazas sensibles en caso de fallo (SEC-06)

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private string $host;
    private string $db;
    private string $user;
    private string $pass;
    private string $charset = 'utf8mb4';
    private ?PDO $pdo = null;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') !== false ? getenv('DB_HOST') : '127.0.0.1';
        $this->db   = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'controlEstudios';
        $this->user = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
        $this->pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

        $this->connect();
    }

    // Metodo privado que crea la conexion PDO segura
    private function connect(): void
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

        // Opciones de PDO: errores como excepciones, fetch asociativo, sin emular prepares
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Registramos el error tecnico en el log privado del servidor (SEC-06)
            error_log('[ControlEstudio DB Error] ' . $e->getMessage());
            // Lanzamos mensaje generico sin filtrar credenciales ni estructura interna
            throw new PDOException('Error al conectar con la base de datos del sistema.');
        }
    }

    // Metodo publico que devuelve la conexion activa
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}