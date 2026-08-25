<?php

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../components/version.php';
require_once __DIR__ . '/../../app/models/User.php';

header('Content-Type: application/json');

// Only allow in active versions
if (!in_array(CURRENT_VERSION, ['v1.10', 'v2.10', 'v3.10', 'final-output', 'v-final'], true)) {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'feature_not_yet_available']);
    exit;
}

try {
    $activeUsers = User::all('a-z', 'all', 'active');
    $uids = [];

    foreach ($activeUsers as $user) {
        $cleanUid = trim($user['rfid_uid'] ?? '');
        if ($cleanUid !== '') {
            $uids[] = strtoupper($cleanUid);
        }
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($uids),
        'uids' => $uids,
        'synced_at' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
