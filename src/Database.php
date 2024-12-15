<?php

namespace App;

class Database
{
    private static array $pool = []; // Connection pool
    private PDO $pdo;
    private ?PDOStatement $stmt = null;

    private function __construct(string $dsn, string $username, string $password, array $options = [])
    {
        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => true, // Enable persistent connections
        ];
        $options = array_replace($defaultOptions, $options);
        $this->pdo = new PDO($dsn, $username, $password, $options);
    }

    /**
     * Get a connection from the pool or create a new one if necessary.
     */
    public static function getInstance(string $dsn, string $username, string $password, array $options = []): self
    {
        if (count(self::$pool) >= self::$maxPoolSize) {
            throw new RuntimeException('Connection pool size limit reached');
        }

        $poolKey = md5($dsn . $username . serialize($options));
        if (!isset(self::$pool[$poolKey])) {
            self::$pool[$poolKey] = new self($dsn, $username, $password, $options);
        }

        return self::$pool[$poolKey];
    }

    /**
     * Execute a query and return results (or true for non-SELECT queries).
     */
    public function query(string $sql, array $params = []): bool|array
    {
        $this->stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $this->stmt->bindValue(is_int($key) ? $key + 1 : $key, $value);
        }

        $this->stmt->execute();

        if (preg_match('/^(SELECT|SHOW|DESCRIBE|EXPLAIN)/i', $sql)) {
            return $this->stmt->fetchAll();
        }

        return true;
    }

    public function execute(string $sql, array $params = []): bool
    {
        $this->stmt = $this->pdo->prepare($sql);
        return $this->stmt->execute($params);
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Release the connection back to the pool.
     */
    public function release(): void
    {
        // Optionally, close or recycle resources here
    }

    public static function cleanupIdleConnections(): void
    {
        foreach (self::$pool as $key => $connection) {
            // Check for idle status and unset if necessary
            unset(self::$pool[$key]); // Example cleanup logic
        }
    }
}
