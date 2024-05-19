<?php

namespace Woodlands\Core\Models\Statements;

use Woodlands\Core\Models\BaseModel;

final class Where
{
    public const AND = "AND";
    public const OR = "OR";
    public const LIKE = "LIKE";

    /** 
     * @template T of BaseModel
     * @var T 
     */
    private BaseModel $model;

    /** @var array<string, array{value: mixed, skip_verify: bool}> */
    private array $values = [];

    private array $predicates = [];
    private array $orderBy = [];
    private array $relationships = [];

    private array $pagination = ["page" => null, "perPage" => null];

    public function __construct(BaseModel $model, string $column = "", string $op = "", mixed  $value = "", ?string $literalWhere = null)
    {
        $this->model = $model;

        if ($literalWhere !== null) {
            $this->predicates[] = $literalWhere;
            return;
        }

        $this->add("", $column, $op, $value);
    }

    private function add(string $condition, string $column, string $op, mixed $value): void
    {
        $placeholder = $column;
        $skip_verify = false;

        if (preg_match("/\./", $placeholder)) {
            $placeholder = str_replace("`", "", $placeholder);
            $placeholder = str_replace(".", "_", $placeholder);
            $skip_verify = true;
        }

        // If we have a value with the same name, we need to make sure we don't overwrite it , so we append a number to the placeholder
        if (array_key_exists($placeholder, $this->values)) {
            $placeholder .= "_" . count($this->values);
        }

        $this->predicates[] = "{$condition} {$column} {$op} :{$placeholder}";
        $this->values[$placeholder] = ["value" => $value, "skip_verify" => $skip_verify];
    }

    public function and(string $column, string $op, mixed $value): self
    {
        $this->add(self::AND, $column, $op, $value);
        return $this;
    }

    public function or(string $column, string $op, mixed $value): self
    {
        $this->add(self::OR, $column, $op, $value);
        return $this;
    }

    public function like(string $column, mixed $value): self
    {
        $this->add(self::LIKE, $column, "LIKE", $value);
        return $this;
    }

    /**
     * @param array<int,mixed> $values
     */
    public function in(string $column, array $values): self
    {
        $value = implode(", ", $values);
        $placeholder = preg_replace("/[^a-zA-Z0-9]/", "_", $column);
        $placeholder = preg_replace("/_+/", "_", $placeholder) . "_" . count($this->values);
        $this->predicates[] = "$column IN (:{$placeholder})";
        $this->values[$placeholder] = ["value" => $value, "skip_verify" => true];

        return $this;
    }

    public function orderBy(string $column, string $direction = "ASC"): self
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function with(string $relationship): self
    {
        $this->relationships[] = $relationship;
        return $this;
    }

    public function withRelations(string ...$relationships): self
    {
        $this->relationships = array_merge($this->relationships, $relationships);
        return $this;
    }

    public function paginate(int $page, int $perPage = 50): self
    {
        if ($page < 1) {
            $page = 1;
        }

        $this->pagination["page"] = $page;
        $this->pagination["perPage"] = $perPage;

        return $this;
    }

    public function getPredicates(): array
    {
        return $this->predicates;
    }

    public function getWhereClause(): string
    {
        return implode(" ", $this->predicates);
    }

    public function getOrderBy(): string
    {
        return implode(", ", $this->orderBy);
    }

    public function getRelations(): array
    {
        return array_unique($this->relationships);
    }

    /**
     * @return array{page: int|null, perPage: int|null}
     */
    public function getPagination(): array
    {
        return $this->pagination;
    }

    /**
     * @template T of BaseModel
     * @return T[]
     */
    public function all(): array
    {
        /** @var T[] */
        return $this->model->whereAll($this, $this->values);
    }

    /**
     * @template T of BaseModel
     * @return T
     */
    public function one(): mixed
    {
        /** @var T */
        return $this->model->whereOne($this, $this->values);
    }
}
