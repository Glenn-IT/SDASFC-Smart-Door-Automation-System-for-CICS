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
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#fff" viewBox="0 0 16 16">
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
                </svg>
            </div>

            <h4 class="login-title">Smart Door Automation System for CICS</h4>

            <?php if (!empty($_GET['reset'])): ?>
                <div class="alert alert-success py-2">Password reset successfully. Please log in.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="mb-3">
                    <label class="form-label" for="username">User Name</label>
                    <div class="login-input-group">
                        <span class="login-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#fff" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control login-input" id="username" name="username" required autofocus
                            <?= $lockSeconds > 0 ? 'disabled' : '' ?>>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="login-input-group">
                        <span class="login-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#fff" viewBox="0 0 16 16">
                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2Zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/>
                            </svg>
                        </span>
                        <input type="password" class="form-control login-input" id="password" name="password" required
                            <?= $lockSeconds > 0 ? 'disabled' : '' ?>>
                        <button type="button" class="btn login-toggle-btn" data-toggle-password="password"
                            <?= $lockSeconds > 0 ? 'disabled' : '' ?>>Show</button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember-me" <?= $lockSeconds > 0 ? 'disabled' : '' ?>>
                        <label class="form-check-label" for="remember-me">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="small">Forgot Password?</a>
                </div>

                <button type="submit" class="btn login-submit-btn w-100" id="login-btn"
                    <?= $lockSeconds > 0 ? 'disabled data-lockout-seconds="' . $lockSeconds . '"' : '' ?>>LOGIN</button>
            </form>
        </div>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
