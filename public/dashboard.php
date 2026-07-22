<?php

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/AccessLog.php';

Auth::requireAdmin();

$userCount = User::count();
$stats = AccessLog::todayStats();
$recentLogs = AccessLog::recent(10);
$dailyCounts = AccessLog::dailyCounts(7);
$roleCounts = User::countByRole();

$pageTitle = 'Dashboard';
include __DIR__ . '/partials/header.php';
?>

<h4 class="mb-3">Welcome back, <?= htmlspecialchars(Auth::currentAdminName()) ?>!</h4>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-card-icon" style="background: linear-gradient(135deg, var(--app-navy), var(--app-blue));">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                    </svg>
                </span>
                <div>
                    <div class="text-muted small">Registered Users</div>
                    <div class="fs-3 fw-semibold"><?= $userCount ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-card-icon" style="background: linear-gradient(135deg, var(--app-blue), var(--app-sky));">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3Zm2-1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2Z"/>
                        <path d="M8 4a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM4.5 12a3.5 3.5 0 1 1 7 0h-7Z"/>
                    </svg>
                </span>
                <div>
                    <div class="text-muted small">Taps Today</div>
                    <div class="fs-3 fw-semibold"><?= $stats['taps'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-card-icon" style="background: linear-gradient(135deg, #1f9d63, #34c982);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0Z"/>
                    </svg>
                </span>
                <div>
                    <div class="text-muted small">Granted Today</div>
                    <div class="fs-3 fw-semibold text-success"><?= $stats['granted'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-card-icon" style="background: linear-gradient(135deg, #b3261e, #e04b42);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708Z"/>
                    </svg>
                </span>
                <div>
                    <div class="text-muted small">Denied Today</div>
                    <div class="fs-3 fw-semibold text-danger"><?= $stats['denied'] ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title mb-3">Taps — Last 7 Days</h6>
                <canvas id="chartTaps" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title mb-3">Today's Access Results</h6>
                <canvas id="chartResults" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title mb-3">Users by Role</h6>
                <canvas id="chartRoles" height="220"></canvas>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const dailyLabels = <?= json_encode(array_column($dailyCounts, 'label')) ?>;
    const dailyCounts = <?= json_encode(array_column($dailyCounts, 'count')) ?>;
    const resultCounts = <?= json_encode([$stats['granted'], $stats['denied']]) ?>;
    const roleLabels = <?= json_encode(array_map('ucfirst', array_keys($roleCounts))) ?>;
    const roleCounts = <?= json_encode(array_values($roleCounts)) ?>;

    new Chart(document.getElementById('chartTaps'), {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Taps',
                data: dailyCounts,
                borderColor: '#293681',
                backgroundColor: 'rgba(66, 116, 217, 0.15)',
                tension: 0.3,
                fill: true,
            }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    new Chart(document.getElementById('chartResults'), {
        type: 'pie',
        data: {
            labels: ['Granted', 'Denied'],
            datasets: [{
                data: resultCounts,
                backgroundColor: ['#1f9d63', '#b3261e'],
            }],
        },
    });

    new Chart(document.getElementById('chartRoles'), {
        type: 'bar',
        data: {
            labels: roleLabels,
            datasets: [{
                label: 'Users',
                data: roleCounts,
                backgroundColor: ['#293681', '#4274d9', '#95ccdd'],
            }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
