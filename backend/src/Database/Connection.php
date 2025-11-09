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

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];

                self::$instance = new PDO($dsn, $username, $password, $options);

            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
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
