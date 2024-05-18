<?php

declare(strict_types=1);

namespace Woodlands\Core\Models;

use DateTime;
use Woodlands\Core\Attributes\Column;
use Woodlands\Core\Attributes\Relationship;
use Woodlands\Core\Attributes\Table;
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Lib\Converter;
use Woodlands\Core\Models\BaseModel;
use Woodlands\Core\Models\Enums\Gender;

/** @psalm-suppress PropertyNotSetInConstructor */
#[Table(name: "staff", primaryKey: "staff_id")]
class Staff extends BaseModel
{
    public const ROLE_HOD = "Head of Department";

    #[Column(name: "staff_id")]
    protected int $id;

    #[Column(name: "first_name")]
    protected string $firstName;

    #[Column(name: "last_name")]
    protected string $lastName;

    #[Column(name: "role", nullable: true)]
    protected ?string $role = null;

    #[Column(
        name: "date_of_birth",
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected DateTime $dob;

    #[Column(
        name: "gender",
        encoder: [Converter::class, "fromGender"],
        decoder: [Converter::class, "toGender"]
    )]
    protected Gender $gender;

    #[Column(name: "user_id")]
    protected int $userId;

    #[Column(name: "department_id", nullable: true, baseTypeIsNullable: true)]
    protected ?int $departmentId = null;

    #[Column(
        name: "hired_on",
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected DateTime $hireDate;

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

    #[Relationship(model: User::class, property: "userId", parentColumn: "user_id")]
    protected User $user;


    public function __construct(protected Connection $conn)
    {
        parent::__construct($conn);
    }

    public function getFullName(): string
    {
        return $this->firstName . " " . $this->lastName;
    }

    public function getAge(): int
    {
        $now = new DateTime();
        $diff = $now->diff($this->dob);
        return $diff->y;
    }

    public function generateEmail(bool $withUniqueSuffix = false): string
    {
        // A SUFFIX is only needed if we already have an email in the database with similar email address, so we attach 3 random characters to the end of the email
        $suffix = $withUniqueSuffix ? substr(uniqid(), 0, 3) : "";
        return strtolower("{$this->firstName[0]}.{$this->lastName}{$suffix}@woodlands.ac.uk");
    }
}
