<?php

require_once __DIR__ . '/../components/under-construction.php';

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/controllers/ProfileController.php';
require_once __DIR__ . '/../app/models/Admin.php';

Auth::requireAdmin();

$adminId = (int) $_SESSION['admin_id'];
$nameError = $nameSuccess = null;
$passwordError = $passwordSuccess = null;
$securityError = $securitySuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_name') {
        $nameError = ProfileController::updateFullName($adminId, $_POST['full_name'] ?? '');
        $nameSuccess = $nameError === null ? 'Name updated.' : null;
    }

    if ($action === 'change_password') {
        $passwordError = ProfileController::changePassword(
            $adminId,
            $_POST['current_password'] ?? '',
            $_POST['new_password'] ?? '',
            $_POST['confirm_password'] ?? ''
        );
        $passwordSuccess = $passwordError === null ? 'Password changed.' : null;
    }

    if ($action === 'update_security') {
        $securityError = ProfileController::updateSecurityQA(
            $adminId,
            $_POST['current_password_sec'] ?? '',
            $_POST['security_question'] ?? '',
            $_POST['security_answer'] ?? ''
        );
        $securitySuccess = $securityError === null ? 'Security question updated.' : null;
    }
}

$admin = Admin::findById($adminId);

$pageTitle = 'Manage Profile';
include __DIR__ . '/partials/header.php';
?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Profile</h5>

                <?php if ($nameSuccess): ?>
                    <div class="alert alert-success py-2"><?= htmlspecialchars($nameSuccess) ?></div>
                <?php elseif ($nameError): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($nameError) ?></div>
                <?php endif; ?>

                <form method="post" action="profile.php">
                    <input type="hidden" name="action" value="update_name">
                    <div class="mb-3">
                        <label class="form-label" for="username_display">Username</label>
                        <input type="text" class="form-control" id="username_display" value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="full_name">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Name</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Change Password</h5>

                <?php if ($passwordSuccess): ?>
                    <div class="alert alert-success py-2"><?= htmlspecialchars($passwordSuccess) ?></div>
                <?php elseif ($passwordError): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($passwordError) ?></div>
                <?php endif; ?>

                <form method="post" action="profile.php">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label" for="current_password">Current Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="current_password">Show</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="new_password">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="new_password">Show</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="confirm_password">Show</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Security Question</h5>
                <p class="text-muted small">Used to verify your identity on the "Forgot password" screen.</p>

                <?php if ($securitySuccess): ?>
                    <div class="alert alert-success py-2"><?= htmlspecialchars($securitySuccess) ?></div>
                <?php elseif ($securityError): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($securityError) ?></div>
                <?php endif; ?>

                <?php if ($admin['security_question']): ?>
                    <p class="mb-3"><strong>Current question:</strong> <?= htmlspecialchars($admin['security_question']) ?></p>
                <?php else: ?>
                    <p class="mb-3 text-warning">No security question set yet. Set one so you can recover your account.</p>
                <?php endif; ?>

                <form method="post" action="profile.php">
                    <input type="hidden" name="action" value="update_security">
                    <div class="mb-3">
                        <label class="form-label" for="security_question">Security Question</label>
                        <select class="form-select" id="security_question" name="security_question" required>
                            <option value="" disabled <?= !$admin['security_question'] ? 'selected' : '' ?>>Choose a question&hellip;</option>
                            <?php foreach (SecurityQuestions::LIST as $q): ?>
                                <option value="<?= htmlspecialchars($q) ?>" <?= $admin['security_question'] === $q ? 'selected' : '' ?>><?= htmlspecialchars($q) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="security_answer">Answer</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="security_answer" name="security_answer" required>
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="security_answer">Show</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="current_password_sec">Current Password (to confirm)</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password_sec" name="current_password_sec" required>
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password="current_password_sec">Show</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Security Question</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
