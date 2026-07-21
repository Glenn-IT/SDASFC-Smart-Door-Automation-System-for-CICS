<?php

require_once __DIR__ . '/../../components/under-construction.php';

require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/Schedule.php';

Auth::requireAdmin();

$users = User::all();
$counts = Schedule::countsByUser();

$pageTitle = 'Schedules';
include __DIR__ . '/../partials/header.php';
?>

<h5 class="mb-3">Schedules by User</h5>

<?php if (!$users): ?>
    <div class="alert alert-info">
        No users yet. <a href="<?= BASE_URL ?>/users/create.php">Add a user</a> first to assign schedules.
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Full Name</th>
                        <th>ID Number</th>
                        <th>Role</th>
                        <th>Schedules</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['id_number']) ?></td>
                            <td class="text-capitalize"><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= $counts[$user['id']] ?? 0 ?> window(s)</td>
                            <td class="text-end">
                                <a href="user.php?id=<?= $user['id'] ?>" class="btn btn-outline-primary btn-sm">Manage Schedules</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
