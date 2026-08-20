<?php

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/models/AccessLog.php';

header('Content-Type: application/json');

Auth::requireAdmin();

$last = AccessLog::lastScanned();

if ($last && !empty($last['rfid_uid'])) {
    echo json_encode([
        'status' => 'success',
        'rfid_uid' => trim($last['rfid_uid']),
        'scanned_at' => $last['scanned_at'],
        'result' => $last['result']
    ]);
} else {
    echo json_encode([
        'status' => 'empty',
        'message' => 'No RFID scans detected yet. Tap a card on the reader to detect its UID.'
    ]);
}
