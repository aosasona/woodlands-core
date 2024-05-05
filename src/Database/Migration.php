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

    public function apply(bool $debug = false): void
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
    /**
     * @param string $name - The name of the migration to rollback, this can also be set to "last" or "all"
     * @param bool $debug
     * @param bool $dryRun - If set to true, the migration will not be reverted, this is useful for testing, it is enabled by default to prevent accidental data loss
     * @return ?array
     */
    public function rollback(string $name, bool $debug = false, bool $dryRun = true): array
    {
        $this->setDebug($debug);

        if (!$this->migrationsTableExists()) {
            $this->logError("Migrations table does not exist");
            return null;
        }

        $completedMigrations = $this->loadCompletedMigrations();
        if (count($completedMigrations) === 0) {
            $this->log("No migrations to revert");
            return null;
        }

        $migrationsToRevert = match ($name) {
            "last" => [end($completedMigrations)],
            "all" => $completedMigrations,
            default => [$name],
        };

        if (count($migrationsToRevert) === 0) {
            $this->logError("No migrations found to revert");
            return null;
        }

        // temporarily disable debug mode if dry run is enabled to avoid printing unnecessary logs
        $this->setDebug($debug && !$dryRun);
        $allMigrations = $this->getAllMigrations(skipCompleted: false);
        $this->setDebug($debug);
        if ($allMigrations === null) {
            $this->logError("No migrations found");
            return null;
        }

        if ($dryRun) {
            $this->log("Dry run enabled, no changes were made");
            return $migrationsToRevert;
        }

        foreach (array_reverse($migrationsToRevert) as $migration) {
            if (!array_key_exists($migration, $allMigrations)) {
                $this->logError("Migration not found: " . $migration);
                continue;
            }

            $this->revertMigration($migration, $allMigrations[$migration]["down"]);
        }

        return $migrationsToRevert;
    }

    private function applyMigration(string $name, string $sql): bool
    {
        $this->logInfo("Applying migration: " . $name);
        try {

            foreach (self::splitIntoStatements($sql) as $sql) {
                if ($this->pdo->exec($sql) === false) {
                    throw new \PDOException("Failed to execute SQL statement");
                }
            }

            $this->logMigration($name);
            return true;
        } catch (\PDOException $e) {
            $errInfo = $this->pdo->errorInfo();
            $this->logError("Failed to apply migration: " . $name . "\n> Reason: ".(end($errInfo) ?? $e->getMessage()));

            return false;
        }
    }

    private function revertMigration(string $name, string $sql): void
    {
        $this->logInfo("Reverting migration: " . $name);
        try {

            foreach (self::splitIntoStatements($sql) as $sql) {
                if ($this->pdo->exec($sql) === false) {
                    throw new \PDOException("Failed to execute SQL statement");
                }
            }

            $this->pdo->exec("DELETE FROM " . self::MIGRATIONS_TABLE . " WHERE name = '" . $name . "'");
        } catch (\PDOException $e) {
            $errInfo = $this->pdo->errorInfo();
            $this->logError("Failed to apply migration: " . $name . "\n> Reason: ".(end($errInfo) ?? $e->getMessage()));
        }
    }

    /**
     *  This splits the content of a migration file into multiple statements so that we can have multiple SQL statements in a single file
     * @return array<int, string>
     */
    private static function splitIntoStatements(string $file_content): array
    {
        $statements = [];
        $current_statement = "";
        $lines = explode("\n", $file_content);

        foreach ($lines as $line) {
            if(empty(trim($line))) {
                continue;
            }

            // if we reach a line that ends with a semicolon, we should consider it as a complete statement and move on to the next one
            if (preg_match("/;$/", trim($line))) {
                $current_statement .= $line;
                $statements[] = $current_statement;
                $current_statement = "";
                continue;
            }

            // if we reach the `-- split` comment, we should split the current statement (i.e mark as complete) and start a new one
            if (preg_match("/--(\s+)?split/i", $line)) {
                $statements[] = $current_statement;
                $current_statement = "";
                continue;
            }

            $current_statement .= $line;
            $current_statement .= "\n";
        }

        // Add the last statement as long as it is not empty
        if(!empty($current_statement)) {
            $statements[] = $current_statement;
        }

        return array_filter($statements, fn ($statement) => !empty(trim($statement)));
    }

    /**
     * @param array<string,array<"up"|"down", string>> $migrations
     */
    private function applyMigrations(array $migrations): void
    {
        $this->log("Applying " . count($migrations) . " migration" . (count($migrations) > 1 ? "s" : ""));

        foreach ($migrations as $name => $files) {
            if (!$this->applyMigration($name, $files["up"])) {
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
        $tables = array_column(column_key: "table_name", array: $tables);
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
    private function groupMigrationsByName(array $files, bool $skipCompleted = true): ?array
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

            if (in_array($name, $completed) && $skipCompleted) {
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

    private function getAllMigrations(bool $skipCompleted = true): ?array
    {
        $files = scandir(self::MIGRATIONS_DIR);

        if ($files == false) {
            $last_error = error_get_last() ?? ["message" => "Unknown error"];
            $this->logError("Unable to read migration files: " . $last_error["message"]);
            return null;
        }

        $migrations = $this->groupMigrationsByName($files, $skipCompleted);
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