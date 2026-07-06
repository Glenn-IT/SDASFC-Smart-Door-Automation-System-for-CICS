<?php

require_once __DIR__ . '/../config/config.php';

class Auth
{
    public static function login(array $admin): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_full_name'] = $admin['full_name'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public static function requireAdmin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }

    public static function currentAdminName(): string
    {
        return $_SESSION['admin_full_name'] ?? '';
    }
}
