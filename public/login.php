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
    <title>Admin Login - SDASFC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow-sm" style="width: 100%; max-width: 380px;">
            <div class="card-body p-4">
                <h4 class="card-title mb-1 text-center">SDASFC</h4>
                <p class="text-muted text-center mb-4">Smart Door Automation System</p>

                <?php if (!empty($_GET['reset'])): ?>
                    <div class="alert alert-success py-2">Password reset successfully. Please log in.</div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="login.php">
                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="password">Show</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="login-btn"
                        <?= $lockSeconds > 0 ? 'disabled data-lockout-seconds="' . $lockSeconds . '"' : '' ?>>Log In</button>
                </form>

                <div class="text-center mt-3">
                    <a href="forgot-password.php" class="small">Forgot password?</a>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
