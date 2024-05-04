<?php

namespace Woodlands\Core\Models;

use ReflectionClass;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Database\Connection;

// This class makes heavy use of attributes, while there are performance implications to using attributes, they are deemed acceptable in this case for the speed of development and convenienve they provide amongst all developers in this project.
abstract class BaseModel
{
    private string $tableName;
    private string $primaryKey;
    private static array $columnNames = [];
    private array $changedColumns = [];

    public function __set(string $name, mixed $value): void
    {
        // Prevent updating the primary key
        if ($name === $this->primaryKey) {
            return;
        }

        // If the column had a previous value and has been instantiated, then it has been changed
        if (property_exists($this, $name) && isset($this->$name) && !in_array($name, $this->changedColumns)) {
            $this->changedColumns[] = $name;
        }

        $this->$name = $value;
    }

    public function __construct(protected Connection $conn)
    {
        $attributes = $this->loadMeta();
        $this->tableName = $attributes?->name ?? "";
        $this->primaryKey = $attributes?->primaryKey ?? "";

        if($this->tableName === "" || $this->primaryKey === "") {
            throw new \Exception("Model class must have a #[Model] attribute with tableName and primaryKey properties");
        }
    }

    /**
     * Get the primary key
     */
    public function getID(): mixed
    {
        return $this->{$this->primaryKey};
    }

    /**
     * This function fetches a record from the database by the primary key.
     */
    public function find(mixed $id): void
    {
        // TODO: implement
    }

    /**
     * This function fetches all records from the database.
     */
    public function all(): void
    {
        // TODO: implement
    }

    /**
     * This function creates a new record in the database if the primary key is not set, otherwise it updates the record.
     */
    public function save(): void
    {
        // TODO: implement
    }

    /**
    * @return array<\Woodlands\Core\Attributes\Column>
    * @throws \ReflectionException
    * @throws \Exception
    */
    public static function getColumns(): array
    {
        $properties = (new ReflectionClass(static::class))->getProperties();
        $columns = [];

        if (empty($properties)) {
            throw new \Exception("Model class must have at least one #[Column] attribute");
        }

        foreach($properties as $property) {
            $attributes = $property->getAttributes();
            if(empty($attributes)) {
                continue;
            }

            $column_attributes = $property->getAttributes("Woodlands\Core\Attributes\Column");

            if(empty($column_attributes)) {
                continue;
            }

            if(count($column_attributes) > 1) {
                throw new \Exception("Model class can only have one #[Column] attribute per property");
            }

            $column = $column_attributes[0]->newInstance();
            $columns[] = $column;
        }

        return $columns;
    }

    /**
     * Get all column names from the model from a previously cached array or from the model's meta
     */
    private static function getColumnNames(): array
    {
        $columns = array_filter(
            (new ReflectionClass(static::class))->getProperties(),
            function ($property) {
                $column_attributes = $property->getAttributes("Woodlands\Core\Attributes\Column");
                return count($column_attributes) > 0;
            }
        );

        if (count($columns) !== count(self::$columnNames)) {
            self::$columnNames = array_map(function ($column) {
                return $column->name;
            }, self::getColumns());
        }

        return self::$columnNames;
    }


    private static function loadMeta(): Table
    {
        $attributes = (new ReflectionClass(static::class))->getAttributes();
        if(count($attributes) === 0) {
            throw new \Exception("Model class must have a #[Model] attribute");
        }

        return $attributes[0]->newInstance();
    }

    //////////////////// DEBUG FUNCTIONS ////////////////////
    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getChangedColumns(): array
    {
        return $this->changedColumns;
    }
}
