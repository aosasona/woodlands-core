<?php

namespace Woodlands\Core\Lib;

use DateTime;
use Woodlands\Core\Models\UserType;

final class Converter
{
    public static function toUserType(string $type): UserType
    {
        return match($type) {
            "staff" => UserType::Staff,
            "student" => UserType::Student,
            default => throw new \Exception("Invalid user type"),
        };
    }

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
}
