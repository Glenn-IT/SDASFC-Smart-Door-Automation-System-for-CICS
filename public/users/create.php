<?php

require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/controllers/UserController.php';

Auth::requireAdmin();

$error = null;
$fullName = $idNumber = $rfidUid = '';
$role = 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $idNumber = trim($_POST['id_number'] ?? '');
    $rfidUid = trim($_POST['rfid_uid'] ?? '');
    $role = $_POST['role'] ?? 'student';

    $error = UserController::create($fullName, $idNumber, $rfidUid, $role);

    if ($error === null) {
        header('Location: index.php?created=1');
        exit;
    }
}

$pageTitle = 'Add User';
include __DIR__ . '/../partials/header.php';
?>

<div class="card shadow-sm" style="max-width: 520px;">
    <div class="card-body">
        <h5 class="card-title">Add User</h5>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="create.php">
            <div class="mb-3">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($fullName) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="id_number">ID Number</label>
                <input type="text" class="form-control" id="id_number" name="id_number" value="<?= htmlspecialchars($idNumber) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="rfid_uid">RFID UID</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="rfid_uid" name="rfid_uid" value="<?= htmlspecialchars($rfidUid) ?>" placeholder="e.g. A1B2C3D4" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="generateRandomRfid()">🎲 Generate</button>
                </div>
                <div class="form-text">Enter manually or click Generate to assign a random RFID code.</div>
            </div>

            <script>
            function generateRandomRfid() {
                const chars = '0123456789ABCDEF';
                let uid = '';
                for (let i = 0; i < 8; i++) {
                    uid += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                document.getElementById('rfid_uid').value = uid;
            }
            </script>
            <div class="mb-3">
                <label class="form-label" for="role">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="faculty" <?= $role === 'faculty' ? 'selected' : '' ?>>Faculty</option>
                    <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save User</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
