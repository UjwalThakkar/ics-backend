<?php

declare(strict_types=1);

namespace IndianConsular\Models;

use PDO;
use PDOException;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = $this->getConnection();
    }

    /**
     * Get database connection
     */
    private function getConnection(): PDO
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'ics_test_db';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';

        try {
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \Exception("Database connection failed");
        }
    }

    /**
     * Find record by primary key
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        $stmt = $this->query($sql, [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find record by a specific field
     */
    public function findBy(string $field, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = ? LIMIT 1";
        $stmt = $this->query($sql, [$value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find all records with optional conditions
     */
    public function findAll(array $conditions = [], string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert a new record
     */
    public function insert(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->query($sql, array_values($data));
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update record by primary key
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach ($data as $field => $value) {
            $fields[] = "{$field} = ?";
            $params[] = $value;
        }

        $params[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";
        $stmt = $this->query($sql, $params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Update record by a specific field
     */
    public function updateBy(string $field, $value, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $val) {
            $fields[] = "{$key} = ?";
            $params[] = $val;
        }

        $params[] = $value;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$field} = ?";
        $stmt = $this->query($sql, $params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete record by primary key
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete record by a specific field
     */
    public function deleteBy(string $field, $value): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$field} = ?";
        $stmt = $this->query($sql, [$value]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Count records with optional conditions
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Check if record exists by primary key
     */
    public function exists(int $id): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->query($sql, [$id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Check if record exists by field
     */
    public function existsBy(string $field, $value): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$field} = ?";
        $stmt = $this->query($sql, [$value]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Execute a custom query
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            throw new \Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->db->rollBack();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get table name
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get primary key name
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * Get database connection (for complex queries)
     */
    public function getDb(): PDO
    {
        return $this->db;
    }

    /**
     * Execute raw SQL query
     */
    public function raw(string $sql, array $params = []): \PDOStatement
    {
        return $this->query($sql, $params);
    }

    /**
     * Truncate table (use with caution!)
     */
    public function truncate(): bool
    {
        try {
            $this->db->exec("TRUNCATE TABLE {$this->table}");
            return true;
        } catch (PDOException $e) {
            error_log("Truncate failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get first record
     */
    public function first(array $conditions = []): ?array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " LIMIT 1";

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Paginate results
     */
    public function paginate(int $page = 1, int $perPage = 10, array $conditions = [], string $orderBy = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($conditions);
        $data = $this->findAll($conditions, $orderBy, $perPage, $offset);

        return [
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }

    /**
     * Pluck a single column
     */
    public function pluck(string $column, array $conditions = []): array
    {
        $sql = "SELECT {$column} FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Insert multiple records
     */
    public function insertBatch(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $fields = array_keys($data[0]);
        $placeholders = '(' . implode(', ', array_fill(0, count($fields), '?')) . ')';
        $allPlaceholders = implode(', ', array_fill(0, count($data), $placeholders));

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES {$allPlaceholders}";

        $params = [];
        foreach ($data as $row) {
            foreach ($fields as $field) {
                $params[] = $row[$field];
            }
        }

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
}