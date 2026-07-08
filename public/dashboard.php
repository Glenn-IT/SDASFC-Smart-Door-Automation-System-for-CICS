<?php

require_once __DIR__ . '/../components/under-construction.php';

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/AccessLog.php';

Auth::requireAdmin();

$userCount = User::count();
$stats = AccessLog::todayStats();
$recentLogs = AccessLog::recent(10);

$pageTitle = 'Dashboard';
include __DIR__ . '/partials/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Registered Users</div>
                <div class="fs-3 fw-semibold"><?= $userCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Taps Today</div>
                <div class="fs-3 fw-semibold"><?= $stats['taps'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Granted Today</div>
                <div class="fs-3 fw-semibold text-success"><?= $stats['granted'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Denied Today</div>
                <div class="fs-3 fw-semibold text-danger"><?= $stats['denied'] ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="card-title mb-0">Recent Activity</h6>
            <a href="<?= BASE_URL ?>/reports/index.php" class="small">View all logs</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
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
                    <?php if (!$recentLogs): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No RFID taps recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($recentLogs as $log): ?>
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
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
