<?php

declare(strict_types=1);

namespace Woodlands\Core\Models;

use DateTime;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Relationship;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Lib\Converter;
use Woodlands\Core\Models\BaseModel;

#[Table(name: "courses", primaryKey: "course_id")]
class Course extends BaseModel
{
    #[Column(name: "course_id")]
    protected int $id;

    #[Column(name: "name")]
    protected string $name;

    #[Column(name: "description", nullable: true)]
    protected ?string $description = null;

    #[Column(name: "department_id", nullable: true)]
    protected ?int $departmentId = null;

    #[Column(
        name: "start_date",
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected DateTime $startDate;

    #[Column(
        name: "end_date",
        nullable: true,
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected ?DateTime $endDate;

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

    #[Relationship(model: Department::class, property: "departmentId", parentColumn: "department_id")]
    protected ?Department $department;
}
