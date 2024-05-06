<?php

declare(strict_types=1);

namespace Woodlands\Core;

use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\User;

final class Auth
{
    /*
     * - At least 8 characters
     * - At least one uppercase letter
     * - At least one lowercase letter
     * - At least one number
    */
    private const PASSWORD_REGEX = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/";

    /**
     * @param array<int,\Woodlands\Core\Models\UserType> $allowed
     */
    public static function login(string $email, string $password, bool $remember, array $allowed): User
    {
        // Validate the email and password
        if(empty($email) || empty($password)) {
            throw new AppException("Both email and password are required", 400);
        }

        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if($email === false || !(str_ends_with($email, "@woodlands.ac.uk"))) {
            throw new AppException("Invalid email provided, must be in the format of an @woodlands.ac.uk email", 400);
        }

        if (empty($password) || strlen($password) < 6) {
            throw new AppException("Password is required and must be at least 6 characters", 400);
        }

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
