<?php

require_once __DIR__ . '/../core/Database.php';

class Admin
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM admins WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        return $admin ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM admins WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $admin = $stmt->fetch();

        return $admin ?: null;
    }

    public static function verifyPassword(int $id, string $password): bool
    {
        $admin = self::findById($id);

        return $admin && password_verify($password, $admin['password_hash']);
    }

    public static function verifySecurityAnswer(int $id, string $answer): bool
    {
        $admin = self::findById($id);

        if (!$admin || !$admin['security_answer_hash']) {
            return false;
        }

        return password_verify(self::normalizeAnswer($answer), $admin['security_answer_hash']);
    }

    public static function updateFullName(int $id, string $fullName): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE admins SET full_name = ? WHERE id = ?'
        );
        $stmt->execute([$fullName, $id]);
    }

    public static function updatePassword(int $id, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = Database::getConnection()->prepare(
            'UPDATE admins SET password_hash = ? WHERE id = ?'
        );
        $stmt->execute([$hash, $id]);
    }

    public static function updateSecurityQA(int $id, string $question, string $answer): void
    {
        $hash = password_hash(self::normalizeAnswer($answer), PASSWORD_DEFAULT);
        $stmt = Database::getConnection()->prepare(
            'UPDATE admins SET security_question = ?, security_answer_hash = ? WHERE id = ?'
        );
        $stmt->execute([$question, $hash, $id]);
    }

    private static function normalizeAnswer(string $answer): string
    {
        return strtolower(trim($answer));
    }
}
