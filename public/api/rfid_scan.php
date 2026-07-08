<?php

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../components/version.php';
require_once __DIR__ . '/../../app/controllers/AccessController.php';

header('Content-Type: application/json');

if (CURRENT_VERSION !== 'v1.10') {
    http_response_code(503);
    echo json_encode(['access' => 'denied', 'reason' => 'feature_not_yet_available']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['access' => 'denied', 'reason' => 'method_not_allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');
$json = json_decode($rawBody, true);

$rfidUid = trim($json['rfid_uid'] ?? $_POST['rfid_uid'] ?? '');

if ($rfidUid === '') {
    http_response_code(400);
    echo json_encode(['access' => 'denied', 'reason' => 'missing_rfid_uid']);
    exit;
}

echo json_encode(AccessController::handleScan($rfidUid));
