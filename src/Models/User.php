<?php

declare(strict_types=1);

namespace Woodlands\Core\Models;

use DateTime;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Lib\Converter;
use Woodlands\Core\Models\BaseModel;
use Woodlands\Core\Models\Enums\UserType;

#[Table(name: "users", primaryKey: "user_id")]
class User extends BaseModel
{
    #[Column(name: "user_id")]
    protected int $id;

    #[Column(name: "email_address")]
    protected string $email;

    #[Column(name: "hashed_password", hideFromOutput: true)]
    protected string $password;

    #[Column(
        name: "user_type",
        encoder: [Converter::class, "fromUserType"],
        decoder: [Converter::class, "toUserType"]
    )]
    protected UserType $type;

    #[Column(
        name: "last_signed_in_at",
        nullable: true,
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected ?DateTime $lastSignedInAt = null;

    #[Column(
        name: "created_at",
        nullable: true,
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
    protected ?DateTime $modifiedAt;


    public function __construct(protected Connection $conn)
    {
        parent::__construct($conn);
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    protected function mapColumnsToProperties($data): void
    {
        parent::mapColumnsToProperties($data);
    }
}
