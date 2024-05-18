<?php

declare(strict_types=1);

namespace Woodlands\Core\Models;

use PDO;
use ReflectionClass;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Relationship;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Exceptions\DecodingException;
use Woodlands\Core\Exceptions\ModelException;
use Woodlands\Core\Models\Statements\Where;
use JsonSerializable;

// This class makes heavy use of attributes, while there are performance implications to using attributes, they are deemed acceptable in this case for the speed of development and convenienve they provide amongst all developers in this project.
abstract class BaseModel implements JsonSerializable
{
    private string $tableName;
    private string $primaryKey;

    private array $changedColumns = [];

    /** @var array<string, \Woodlands\Core\Attributes\Column> **/
    private static array $cachedColumns = [];

    /** @var array<string, string> **/
    private static array $cachedColumnNames = [];

    /** @var array<string, \Woodlands\Core\Attributes\Relationship> **/
    private static array $cachedRelationships = [];

    public function __get(string $name): mixed
    {
        if (!property_exists($this, $name)) {
            throw new \Exception("Property $name does not exist");
        }

        // Intercept relationships and return the related model
        // This is done by checking if the property has a Relationship attribute and if it does, we return the related model
        if(array_key_exists($name, self::getRelationships()) && !isset($this->$name)) {
            $data = $this->lazyLoadrelationship($name);

            // Cache the relationship
            $this->$name = $data;
            return $data;
        }

        return $this->$name;
    }

    // All fields need to be protected or private (and extend __get and __set to allow access to them) and enable tracking of upates to all fields
    // WARNING: using public fields will bypass the __set method and will not track changes, causing the `->update()` method to not work as expected
    public function __set(string $name, mixed $value): void
    {
        if(!property_exists($this, $name)) {
            throw new \Exception("Property `$name` does not exist on model `".static::class."`");
        }

        // If we have not set the column before, then it is a "new" value
        // If the value is the same as the previous value, then it is not changed
        // To distinuish between a new modek and a changed model, we need to check if it has a primary key
        $isUpdate = $this->getID() !== null;
        $valueHasChanged = (($this->$name ?? null) !== $value);
        if (property_exists($this, $name) && !in_array($name, $this->changedColumns) && $isUpdate && $valueHasChanged) {
            $this->changedColumns[] = $name;
        }

        $this->$name = $value;
    }

    public function __construct(protected Connection $conn)
    {
        $attributes = $this->loadMeta();
        $this->tableName = $attributes->name;
        $this->primaryKey = $attributes->primaryKey;

        if($this->tableName === "" || $this->primaryKey === "") {
            throw new \Exception("Model class must have a #[Model] attribute with tableName and primaryKey properties");
        }
    }

    public static function new(?Connection $conn = null): self
    {
        /** @psalm-suppress UnsafeInstantiation */
        return new static($conn ?? Connection::getInstance());
    }

    /**
     * Get the primary key
     */
    public function getID(): mixed
    {
        $columns = self::getColumnNames();
        $primaryKeyProperty = array_search($this->primaryKey, $columns);
        return $this->{$primaryKeyProperty} ?? null;
    }

    private function setID(mixed $id): void
    {
        $columns = self::getColumnNames();
        $primaryKeyProperty = array_search($this->primaryKey, $columns);
        $this->{$primaryKeyProperty} = (int)$id;
    }

    /**
     * This function fetches a record from the database by the primary key.
     */
    public function findById(mixed $id): ?self
    {
        $columns = $this->getColumnsString();
        $stmt = $this->conn->getConnection()->prepare("SELECT $columns FROM `{$this->tableName}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute([":id" => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if($data === false) {
            return null;
        }

        $this->mapColumnsToProperties($data);
        return $this;
    }

