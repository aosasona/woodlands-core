<?php

declare(strict_types=1);

namespace Woodlands\Core;

use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\Enums\UserType;
use Woodlands\Core\Models\Staff;
use Woodlands\Core\Models\Student;
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
     * @param array<int,\Woodlands\Core\Models\Enums\UserType> $allowed
     */
    public static function login(string $email, string $password, bool $remember, array $allowed): User
    {
        // Validate the email and password
        if (empty($email) || empty($password)) {
            throw new AppException("Both email and password are required", 400);
        }

        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($email === false || !(str_ends_with($email, "@woodlands.ac.uk"))) {
            throw new AppException("Invalid email provided, must be in the format of an @woodlands.ac.uk email", 400);
        }

        if (empty(trim($password))) {
            throw new AppException("Password is required", 400);
        }

        /** @var ?User $user **/

        $user = User::new()->where("email_address", "=", $email)->one();
        if (empty($user)) {
            throw new AppException("Invalid credentials provided", 400);
        }

        if (!$user->verifyPassword($password)) {
            throw new AppException("Invalid credentials provided", 400);
        }

        if (!in_array($user->type, $allowed)) {
            throw new AppException("You do not have sufficient permissons to access this area", 403);
        }

        // Update the last signed in time
        $user->lastSignedInAt = new \DateTime("now");
        $user->save();


        $_SESSION["user_id"] = $user->id;
        if ($remember) {
            setcookie("user_id", (string)$user->id, time() + 3600 * 24 * 30, "/"); // remember for 30 days
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
    /**
     * @param array<int,\Woodlands\Core\Models\Enums\UserType> $allowed
     * @param string $login_path
     */
    public static function requireLogin(array $allowed, string $login_path = "/sign-in"): void
    {
        if (!self::isLoggedIn()) {
            header("Location: $login_path");
            exit;
        }

        $user = self::user();
        if (!in_array($user?->type, $allowed)) {
            throw new AppException("You do not have sufficient permissons to access this area", 403);
        }
    }

    public static function getOwner(): Student|Staff|null
    {
        /** @var User $user */
        $user = self::user();
        if (!$user) {
            throw new AppException("No user signed in!", 403);
        }

        if (empty($user->getID())) {
            throw new AppException("Invalid user signed in!", 403);
        }

        return match ($user->type) {
            UserType::Student => Student::new()->findById($user->getID()),
            UserType::Staff => Staff::new()->findById($user->getID()),
            default => null
        };
    }

    public static function user(): ?User
    {
        $userId = $_SESSION["user_id"] ?? $_COOKIE["user_id"] ?? null;
        if (empty($userId)) {
            return null;
        }

        if (empty($_SESSION["user_id"])) {
            $_SESSION["user_id"] = $userId;
        }

        return User::new()->findById($userId);
    }
}
