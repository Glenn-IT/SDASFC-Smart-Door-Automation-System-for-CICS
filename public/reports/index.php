<?php

require_once __DIR__ . '/../../components/under-construction.php';

require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/models/AccessLog.php';
require_once __DIR__ . '/../../app/models/User.php';

Auth::requireAdmin();

$filters = [
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'result' => $_GET['result'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
];

$logs = AccessLog::filtered($filters);
$users = User::all();

$queryString = http_build_query(array_filter($filters));

$pageTitle = 'Reports';
include __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Access Logs</h5>
    <a href="export.php?<?= htmlspecialchars($queryString) ?>" class="btn btn-outline-primary btn-sm">Export CSV</a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="get" action="index.php" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label small" for="date_from">From</label>
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small" for="date_to">To</label>
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small" for="result">Result</label>
                <select class="form-select form-select-sm" id="result" name="result">
                    <option value="">All</option>
                    <option value="granted" <?= $filters['result'] === 'granted' ? 'selected' : '' ?>>Granted</option>
                    <option value="denied" <?= $filters['result'] === 'denied' ? 'selected' : '' ?>>Denied</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small" for="user_id">User</label>
                <select class="form-select form-select-sm" id="user_id" name="user_id">
                    <option value="">All</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= (string) $filters['user_id'] === (string) $user['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>RFID UID</th>
                    <th>Result</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$logs): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No access log entries match these filters.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['scanned_at']) ?></td>
                        <td><?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?></td>
                        <td><code><?= htmlspecialchars($log['rfid_uid']) ?></code></td>
                        <td>
                            <?php if ($log['result'] === 'granted'): ?>
                                <span class="badge bg-success">Granted</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Denied</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($log['reason']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