    public function findByColumn(string $column, mixed $value): ?self
    {
        if(!in_array($column, self::getColumnNames())) {
            throw new ModelException("Column $column does not exist on model `".static::class."`");
        }

        $columns = $this->getColumnsString();
        $stmt = $this->conn->getConnection()->prepare("SELECT $columns FROM `{$this->tableName}` WHERE `$column` = :value LIMIT 1");
        $stmt->execute([":value" => $value]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if($data === false) {
            return null;
        }

        $this->mapColumnsToProperties($data);
        return $this;
    }

    public function where(string $col, string $op, mixed $value): Where
    {
        return new Where($this, $col, $op, $value);
    }

    /**
     * @param array<int,mixed> $values
     */
    public function whereOne(Where $where, array $values): ?self
    {
        return $this->execGenericWhere($where, $values, 1)[0] ?? null;
    }

    /**
     * @return BaseModel[]
     * @param array<int,mixed> $values
     */
    public function whereAll(Where $where, array $values): array
    {
        return $this->execGenericWhere($where, $values, 0);
    }

    /**
     * @return Woodlands\Core\Models\BaseModel[]
     * @param array<int,mixed> $values
     */
    private function execGenericWhere(Where $where, array $values, int $count): array
    {
        $joins = "";
        $columns = $this->getColumnsString();
        $db_columns = array_values(self::getColumnNames());

        foreach($values as $key => $value) {
            // replace keys like "foo_bar_1" with "foo_bar"
            $key = preg_replace('/_[0-9]+$/', '', $key);
            if (in_array($key, $db_columns)) {
                continue;
            }

            $closest = $this->findClosestColumn($key, $db_columns);
            throw new ModelException("Column `{$key}` does not exist on table `{$this->tableName}`, did you mean `{$closest}`?");
        }

        $relations = $where->getRelations();
        // Add the relationship columns to the columns to fetch
        // TODO: optimise this, this is a bit of an inefficient way to do this
        foreach($relations as $relation) {
            $relationship = self::getRelationships()[$relation] ?? null;
            if($relationship == null) {
                throw new ModelException("Relationship $relation does not exist on model `".static::class."`");
            }

            /** @var BaseModel $model */
            $relation_model = new $relationship->model($this->conn);
            $relation_columns = [$relationship->model, "getColumns"]();
            $relation_columns = array_values(array_map(fn ($col) => $col->name, $relation_columns));
            $relation_columns = array_map(fn ($column) => "`{$relation_model->tableName}`.`$column` AS `{$relation}_irn_$column`", $relation_columns);
            $columns .= ", ".implode(", ", $relation_columns);

            $foreignKeyColumn = $this->getColumnNames()[$relationship->property] ?? null;
            if ($foreignKeyColumn == null) {
                throw new ModelException("Column {$relationship->property} does not exist on model `".static::class."`");
            }

            $joins .= " LEFT JOIN `{$relation_model->tableName}` ON `{$this->tableName}`.`{$foreignKeyColumn}` = `{$relation_model->tableName}`.`{$relationship->parentColumn}`";
        }

        $pagination = $where->getPagination();
        $pagination_clause = "";
        if ($pagination["page"] !== null && $pagination["perPage"] !== null) {
            if($count >= 1) {
                throw new ModelException("Cannot use pagination with count, provided a limit of $count but also attempted to paginate");
            }

            $offset = ($pagination["page"] - 1) * $pagination["perPage"];
            $pagination_clause = "LIMIT $offset, {$pagination["perPage"]}";
        }

        $sql = "SELECT $columns FROM `{$this->tableName}`{$joins} WHERE {$where->getWhereClause()} {$where->getOrderBy()}";

        if ($count >= 1) {
            $sql .= " LIMIT $count";
        } elseif ($pagination_clause !== "") {
            $sql .= " $pagination_clause";
        }

        $stmt = $this->conn->getConnection()->prepare($sql);
        $stmt->execute($values);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($data == false) {
            return [];
        }

        return $this->decodeMany($data);
    }
    /**
     * @param array<int,mixed> $data
     * @return BaseModel[]
     */
    public function decodeMany(array $data): array
    {
        $models = [];

        foreach($data as $row) {
            /** @psalm-suppress UnsafeInstantiation */
            $model = new static($this->conn);
            $model->mapColumnsToProperties($row);
            $models[] = $model;
        }

        return $models;
    }

    /**
     * This function fetches all records from the database.
     * @return array<self>
     */
    public function all(): array
    {
        $columns = $this->getColumnsString();
        $sql = "SELECT {$columns} FROM `{$this->tableName}`";
        $stmt = $this->conn->getConnection()->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->decodeMany($data);
    }

    public function encodePropertiesForPersistence(Column $column, mixed $value): mixed
    {
        if(is_scalar($value)) {
            return $value;
        }

        if($column->encoder == null) {
            throw new DecodingException("Column $column->name must be a scalar value or provide an encoder");
        }

        $encoder = $column->encoder;
        if (!is_callable($encoder)) {
            throw new DecodingException("Encoder for column $column->name is not callable");
        }

        return $encoder($value);

    }

    protected function insert(): self
    {
        $columnsToInsert = [];

        // Make sure that all non-nullable columns are set
        foreach(self::getColumns() as $propertyName => $column) {
            if($column->name === $this->primaryKey) {
                continue;
            }

            if ($column->nullable === false && (!isset($this->$propertyName) || empty($this->$propertyName))) {
                throw new ModelException("Model property `{$propertyName}` ({$column->name}) on model `".$this->tableName."` cannot be null");
            }

            if (!isset($this->$propertyName)) {
                continue;
            }

            // Ensure it is a scalar value
            $value = $this->encodePropertiesForPersistence($column, $this->$propertyName);

            $columnsToInsert[$column->name] = $value;
        }

        $columns = implode(", ", array_keys($columnsToInsert));
        $placeholders = implode(", ", array_map(fn ($column) => ":$column", array_keys($columnsToInsert)));
        $sql = "INSERT INTO `{$this->tableName}` ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->getConnection()->prepare($sql);
        $stmt->execute($columnsToInsert);

        $this->setID($this->conn->getConnection()->lastInsertId());

        return $this;
    }

    private function update(): self
    {
        if(empty($this->changedColumns)) {
            return $this;
        }

        $columnsToUpdate = [];

        // Make sure the updated columns retain their integrity
        foreach($this->changedColumns as $propertyName) {
            $column = self::getColumns()[$propertyName];
            $value = $this->$propertyName;

            // Ensure it is a scalar value
            $value = $this->encodePropertiesForPersistence($column, $this->$propertyName);

            $columnsToUpdate[$column->name] = $value;
        }

        $columns = implode(", ", array_map(fn ($column) => "`$column` = :$column", array_keys($columnsToUpdate)));
        $sql = "UPDATE `{$this->tableName}` SET $columns WHERE `{$this->primaryKey}` = :{$this->primaryKey} LIMIT 1";
        $stmt = $this->conn->getConnection()->prepare($sql);
        $columnsToUpdate[$this->primaryKey] = $this->getID();
        $stmt->execute($columnsToUpdate);

        return $this;
    }

    /**
     * This function creates a new record in the database if the primary key is not set, otherwise it updates the record.
     */
    public function save(?array $forceUpdate = null): self
    {
        if ($forceUpdate !== null) {
            $this->changedColumns = $forceUpdate;
        }

        // We need to determine if we are inserting or updating, and if we can't, bail early
        if($this->getID() == null && !empty($this->changedColumns)) {
            throw new ModelException("Cannot update a record that has not been saved");
        }

        return match($this->getID()) {
            null => $this->insert(),
            default => $this->update(),
        };
    }

    public function delete(): self
    {
        $stmt = $this->conn->getConnection()->prepare("DELETE FROM `{$this->tableName}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute([":id" => $this->getID()]);
        return $this;
    }

    /**
     * @param array<int,mixed> $params
     * @return array|bool
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->conn->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<int,mixed> $params
     */
    public function exec(string $sql, array $params = []): int
    {
        $stmt = $this->conn->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }


    /**
     * @param array<string,mixed> $data
     */
    public function mapColumnsToProperties(array $data): void
    {
        $data = $data ?: [];
        $columns = self::getColumns();
        foreach ($columns as $propertyName => $column) {
            $value = $data[$column->name] ?? null;

            // If the base type is not nullable and we haven't expicitly set the column to be nullable, then we should throw an error if the value is null
            // Else, if we have a default and we have specified this column to be nullable even though the base type is not, then we should set the value to the default
            if($value == null && !$column->baseTypeIsNullable) {
                if (!$column->nullable || $column->default == null) {
                    throw new DecodingException("Column $column->name cannot be null on model `".static::class."`");
                }

                $value = $column->default;
            }

            // Skip if the value is null and the column is nullable
            if($value == null) {
                $this->$propertyName = null;
                continue;
            }

            // Run the converter if it exists and the type of the data and the properties are not the same already
            if($column->decoder != null) {
                $converter = $column->decoder;
                if (!is_callable($converter)) {
                    throw new DecodingException("Converter for column $column->name is not callable");
                }
                $this->$propertyName = $converter($value);
            } else {
                $this->$propertyName = $value;
            }
        }

        // Handle relationships if present
        foreach(self::getRelationships() as $relationProperty => $relationship) {
            $prefix = "{$relationProperty}_irn_";

            // Extract data related to the relationship alone
            // columns are prefixed with the name of the relationship and `irn` to signify that this is a relationship column
            $relationship_data = array_filter($data, function ($key) use ($prefix) {
                return str_starts_with($key, $prefix); // we use `irn` to signify that this is a relationship column
            }, ARRAY_FILTER_USE_KEY);

            // If we have no data for the relationship, then we can skip this
            // $relationship_data = array_filter($relationship_data);
            if (empty($relationship_data)) {
                continue;
            }

            // Strip the prefix from the column names
            foreach($relationship_data as $key => $value) {
                $relationship_data[str_replace($prefix, "", $key)] = $value;
                unset($relationship_data[$key]);
            }

            /** @var self $model */
            $model = new $relationship->model($this->conn);

            // If the model's primary key is null here, skip this relationship data, as it means we have no data to work with for this relationship
            if(empty($relationship_data[$model->primaryKey])) {
                $this->{$relationProperty} = null;
                continue;
            }

            $model->mapColumnsToProperties($relationship_data);
            $this->{$relationProperty} = $model;
        }
    }

    /**
     * @param array<int,mixed> $columns
     */
    private function findClosestColumn(string $column, array $columns): string
    {
        $closest = null;
        $shortest = -1;

        foreach($columns as $col) {
            $lev = levenshtein($column, $col);
            if ($lev == 0) {
                $closest = $col;
                $shortest = 0;
                break;
            }

            if ($lev <= $shortest || $shortest < 0) {
                $closest = $col;
                $shortest = $lev;
            }
        }

        return $closest;
    }

    private function lazyLoadrelationship(string $name): mixed
    {
        $relationship = self::getRelationships()[$name];
        // If our foreign key does not exist, then we cannot load the relationship
        if(!isset($this->{$relationship->property})) {
            return null;
        }

        /** @var self $model */
        $model = new $relationship->model($this->conn);
        $model = $model->where($relationship->parentColumn, "=", $this->{$relationship->property});

        if($relationship->cardinality === Relationship::SINGLE) {
            return $model->one();
        }

        return $model->all();
    }

    /**
    * @return array<string, \Woodlands\Core\Attributes\Relationship>
    * @throws \ReflectionException
    * @throws \Exception
    */
    public static function getRelationships(): array
    {
        $properties = array_filter(
            (new ReflectionClass(static::class))->getProperties(),
            function ($property) {
                $relationship_attributes = $property->getAttributes(Relationship::class);
                return count($relationship_attributes) > 0;
            }
        );

        if (count($properties) == count(self::$cachedRelationships)) {
            return self::$cachedRelationships;
        }

        $relationships = [];

        foreach($properties as $property) {
            $relationship_attributes = $property->getAttributes(Relationship::class);
            if(empty($relationship_attributes)) {
                continue;
            }

            if(count($relationship_attributes) > 1) {
                throw new \Exception("Model class can only have one #[Relationship] attribute per property");
            }

            $relationship = $relationship_attributes[0]->newInstance();
            $relationships[$property->getName()] = $relationship;
        }

        return $relationships;
    }


    /**
    * @return array<string, \Woodlands\Core\Attributes\Column>
    * @throws \ReflectionException
    * @throws \Exception
    */
    public static function getColumns(): array
    {
        $properties = array_filter(
            (new ReflectionClass(static::class))->getProperties(),
            function ($property) {
                $column_attributes = $property->getAttributes(Column::class);
                return count($column_attributes) > 0;
            }
        );

        if (count($properties) == count(self::$cachedColumns)) {
            return self::$cachedColumns;
        }

        $columns = [];

        if (empty($properties)) {
            throw new \Exception("Model class must have at least one #[Column] attribute");
        }

        foreach($properties as $property) {
            $column_attributes = $property->getAttributes(Column::class);
            if(empty($column_attributes)) {
                continue;
            }

            if(count($column_attributes) > 1) {
                throw new \Exception("Model class can only have one #[Column] attribute per property");
            }

            $column = $column_attributes[0]->newInstance();
            $column->baseTypeIsNullable = $property->getType()->allowsNull();
            $columns[$property->getName()] = $column;
        }

        return $columns;
    }


    /**
     * Get all column names from the model from a previously cached array or from the model's meta
     * @return string[]
     */
    private static function getColumnNames(): array
    {
        $columns = self::getColumns();
        if (count($columns) == count(self::$cachedColumnNames)) {
            return self::$cachedColumnNames;
        }

        $columnNames = array_map(fn ($column) => $column->name, $columns);
        self::$cachedColumnNames = $columnNames;
        return $columnNames;
    }

    private function getColumnsString(): string
    {
        $cols = implode("`, ".$this->tableName.".`", self::getColumnNames());
        return "{$this->tableName}.`$cols`";
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

    public function toJSON(bool $includeNullFields = false): string
    {
        $data = [];
        $columns = self::getColumns();

        foreach($columns as $propertyName => $column) {
            if($column->hideFromOutput || (!isset($this->$propertyName) && !$includeNullFields)) {
                continue;
            }

            if ($this->$propertyName instanceof \DateTime) {
                $data[$column->name] = $this->$propertyName->format("Y-m-d H:i:s");
            } else {
                $data[$column->name] = $this->$propertyName ?? null;
            }
        }

        foreach(self::getRelationships() as $propertyName => $relation) {
            if(!isset($this->$propertyName)) {
                continue;
            }

            $data[$propertyName] = $this->$propertyName ?? null;
        }

        return json_encode($data);
    }

    public function __toString(): string
    {
        return $this->toJSON();
    }

    public function __debugInfo(): array
    {
        return json_decode($this->toJSON(), true);
    }

    public function __serialize(): array
    {
        return json_decode($this->toJSON(), true);
    }

    /**
     * @param array<int,mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->mapColumnsToProperties($data);
    }

    public function __isset(string $name): bool
    {
        return !empty($this->$name);
    }

    public function jsonSerialize(): array
    {
        return json_decode($this->toJSON(), true);
    }

}
