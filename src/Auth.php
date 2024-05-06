<?php

declare(strict_types=1);

namespace Woodlands\Core;

use Woodlands\Core\Models\User;

final class Auth
{
    /**
     * @param array<int,\Woodlands\Core\Models\UserType> $allowed
     */
    public static function login(string $email, string $password, array $allowed): User
    {

    }
}
