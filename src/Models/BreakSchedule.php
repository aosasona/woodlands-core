<?php

declare(strict_types=1);

namespace Woodlands\Core\Models;

use DateTime;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Lib\Converter;
use Woodlands\Core\Models\BaseModel;

#[Table(name: "school_break_schedule", primaryKey: "schedule_id")]
class BreakSchedule extends BaseModel
{
    #[Column(name: "schedule_id")]
    protected int $id;

    #[Column(name: "name")]
    protected string $name;

    #[Column(
        name: "from",
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected DateTime $from;

    #[Column(
        name: "to",
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected DateTime $to;

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
    protected ?DateTime $modifiedAt;
}
