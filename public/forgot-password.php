<?php

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/SecurityQuestions.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$error = null;

// Step 1: username submitted -> look up account + security question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $error = AuthController::startPasswordReset(trim($_POST['username']));
}

// Step 2: security question + answer submitted -> verify both match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $error = AuthController::checkSecurityAnswer($_POST['security_question'] ?? '', $_POST['answer']);

    if ($error === null) {
        header('Location: ' . BASE_URL . '/reset-password.php');
        exit;
    }
}

$step = !empty($_SESSION['fp_admin_id']) ? 2 : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Smart Door Automation System for CICS</title>
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
            <h4 class="login-title">Forgot Password</h4>
            <p class="login-subtitle">
                <?= $step === 1 ? 'Enter your username to continue.' : 'Answer your security question.' ?>
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <form method="post" action="forgot-password.php">
                    <div class="login-field mb-3">
                        <label class="login-label" for="username">Username</label>
                        <div class="login-input-group">
                            <span class="login-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>
                            <input type="text" class="login-input" id="username" name="username" placeholder="Enter your username" required autofocus>
                        </div>
                    </div>
                    <button type="submit" class="login-submit-btn w-100">Continue</button>
                </form>
            <?php else: ?>
                <form method="post" action="forgot-password.php">
                    <div class="login-field mb-3">
                        <label class="login-label" for="security_question">Your Security Question</label>
                        <select class="form-select login-select" id="security_question" name="security_question" required>
                            <option value="" disabled selected>Choose the question you registered&hellip;</option>
                            <?php foreach (SecurityQuestions::LIST as $q): ?>
                                <option value="<?= htmlspecialchars($q) ?>"><?= htmlspecialchars($q) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="login-field mb-3">
                        <label class="login-label" for="answer">Answer</label>
                        <div class="login-input-group">
                            <span class="login-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M9 11.24V7.5a3 3 0 1 1 6 0v3.74"/><rect x="5" y="11" width="14" height="10" rx="2"/>
                                </svg>
                            </span>
                            <input type="text" class="login-input" id="answer" name="answer" placeholder="Enter your answer" required>
                        </div>
                    </div>
                    <button type="submit" class="login-submit-btn w-100">Verify Answer</button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-3">
                <a href="login.php" class="login-link">Back to login</a>
            </div>
            </div>
        </div>
    </div>
</body>
</html>
