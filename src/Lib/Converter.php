<?php

namespace Woodlands\Core\Lib;

use DateTime;
use Woodlands\Core\Models\Enums\{Gender, UserType};

final class Converter
{
    // We could technically use the "tryFrom" method on the enums but this is a more explicit way of doing it and guarantee we do not get a null, an exceptio is preferred in this case
    public static function toUserType(string $type): UserType
    {
        return match($type) {
            "staff" => UserType::Staff,
            "student" => UserType::Student,
            default => throw new \Exception("Invalid user type"),
        };
    }

    // The ->value method on backed enums are also usable here but we want to make sure nothing added to the enum is not handled in the way we explicitly want
    public static function fromUserType(UserType $type): string
    {
        return match($type) {
            UserType::Staff => "staff",
            UserType::Student => "student",
        };
    }

    public static function toDateTime(string $date): DateTime
    {
        return new DateTime($date);
    }

    public static function fromDateTime(DateTime $date): string
    {
        return $date->format("Y-m-d H:i:s");
    }

    public static function toGender(string $gender): string
    {
        return match($gender) {
            "male" => Gender::Male,
            "female" => Gender::Female,
            "others" => Gender::Others,
            default => throw new \Exception("Invalid gender provided, got $gender")
        };
    }

    public static function fromGender(Gender $gender): string
    {
        return match ($gender) {
            Gender::Male => "male",
            Gender::Female => "female",
            Gender::Others => "others",
            default => throw new \Exception("Invalid gender provided, got $gender")
        };
    }
}
