<?php

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../components/version.php';
require_once __DIR__ . '/../../app/controllers/AccessController.php';

if (!in_array(CURRENT_VERSION, ['v1.10', 'v2.10', 'v3.10', 'final-output', 'v-final'], true)) {
    header('Content-Type: application/json');
    http_response_code(503);
    echo json_encode(['access' => 'denied', 'reason' => 'feature_not_yet_available']);
    exit;
}

// 1. Handle HTTP POST (Primary method used by ESP32 and Serial Bridge)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $rawBody = file_get_contents('php://input');
    $json = json_decode($rawBody, true);

    $rfidUid = trim($json['rfid_uid'] ?? $_POST['rfid_uid'] ?? '');

    if ($rfidUid === '') {
        http_response_code(400);
        echo json_encode(['access' => 'denied', 'reason' => 'missing_rfid_uid']);
        exit;
    }

    echo json_encode(AccessController::handleScan($rfidUid));
    exit;
}

// 2. Handle HTTP GET with ?rfid_uid=... (Browser quick test)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['rfid_uid'])) {
    header('Content-Type: application/json');
    $rfidUid = trim((string)$_GET['rfid_uid']);
    if ($rfidUid === '') {
        http_response_code(400);
        echo json_encode(['access' => 'denied', 'reason' => 'missing_rfid_uid']);
        exit;
    }
    echo json_encode(AccessController::handleScan($rfidUid));
    exit;
}

// 3. Handle Browser direct navigation without parameters (Diagnostic Interface)
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
if (stripos($acceptHeader, 'text/html') !== false) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SDASFC Access Control API — Status & Tester</title>
        <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #0f172a; color: #f8fafc; font-family: system-ui, sans-serif; }
            .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; }
        </style>
    </head>
    <body class="py-5">
        <div class="container" style="max-width: 600px;">
            <div class="card p-4 shadow-lg">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-success me-2 px-3 py-2 fs-6">● API ONLINE</span>
                    <h5 class="m-0 fw-bold">SDASFC Access API</h5>
                </div>
                <p class="text-secondary small">
                    This endpoint receives RFID card tap events via <code>HTTP POST</code> from the ESP32 Wi-Fi controller or the USB Serial Bridge.
                </p>

                <div class="bg-dark p-3 rounded mb-3 border border-secondary">
                    <label class="form-label fw-semibold small text-light">Test Card UID Simulation:</label>
                    <div class="input-group">
                        <input type="text" id="testUid" class="form-control font-monospace" value="93 39 6E 1B" placeholder="e.g. 93 39 6E 1B">
                        <button class="btn btn-primary" onclick="testScan()">Simulate Tap</button>
                    </div>
                    <div class="form-text text-secondary">Pre-filled with Master Emergency Key (<code>93 39 6E 1B</code>).</div>
                </div>

                <div id="testResult" class="p-3 rounded bg-black font-monospace small" style="display:none;"></div>

                <div class="mt-4 pt-3 border-top border-secondary text-secondary small">
                    <strong>ESP32 POST Endpoint:</strong><br>
                    <code>POST http://<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost') ?><?= htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '') ?></code><br>
                    <code>Payload: {"rfid_uid": "93 39 6E 1B"}</code>
                </div>
            </div>
        </div>

        <script>
        function testScan() {
            const uid = document.getElementById('testUid').value.trim();
            const resDiv = document.getElementById('testResult');
            resDiv.style.display = 'block';
            resDiv.innerHTML = '<span class="text-info">Sending POST request...</span>';

            fetch('rfid_scan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rfid_uid: uid })
            })
            .then(res => res.json())
            .then(data => {
                if (data.access === 'granted') {
                    resDiv.innerHTML = '<span class="text-success fw-bold">✅ ACCESS GRANTED</span><br>' + JSON.stringify(data, null, 2);
                } else {
                    resDiv.innerHTML = '<span class="text-danger fw-bold">❌ ACCESS DENIED (' + data.reason + ')</span><br>' + JSON.stringify(data, null, 2);
                }
            })
            .catch(err => {
                resDiv.innerHTML = '<span class="text-danger">❌ Error: ' + err + '</span>';
            });
        }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// 4. Default Non-HTML GET Response
header('Content-Type: application/json');
echo json_encode([
    'status' => 'online',
    'endpoint' => 'rfid_scan.php',
    'message' => 'SDASFC Access API is ready. Submit tap events via HTTP POST with JSON body {"rfid_uid": "<HEX>"} or GET with ?rfid_uid=<HEX>'
]);
