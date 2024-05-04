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

    public function toDateTime(string $date): DateTime
    {
        return new DateTime($date);
    }
}
