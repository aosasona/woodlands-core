<?php

declare(strict_types=1);

namespace Woodlands\Core\Models;

use DateTime;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Lib\Converter;
use Woodlands\Core\Models\BaseModel;

#[Table(name: "module_sessions", primaryKey: "session_id")]
class ModuleSession extends BaseModel
{
    #[Column(name: "session_id")]
    protected int $id;

    #[Column(name: "module_id")]
    protected int $moduleId;

    #[Column(name: "room_id", nullable: true)]
    protected ?int $roomId = null;

    #[Column(name: "day")]
    protected string $day;

    #[Column(name: "from_time")]
    protected string $startTime;

    #[Column(name: "to_time")]
    protected string $endTime;

    #[Column(
        name: "created_at",
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected DateTime $createdAt;

    #[Column(
        name: "last_modified_at",
        nullable: true,
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected ?DateTime $modifiedAt = null;
}
