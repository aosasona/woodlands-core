<?php

namespace Woodlands\Core\Database;

use PDO;

class Migration
{
    private bool $debug = false;

    private PDO $pdo;

    public const MIGRATIONS_DIR = __DIR__ . "/../../data/migrations";

    public const MIGRATIONS_TABLE = "__migrations";

    public const MIGRATIONS_TABLE_MYSQL_SQL = <<<SQL
    CREATE TABLE IF NOT EXISTS __migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    SQL;

    private function __construct(private Connection $connection)
    {
        $this->pdo = $connection->getConnection();
    }

    public static function new(Connection $connection): self
    {
        return new self($connection);
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    public function run(bool $debug = false): void
    {
        $this->setDebug($debug);

        if (!$this->migrationsTableExists()) {
            $this->createMigrationsTable();
        }

        $migrations = $this->getAllMigrations();
        if ($migrations === null) {
            if ($this->debug) {
                echo "No migrations found\n";
            }

            return;
        }

        if (count($migrations) === 0) {
            $this->log("No migrations to apply");
            return;
        }

        $this->applyMigrations($migrations);
    }

    private function applyMigration(string $name, string $sql): bool
    {
        $this->logInfo("Applying migration: " . $name);
        try {

            $this->pdo->exec($sql);
            $this->logMigration($name);

            return true;
        } catch (\PDOException $e) {
            $this->logError("Failed to apply migration: " . $name);
            $this->logError($e->getMessage());

            return false;
        }
    }

    private function revertMigration(string $name, string $sql): void
    {
        $this->logInfo("Reverting migration: " . $name);
        try {

            $this->pdo->exec($sql);
            $this->pdo->exec("DELETE FROM " . self::MIGRATIONS_TABLE . " WHERE name = '" . $name . "'");
        } catch (\PDOException $e) {
            $this->logError("Failed to revert migration: " . $name);
            $this->logError($e->getMessage());
        }
    }

    /**
     * @param array<string,array<"up"|"down", string>> $migrations
     */
    private function applyMigrations(array $migrations): void
    {
        $this->log("Applying " . count($migrations) . " migration" . (count($migrations) > 1 ? "s" : ""));

        foreach ($migrations as $name => $files) {
            if (!$this->applyMigration($name, $files["up"])) {
                $this->logError("Failed to apply migration: " . $name);
                $this->revertMigration($name, $files["down"]);
                return; // we don't want to continue if a migration fails, it is critical that we stop there and then
            }
        }

        $this->log("> All migrations applied");
    }

    private function createMigrationsTable(): void
    {
        $this->pdo->exec(self::MIGRATIONS_TABLE_MYSQL_SQL);
    }

    private function migrationsTableExists(): bool
    {
        $tables = $this->connection->getTables();
        return in_array(self::MIGRATIONS_TABLE, $tables);
    }

    /**
     * @return array|bool
     */
    private function loadCompletedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT name FROM " . self::MIGRATIONS_TABLE);
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?? [];
    }

    /*
     * @var array<string> $files
     * * @param array<int,mixed> $files
     */
    private function groupMigrationsByName(array $files): ?array
    {
        $completed = $this->loadCompletedMigrations();

        $migrations = [];
        foreach ($files as $file) {
            if ($file === "." || $file === "..") {
                continue;
            }

            $name = preg_replace("/.(up|down).sql$/", "", $file);

            if ($name === null || $name == false) {
                $this->logError("Invalid migration file: " . $file);
                return null;
            } elseif (is_array($name)) {
                $name = $name[0] ?? "";
            }

            if (in_array($name, $completed)) {
                if (str_ends_with($file, ".up.sql")) {
                    $this->logInfo("Skipping completed migration: " . $name);
                }
                continue;
            }

            if (!array_key_exists($name, $migrations)) {
                $migrations[$name] = ["up" => null, "down" => null];
            }

            if (preg_match("/.up.sql$/", $file)) {
                $migrations[$name]["up"] = $file;
            } elseif (preg_match("/.down.sql$/", $file)) {
                $migrations[$name]["down"] = $file;
            }
        }

        return $migrations;
    }
    /**
     * @param array<int,mixed> $migrations
     */
    private function resolveMigrationFiles(array &$migrations): void
    {
        foreach ($migrations as $name => $files) {
            if ($files["up"] === null || $files["down"] === null) {
                $this->logError("Invalid migration files for: " . $name);
                $this->logError("Missing: " . ($files["up"] === null ? "up" : "down"));
                unset($migrations[$name]);

                // if we unable to resolve even one migration file, we should clean up the state and make sure we don't apply any migrations
                $migrations = [];
                return;
            }

            $up = file_get_contents(self::MIGRATIONS_DIR . "/" . $files["up"]);
            $migrations[$name]["up"] = match ($up) {
                false => null,
                default => $up
            };

            $down = file_get_contents(self::MIGRATIONS_DIR . "/" . $files["down"]);
            $migrations[$name]["down"] = match ($down) {
                false => null,
                default => $down
            };

            $this->log("Loaded migration: " . $name);
        }
    }

    private function getAllMigrations(): ?array
    {
        $files = scandir(self::MIGRATIONS_DIR);

        if ($files == false) {
            $last_error = error_get_last() ?? ["message" => "Unknown error"];
            $this->logError("Unable to read migration files: " . $last_error["message"]);
            return null;
        }

        $migrations = $this->groupMigrationsByName($files);
        if ($migrations === null) {
            return null;
        }

        $this->resolveMigrationFiles($migrations);
        ksort($migrations);

        return $migrations;
    }

    private function log(string $message): void
    {
        if ($this->debug) {
            printf("\033[32m%s\033[0m\n", $message);
        }
    }

    private function logError(string $message): void
    {
        if ($this->debug) {
            printf("\033[31m%s\033[0m\n", $message);
        }
    }

    private function logInfo(string $message): void
    {
        if ($this->debug) {
            printf("\033[33m%s\033[0m\n", $message);
        }
    }

    private function logMigration(string $name): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO " . self::MIGRATIONS_TABLE . " (name) VALUES (:name)");
        $stmt->execute([":name" => $name]);
    }
}
