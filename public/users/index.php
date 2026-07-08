<?php

require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/controllers/UserController.php';
require_once __DIR__ . '/../../app/models/User.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'toggle_status' && $id) {
        UserController::toggleStatus($id);
    }

    if ($action === 'delete' && $id) {
        UserController::delete($id);
    }

    header('Location: index.php');
    exit;
}

$users = User::all();

$pageTitle = 'Manage Users';
include __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Users</h5>
    <a href="create.php" class="btn btn-primary btn-sm">+ Add User</a>
</div>

<?php if (!empty($_GET['created'])): ?>
    <div class="alert alert-success py-2">User created.</div>
<?php endif; ?>
<?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success py-2">User updated.</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Full Name</th>
                    <th>ID Number</th>
                    <th>RFID UID</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$users): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No users yet. Add one to get started.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['id_number']) ?></td>
                        <td><code><?= htmlspecialchars($user['rfid_uid']) ?></code></td>
                        <td><span class="text-capitalize"><?= htmlspecialchars($user['role']) ?></span></td>
                        <td>
                            <?php if ($user['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <form method="post" action="index.php" class="d-inline">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                    <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                            <form method="post" action="index.php" class="d-inline"
                                onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
