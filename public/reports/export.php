<?php

require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/models/ReportsFeed.php';

Auth::requireAdmin();

$filters = [
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'result' => $_GET['result'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
];

$rows = ReportsFeed::filtered($filters);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="reports_' . date('Y-m-d_His') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date/Time', 'Type', 'User/Admin', 'Detail', 'Result']);

foreach ($rows as $row) {
    fputcsv($out, [
        formatDateTime($row['timestamp']),
        $row['type'] === 'access' ? 'RFID' : 'Admin',
        $row['person'],
        $row['type'] === 'access'
            ? $row['detail_primary'] . ' - ' . $row['detail_secondary']
            : $row['detail_secondary'],
        $row['result'] ?? '',
    ]);
}

fclose($out);
