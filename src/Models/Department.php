<?php

declare(strict_types=1);

namespace Woodlands\Core\Models;

use DateTime;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Lib\Converter;
use Woodlands\Core\Models\BaseModel;

/** @psalm-suppress PropertyNotSetInConstructor */
#[Table(name: "departments", primaryKey: "department_id")]
class Department extends BaseModel
{
    #[Column(name: "department_id")]
    protected int $id;

    #[Column(name: "name")]
    protected string $name;

    #[Column(name: "description", nullable: true)]
    protected ?string $description = null;

    #[Column(
        name: "created_at",
        nullable: true,
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected ?DateTime $createdAt;

    #[Column(
        name: "last_modified_at",
        nullable: true,
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected ?DateTime $modifiedAt = null;
}
