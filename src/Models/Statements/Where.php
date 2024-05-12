<?php

namespace Woodlands\Core\Models\Statements;

use Woodlands\Core\Models\BaseModel;

final class Where
{
    public const AND = "AND";
    public const OR = "OR";
    public const LIKE = "LIKE";

    private BaseModel $model;

    private array $values = [];
    private array $predicates = [];
    private array $orderBy = [];
    private array $relationships = [];

    private array $pagination = ["page" => null, "perPage" => null];

    public function __construct(BaseModel $model, string $column, string $op, mixed  $value)
    {
        $this->model = $model;

        $this->add("", $column, $op, $value);
    }

    private function add(string $condition, string $column, string $op, mixed $value): void
    {
        $placeholder = $column;

        // If we have a value with the same name, we need to make sure we don't overwrite it , so we append a number to the placeholder
        if (array_key_exists($placeholder, $this->values)) {
            $placeholder .= "_".count($this->values);
        }

        $this->predicates[] = "{$condition} {$column} {$op} :{$placeholder}";
        $this->values[$placeholder] = $value;
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
        $placeholders = [];
        foreach ($values as $value) {
            $placeholder = $column ."_". count($this->values);
            $placeholders[] = ":$placeholder";
            $this->values[$placeholder] = $value;
        }

        $this->predicates[] = "$column IN (" . implode(", ", $placeholders) . ")";
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
        if($page < 1) {
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

    public function getConditions(): string
    {
        return implode(" ", $this->conditions);
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
     * @param array<int,mixed> $values
     * @return BaseModel[]
     */
    public function all(): array
    {
        return $this->model->whereAll($this, $this->values);
    }

    /**
     * @param array<int,mixed> $values
     */
    public function one(): BaseModel|null
    {
        return $this->model->whereOne($this, $this->values);
    }
}
