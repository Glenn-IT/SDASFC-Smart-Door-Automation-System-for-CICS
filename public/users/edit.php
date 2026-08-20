<?php

require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/controllers/UserController.php';
require_once __DIR__ . '/../../app/models/User.php';

Auth::requireAdmin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$user = User::findById($id);

if (!$user) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $idNumber = trim($_POST['id_number'] ?? '');
    $rfidUid = trim($_POST['rfid_uid'] ?? '');
    $role = $_POST['role'] ?? 'student';

    $error = UserController::update($id, $fullName, $idNumber, $rfidUid, $role);

    if ($error === null) {
        header('Location: index.php?updated=1');
        exit;
    }

    $user = ['id' => $id, 'full_name' => $fullName, 'id_number' => $idNumber, 'rfid_uid' => $rfidUid, 'role' => $role, 'status' => $user['status']];
}

$pageTitle = 'Edit User';
include __DIR__ . '/../partials/header.php';
?>

<div class="card shadow-sm" style="max-width: 520px;">
    <div class="card-body">
        <h5 class="card-title">Edit User</h5>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="edit.php">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <div class="mb-3">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="id_number">ID Number</label>
                <input type="text" class="form-control" id="id_number" name="id_number" value="<?= htmlspecialchars($user['id_number']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="rfid_uid">RFID UID</label>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" id="rfid_uid" name="rfid_uid" value="<?= htmlspecialchars($user['rfid_uid']) ?>" placeholder="e.g. 0A 75 B4 02 or A1B2C3D4" required>
                    <button type="button" class="btn btn-outline-primary" id="btn-fetch-rfid" onclick="fetchLastScannedRfid()">📡 Fetch Last Scanned Card</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="generateRandomRfid()">🎲 Generate</button>
                </div>
                <div class="form-text" id="rfid-help-text">Tap your physical card on the RFID reader, then click <strong>"Fetch Last Scanned Card"</strong> to auto-fill.</div>
            </div>

            <script>
            function fetchLastScannedRfid() {
                const btn = document.getElementById('btn-fetch-rfid');
                const help = document.getElementById('rfid-help-text');
                btn.disabled = true;
                btn.innerHTML = '⏳ Checking...';

                fetch('../api/last_scanned_rfid.php')
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = '📡 Fetch Last Scanned Card';
                        if (data.status === 'success') {
                            document.getElementById('rfid_uid').value = data.rfid_uid;
                            help.innerHTML = '<span class="text-success fw-bold">✅ Detected Card UID: ' + data.rfid_uid + ' (Scanned at ' + data.scanned_at + ')</span>';
                        } else {
                            help.innerHTML = '<span class="text-warning">⚠️ ' + data.message + '</span>';
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = '📡 Fetch Last Scanned Card';
                        help.innerHTML = '<span class="text-danger">❌ Error connecting to API: ' + err + '</span>';
                    });
            }

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
                    <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="faculty" <?= $user['role'] === 'faculty' ? 'selected' : '' ?>>Faculty</option>
                    <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
