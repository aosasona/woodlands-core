<?php

declare(strict_types=1);

namespace Woodlands\Core;

use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\User;

final class Auth
{
    /**
     * @param array<int,\Woodlands\Core\Models\UserType> $allowed
     */
    public static function login(string $email, string $password, bool $remember, array $allowed): User
    {
        /** @var ?User $user **/
        $user = User::new()->where("email", "=", $email)->one();
        if(empty($user)) {
            throw new AppException("User not found", 400);
        }

        if(!$user->verifyPassword($password)) {
            throw new AppException("Invalid password", 400);
        }

        if(!in_array($user->type, $allowed)) {
            throw new AppException("You do not have sufficient permissons to access this area", 403);
        }

        $_SESSION["user_id"] = $user->id;
        if($remember) {
            setcookie("user_id", $user->id, time() + 3600 * 24 * 30, "/"); // remember for 30 days
        }

        return $user;
    }

    public static function logout(): void
    {
        unset($_SESSION["user_id"]);
        setcookie("user_id", "", time() - 3600, "/");
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION["user_id"]) || isset($_COOKIE["user_id"]);
    }

    public static function user(): ?User
    {
        $userId = $_SESSION["user_id"] ?? $_COOKIE["user_id"] ?? null;
        if(empty($userId)) {
            return null;
        }

        if(empty($_SESSION["user_id"])) {
            $_SESSION["user_id"] = $userId;
        }

        return User::new()->findById($userId);
    }
}
