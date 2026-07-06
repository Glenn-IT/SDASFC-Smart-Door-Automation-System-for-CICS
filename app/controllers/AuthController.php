<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Admin.php';

class AuthController
{
    private const MAX_LOGIN_ATTEMPTS = 3;
    private const LOGIN_LOCKOUT_SECONDS = 30;

    /**
     * Seconds remaining before the login button unlocks. 0 if not locked.
     */
    public static function getLoginLockSeconds(): int
    {
        $lockUntil = $_SESSION['login_lock_until'] ?? null;

        if (!$lockUntil) {
            return 0;
        }

        $remaining = $lockUntil - time();

        if ($remaining <= 0) {
            unset($_SESSION['login_lock_until'], $_SESSION['login_attempts']);

            return 0;
        }

        return $remaining;
    }

    public static function attemptLogin(string $username, string $password): ?string
    {
        if (self::getLoginLockSeconds() > 0) {
            return 'Too many failed attempts. Please wait before trying again.';
        }

        $admin = Admin::findByUsername($username);

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

            if ($_SESSION['login_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
                $_SESSION['login_lock_until'] = time() + self::LOGIN_LOCKOUT_SECONDS;
                $_SESSION['login_attempts'] = 0;

                return 'Too many failed attempts. Please wait before trying again.';
            }

            return 'Invalid username or password.';
        }

        unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
        Auth::login($admin);

        return null;
    }

    /**
     * Step 1 of forgot-password: look up the admin and start the reset
     * session. Returns an error string, or null on success.
     */
    public static function startPasswordReset(string $username): ?string
    {
        $admin = Admin::findByUsername($username);

        if (!$admin || !$admin['security_question']) {
            return 'No account with a security question found for that username.';
        }

        $_SESSION['fp_admin_id'] = $admin['id'];
        $_SESSION['fp_question'] = $admin['security_question'];
        $_SESSION['fp_attempts'] = 0;
        $_SESSION['fp_verified'] = false;

        return null;
    }

    /**
     * Step 2 of forgot-password: the user picks which question is theirs
     * and answers it. Both must match for verification to succeed. Returns
     * an error string, or null on success.
     */
    public static function checkSecurityAnswer(string $question, string $answer): ?string
    {
        $adminId = $_SESSION['fp_admin_id'] ?? null;

        if (!$adminId) {
            return 'Session expired. Please start over.';
        }

        $questionMatches = hash_equals($_SESSION['fp_question'] ?? '', $question);

        if ($questionMatches && Admin::verifySecurityAnswer((int) $adminId, $answer)) {
            $_SESSION['fp_verified'] = true;

            return null;
        }

        $_SESSION['fp_attempts'] = ($_SESSION['fp_attempts'] ?? 0) + 1;

        if ($_SESSION['fp_attempts'] >= 3) {
            self::clearPasswordResetSession();

            return 'Too many incorrect attempts. Please start over.';
        }

        return 'Incorrect question or answer. Please try again.';
    }

    /**
     * Step 3 of forgot-password: set the new password once the security
     * answer has been verified. Returns an error string, or null on success.
     */
    public static function completePasswordReset(string $newPassword, string $confirmPassword): ?string
    {
        $adminId = $_SESSION['fp_admin_id'] ?? null;
        $verified = $_SESSION['fp_verified'] ?? false;

        if (!$adminId || !$verified) {
            return 'Session expired. Please start over.';
        }

        if (strlen($newPassword) < 6) {
            return 'Password must be at least 6 characters.';
        }

        if ($newPassword !== $confirmPassword) {
            return 'Passwords do not match.';
        }

        Admin::updatePassword((int) $adminId, $newPassword);
        self::clearPasswordResetSession();

        return null;
    }

    public static function clearPasswordResetSession(): void
    {
        unset($_SESSION['fp_admin_id'], $_SESSION['fp_question'], $_SESSION['fp_attempts'], $_SESSION['fp_verified']);
    }
}
