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

#[Table(name: "students", primaryKey: "student_id")]
class Student extends BaseModel
{
    #[Column(name: "student_id")]
    protected int $id;

    #[Column(name: "first_name")]
    protected string $firstName;

    #[Column(name: "last_name")]
    protected string $lastName;

    #[Column(
        name: "date_of_birth",
        nullable: true,
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected DateTime $dob;


    #[Column(name: "department_id")]
    protected int $departmentId;

    #[Column(name: "nationality")]
    protected string $nationality;


    #[Column(
        name: "gender",
        encoder: [Converter::class, "fromGender"],
        decoder: [Converter::class, "toGender"]
    )]
    protected Gender $gender;

    #[Column(name: "user_id")]
    protected int $userId;

    #[Column(
        name: "enrolled_at",
        encoder: [Converter::class, "fromDateTime"],
        decoder: [Converter::class, "toDateTime"]
    )]
    protected DateTime $enrolledAt;

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
}
