<?php

namespace Woodlands\Core\Models;

use DateTime;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Lib\Converter;
use Woodlands\Core\Models\BaseModel;

#[Table(name: "users", primaryKey: "user_id")]
class User extends BaseModel
{
    #[Column(name: "user_id")]
    protected string $id;

    #[Column(name: "email")]
    protected string $email;

    #[Column(name: "hashed_password")]
    private string $password;

    #[Column(name: "user_type", converter: [Converter::class, "toUserType"])]
    protected UserType $type;

    #[Column(
        name: "last_signed_in_at",
        nullable: true,
        converter: [Converter::class, "toDateTime"],
    )]
    protected ?DateTime $lastSignedInAt = null;

    #[Column(name: "created_at", converter: [Converter::class, "toDateTime"])]
    protected DateTime $createdAt;

    #[Column(name: "last_modified_at", converter: [Converter::class, "toDateTime"])]
    protected DateTime $modifiedAt;


    public function __construct(protected Connection $conn)
    {
        parent::__construct($conn);
    }

    public function __set(string $name, mixed $value): void
    {
        parent::__set($name, $value);
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
