<?php

require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/models/ReportsFeed.php';
require_once __DIR__ . '/../../app/models/User.php';

Auth::requireAdmin();

$filters = [
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'result' => $_GET['result'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
];

$rows = ReportsFeed::filtered($filters);
$users = User::all();

$queryString = http_build_query(array_filter($filters));

$pageTitle = 'Reports';
include __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">System Reports</h5>
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
                    <th>Type</th>
                    <th>User/Admin</th>
                    <th>Details</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No transactions match these filters.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars(formatDateTime($row['timestamp'])) ?></td>
                        <td>
                            <?php if ($row['type'] === 'access'): ?>
                                <span class="badge bg-info text-dark">RFID</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Admin</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['person']) ?></td>
                        <td>
                            <?php if ($row['type'] === 'access'): ?>
                                <code><?= htmlspecialchars($row['detail_primary']) ?></code> &mdash; <?= htmlspecialchars($row['detail_secondary']) ?>
                            <?php else: ?>
                                <?= htmlspecialchars($row['detail_secondary']) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['result'] === 'granted'): ?>
                                <span class="badge bg-success">Granted</span>
                            <?php elseif ($row['result'] === 'denied'): ?>
                                <span class="badge bg-danger">Denied</span>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
