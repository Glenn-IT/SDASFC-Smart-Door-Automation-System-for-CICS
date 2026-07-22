<?php

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if (empty($_SESSION['fp_admin_id']) || empty($_SESSION['fp_verified'])) {
    header('Location: ' . BASE_URL . '/forgot-password.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $error = AuthController::completePasswordReset($newPassword, $confirmPassword);

    if ($error === null) {
        header('Location: ' . BASE_URL . '/login.php?reset=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Smart Door Automation System for CICS</title>
    <link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-blob login-blob-1"></div>
    <div class="login-blob login-blob-2"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-banner">
                <div class="login-banner-shapes">
                    <span class="login-banner-shape login-banner-shape-1"></span>
                    <span class="login-banner-shape login-banner-shape-2"></span>
                    <span class="login-banner-shape login-banner-shape-3"></span>
                </div>
                <div class="login-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/><path d="M12 15v2"/>
                    </svg>
                </div>
            </div>

            <div class="login-body-content">
            <h4 class="login-title">Reset Password</h4>
            <p class="login-subtitle">Set a new password for your account.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="reset-password.php">
                <div class="login-field mb-3">
                    <label class="login-label" for="password">New Password</label>
                    <div class="login-input-group">
                        <span class="login-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                            </svg>
                        </span>
                        <input type="password" class="login-input" id="password" name="password" placeholder="At least 6 characters" required minlength="6">
                        <button type="button" class="login-toggle-btn" data-toggle-password="password">Show</button>
                    </div>
                </div>
                <div class="login-field mb-3">
                    <label class="login-label" for="confirm_password">Confirm New Password</label>
                    <div class="login-input-group">
                        <span class="login-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                            </svg>
                        </span>
                        <input type="password" class="login-input" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required minlength="6">
                        <button type="button" class="login-toggle-btn" data-toggle-password="confirm_password">Show</button>
                    </div>
                </div>
                <button type="submit" class="login-submit-btn w-100">Reset Password</button>
            </form>
            </div>
        </div>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
