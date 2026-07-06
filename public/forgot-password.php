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
    <title>Forgot Password - SDASFC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow-sm" style="width: 100%; max-width: 420px;">
            <div class="card-body p-4">
                <h4 class="card-title mb-1 text-center">Forgot Password</h4>
                <p class="text-muted text-center mb-4">
                    <?= $step === 1 ? 'Enter your username to continue.' : 'Answer your security question.' ?>
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                    <form method="post" action="forgot-password.php">
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Continue</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="forgot-password.php">
                        <div class="mb-3">
                            <label class="form-label" for="security_question">Your Security Question</label>
                            <select class="form-select" id="security_question" name="security_question" required>
                                <option value="" disabled selected>Choose the question you registered&hellip;</option>
                                <?php foreach (SecurityQuestions::LIST as $q): ?>
                                    <option value="<?= htmlspecialchars($q) ?>"><?= htmlspecialchars($q) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="answer">Answer</label>
                            <input type="text" class="form-control" id="answer" name="answer" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify Answer</button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="login.php" class="small">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
