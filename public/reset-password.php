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
    <title>Reset Password - SDASFC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow-sm" style="width: 100%; max-width: 420px;">
            <div class="card-body p-4">
                <h4 class="card-title mb-1 text-center">Reset Password</h4>
                <p class="text-muted text-center mb-4">Set a new password for your account.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="reset-password.php">
                    <div class="mb-3">
                        <label class="form-label" for="password">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="password">Show</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="confirm_password">Show</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
