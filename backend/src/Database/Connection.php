<?php

declare(strict_types=1);

namespace IndianConsular\Database;

use PDO;
use PDOException;
use Exception;

class Connection
{
    private static ?PDO $instance = null;

    public static function initialize(): void
    {
        if (self::$instance === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $port = $_ENV['DB_PORT'] ?? '3306';
                // $dbname = $_ENV['DB_NAME'] ?? 'indian_consular_services2';
                $dbname = $_ENV['DB_NAME'] ?? 'ics_test_db';
                $username = $_ENV['DB_USER'] ?? 'root';
                $password = $_ENV['DB_PASS'] ?? '';

                // Set connection timeout to prevent hanging (5 seconds)
                ini_set('default_socket_timeout', '5');
                
                error_log("Connection: Attempting to connect to MySQL at {$host}:{$port}, database: {$dbname}");

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4; SET time_zone = '+00:00'",
                    PDO::ATTR_TIMEOUT => 5  // Connection timeout in seconds
                ];

                $startTime = microtime(true);
                self::$instance = new PDO($dsn, $username, $password, $options);
                $connectionTime = round((microtime(true) - $startTime) * 1000, 2);
                error_log("Connection: Database connected successfully in {$connectionTime}ms");

            } catch (PDOException $e) {
                $errorMsg = "Database connection failed: " . $e->getMessage();
                error_log("Connection: " . $errorMsg);
                error_log("Connection: Error code: " . $e->getCode());
                throw new Exception($errorMsg);
            }
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            throw new Exception("Database not initialized");
        }

        return self::$instance;
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollback(): bool
    {
        return self::getInstance()->rollback();
    }

    public static function prepare(string $query): \PDOStatement
    {
        return self::getInstance()->prepare($query);
    }

    public static function query(string $query): \PDOStatement
    {
        return self::getInstance()->query($query);
    }

    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }
}
