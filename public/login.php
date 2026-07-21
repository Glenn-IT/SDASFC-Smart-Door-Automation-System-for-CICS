<?php

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if (Auth::isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $error = AuthController::attemptLogin($username, $password);

    if ($error === null) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

$lockSeconds = AuthController::getLoginLockSeconds();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Smart Door Automation System for CICS</title>
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
                    <img src="assets/img/logo.jpg" alt="SDASFC Logo" class="login-avatar-img">
                </div>
            </div>

            <div class="login-body-content">
            <h4 class="login-title">Smart Door Automation System for CICS</h4>
            <p class="login-subtitle">Sign in to manage your building access</p>

            <?php if (!empty($_GET['reset'])): ?>
                <div class="alert alert-success py-2">Password reset successfully. Please log in.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="login-field mb-3">
                    <label class="login-label" for="username">Username</label>
                    <div class="login-input-group">
                        <span class="login-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input type="text" class="login-input" id="username" name="username" placeholder="Enter your username" required autofocus
                            <?= $lockSeconds > 0 ? 'disabled' : '' ?>>
                    </div>
                </div>
                <div class="login-field mb-3">
                    <label class="login-label" for="password">Password</label>
                    <div class="login-input-group">
                        <span class="login-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                            </svg>
                        </span>
                        <input type="password" class="login-input" id="password" name="password" placeholder="Enter your password" required
                            <?= $lockSeconds > 0 ? 'disabled' : '' ?>>
                        <button type="button" class="login-toggle-btn" data-toggle-password="password"
                            <?= $lockSeconds > 0 ? 'disabled' : '' ?>>Show</button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 login-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember-me" <?= $lockSeconds > 0 ? 'disabled' : '' ?>>
                        <label class="form-check-label" for="remember-me">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="login-link">Forgot password?</a>
                </div>

                <button type="submit" class="login-submit-btn w-100" id="login-btn"
                    <?= $lockSeconds > 0 ? 'disabled data-lockout-seconds="' . $lockSeconds . '"' : '' ?>>Log In</button>
            </form>
            </div>
        </div>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
