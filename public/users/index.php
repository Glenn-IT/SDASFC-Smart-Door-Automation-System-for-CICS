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

    $redirectQuery = http_build_query(array_filter([
        'sort' => $_POST['sort'] ?? '',
        'role' => $_POST['role'] ?? '',
        'status' => $_POST['status'] ?? '',
        'search' => $_POST['search'] ?? '',
    ]));

    header('Location: index.php' . ($redirectQuery ? '?' . $redirectQuery : ''));
    exit;
}

$sort = $_GET['sort'] ?? 'a-z';
$role = $_GET['role'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$users = User::all($sort, $role, $status, $search);

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

<!-- Filter & Search Toolbar -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="get" action="index.php" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, ID, or RFID..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="a-z" <?= $sort === 'a-z' ? 'selected' : '' ?>>Sort: Name (A - Z)</option>
                    <option value="z-a" <?= $sort === 'z-a' ? 'selected' : '' ?>>Sort: Name (Z - A)</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $role === 'all' ? 'selected' : '' ?>>All Roles</option>
                    <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="faculty" <?= $role === 'faculty' ? 'selected' : '' ?>>Faculty</option>
                    <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm flex-fill">Filter</button>
                <?php if ($search !== '' || $sort !== 'a-z' || $role !== 'all' || $status !== 'all'): ?>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

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
                        <td colspan="6" class="text-center text-muted py-4">
                            <?php if ($search !== '' || $role !== 'all' || $status !== 'all'): ?>
                                No users match the selected filters.
                            <?php else: ?>
                                No users yet. Add one to get started.
                            <?php endif; ?>
                        </td>
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
                                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                                <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
                                <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                    <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
