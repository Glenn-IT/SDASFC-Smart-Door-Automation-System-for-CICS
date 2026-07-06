<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/SecurityQuestions.php';
require_once __DIR__ . '/../models/Admin.php';

class ProfileController
{
    public static function updateFullName(int $adminId, string $fullName): ?string
    {
        $fullName = trim($fullName);

        if ($fullName === '') {
            return 'Full name is required.';
        }

        Admin::updateFullName($adminId, $fullName);
        $_SESSION['admin_full_name'] = $fullName;

        return null;
    }

    public static function changePassword(int $adminId, string $currentPassword, string $newPassword, string $confirmPassword): ?string
    {
        if (!Admin::verifyPassword($adminId, $currentPassword)) {
            return 'Current password is incorrect.';
        }

        if (strlen($newPassword) < 6) {
            return 'New password must be at least 6 characters.';
        }

        if ($newPassword !== $confirmPassword) {
            return 'New passwords do not match.';
        }

        Admin::updatePassword($adminId, $newPassword);

        return null;
    }

    public static function updateSecurityQA(int $adminId, string $currentPassword, string $question, string $answer): ?string
    {
        if (!Admin::verifyPassword($adminId, $currentPassword)) {
            return 'Current password is incorrect.';
        }

        if (!in_array($question, SecurityQuestions::LIST, true)) {
            return 'Please choose a valid security question.';
        }

        if (trim($answer) === '') {
            return 'Security answer is required.';
        }

        Admin::updateSecurityQA($adminId, $question, $answer);

        return null;
    }
}
