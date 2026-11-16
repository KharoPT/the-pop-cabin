<?php
/**
 * The Pop Cabin — Database connection
 * A tiny singleton wrapper around PDO so every class shares one connection.
 */

class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(
                'Database connection failed. Check DB_HOST / DB_NAME / DB_USER / DB_PASS in config/config.php, ' .
                'and make sure a database named "' . DB_NAME . '" exists with your tables imported. (' . $e->getMessage() . ')'
            );
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}
