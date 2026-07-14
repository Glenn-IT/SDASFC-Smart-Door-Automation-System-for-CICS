<?php

require_once __DIR__ . '/../../components/under-construction.php';

require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/Schedule.php';
require_once __DIR__ . '/../../app/controllers/ScheduleController.php';

Auth::requireAdmin();

$userId = (int) ($_GET['id'] ?? $_POST['user_id'] ?? 0);
$user = User::findById($userId);

if (!$user) {
    header('Location: index.php');
    exit;
}

$error = null;
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $error = ScheduleController::create(
            $userId,
            $_POST['day_of_week'] ?? '',
            $_POST['time_start'] ?? '',
            $_POST['time_end'] ?? ''
        );

        if ($error === null) {
            header('Location: user.php?id=' . $userId . '&added=1');
            exit;
        }
    }

    if ($action === 'update') {
        $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
        $error = ScheduleController::update(
            $scheduleId,
            $userId,
            $_POST['day_of_week'] ?? '',
            $_POST['time_start'] ?? '',
            $_POST['time_end'] ?? ''
        );

        if ($error === null) {
            header('Location: user.php?id=' . $userId . '&updated=1');
            exit;
        }

        $editing = [
            'id' => $scheduleId,
            'day_of_week' => $_POST['day_of_week'] ?? '',
            'time_start' => $_POST['time_start'] ?? '',
            'time_end' => $_POST['time_end'] ?? '',
        ];
    }

    if ($action === 'toggle_active') {
        ScheduleController::toggleActive((int) ($_POST['schedule_id'] ?? 0));
        header('Location: user.php?id=' . $userId);
        exit;
    }

    if ($action === 'delete') {
        ScheduleController::delete((int) ($_POST['schedule_id'] ?? 0));
        header('Location: user.php?id=' . $userId . '&deleted=1');
        exit;
    }
}

if (!$editing && !empty($_GET['edit'])) {
    $found = Schedule::findById((int) $_GET['edit']);
    if ($found && (int) $found['user_id'] === $userId) {
        $editing = $found;
    }
}

$schedules = Schedule::findByUserId($userId);
$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

$pageTitle = 'Schedules — ' . $user['full_name'];
include __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><?= htmlspecialchars($user['full_name']) ?></h5>
        <div class="text-muted small"><?= htmlspecialchars($user['id_number']) ?> &middot; <span class="text-capitalize"><?= htmlspecialchars($user['role']) ?></span></div>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">Back to Users</a>
</div>

<?php if (!empty($_GET['added'])): ?>
    <div class="alert alert-success py-2">Schedule added.</div>
<?php endif; ?>
<?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success py-2">Schedule updated.</div>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
    <div class="alert alert-success py-2">Schedule deleted.</div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title"><?= $editing ? 'Edit Window' : 'Add Access Window' ?></h6>
                <form method="post" action="user.php?id=<?= $userId ?>">
                    <input type="hidden" name="user_id" value="<?= $userId ?>">
                    <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
                    <?php if ($editing): ?>
                        <input type="hidden" name="schedule_id" value="<?= $editing['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="day_of_week">Day</label>
                        <select class="form-select" id="day_of_week" name="day_of_week" required>
                            <option value="" disabled <?= !$editing ? 'selected' : '' ?>>Choose a day&hellip;</option>
                            <?php foreach ($days as $day): ?>
                                <option value="<?= $day ?>" <?= ($editing['day_of_week'] ?? '') === $day ? 'selected' : '' ?>><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="time_start">Start Time</label>
                            <input type="time" class="form-control" id="time_start" name="time_start" value="<?= htmlspecialchars($editing['time_start'] ?? '') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="time_end">End Time</label>
                            <input type="time" class="form-control" id="time_end" name="time_end" value="<?= htmlspecialchars($editing['time_end'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Add Window' ?></button>
                        <?php if ($editing): ?>
                            <a href="user.php?id=<?= $userId ?>" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Day</th>
                            <th>Window</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$schedules): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No access windows yet.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?= htmlspecialchars($schedule['day_of_week']) ?></td>
                                <td><?= substr($schedule['time_start'], 0, 5) ?> &ndash; <?= substr($schedule['time_end'], 0, 5) ?></td>
                                <td>
                                    <?php if ($schedule['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="user.php?id=<?= $userId ?>&edit=<?= $schedule['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <form method="post" action="user.php?id=<?= $userId ?>" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
                                        <button type="submit" class="btn btn-outline-warning btn-sm">
                                            <?= $schedule['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                    <form method="post" action="user.php?id=<?= $userId ?>" class="d-inline"
                                        onsubmit="return confirm('Delete this schedule window?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
