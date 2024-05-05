<?php

declare(strict_types=1);

namespace Woodlands\Core\Database;

use InvalidArgumentException;
use PDO;
use PDOStatement;

class Connection
{
    private static ?self $instance = null;
    private PDO $connection;

    private function __construct(string $dsn, string $username, string $password)
    {
        $this->connection = new PDO($dsn, $username, $password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public static function getInstance(string $dsn = "", string $username = "", string $password = ""): self
    {
        // Automatically load database credentials from the environment if they are not provided
        $dsn = $dsn === "" ? $_ENV["DSN"] ?? "" : $dsn;
        $username = $username === "" ? $_ENV["DB_USER"] ?? "" : $username;
        $password = $password === "" ? $_ENV["DB_PASSWORD"] ?? "" : $password;

        if ($dsn === "") {
            throw new InvalidArgumentException("DSN cannot be empty");
        }

        if (self::$instance == null) {
            self::$instance = new self($dsn, $username, $password);
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * @return array<string>
     */
    public function getTables(): array
    {
        $sql =  "SELECT table_name FROM information_schema.tables WHERE table_schema = 'woodlands'";
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * @param array<int,mixed> $params
     */
    public function query(string $sql, array $params = []): ?PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt ?: null;
    }
}
