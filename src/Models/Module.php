<?php

declare(strict_types=1);

namespace Woodlands\Core\Models;

use DateTime;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Lib\Converter;
use Woodlands\Core\Models\BaseModel;

#[Table(name: "modules", primaryKey: "module_id")]
class Module extends BaseModel
{
    #[Column(name: "module_id")]
    protected int $id;

    #[Column(name: "name")]
    protected string $name;

    #[Column(name: "code")]
    protected string $code;

    #[Column(name: "description", nullable: true)]
    protected ?string $description = null;

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
