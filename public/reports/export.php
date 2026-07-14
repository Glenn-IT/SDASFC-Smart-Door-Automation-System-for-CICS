<?php

require_once __DIR__ . '/../../components/under-construction.php';

require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/models/AccessLog.php';

Auth::requireAdmin();

$filters = [
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'result' => $_GET['result'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
];

$logs = AccessLog::filtered($filters);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="access_logs_' . date('Y-m-d_His') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date/Time', 'User', 'RFID UID', 'Result', 'Reason']);

foreach ($logs as $log) {
    fputcsv($out, [
        $log['scanned_at'],
        $log['full_name'] ?? 'Unknown',
        $log['rfid_uid'],
        $log['result'],
        $log['reason'],
    ]);
}

fclose($out);
