<?php

declare(strict_types=1);

namespace Woodlands\Core\Models;

use DateTime;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Lib\Converter;
use Woodlands\Core\Models\BaseModel;

#[Table(name: "classrooms", primaryKey: "classroom_id")]
class Classroom extends BaseModel
{
    #[Column(name: "classroom_id")]
    protected int $id;

    #[Column(name: "room_code")]
    protected string $roomCode;

    #[Column(name: "capacity")]
    protected int $capacity;

    #[Column(
        name: "added_on",
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected DateTime $addedOn;

    #[Column(
        name: "last_modified_at",
        nullable: true,
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected ?DateTime $modifiedAt = null;
}
