<?php
/**
 * SDASFC — PHP CLI Hybrid Serial Bridge Script
 * Synchronizes active card whitelists to ESP32 Flash memory for standalone
 * offline operation, and relays real-time RFID scans to the Web API.
 * Run via CLI: php serial_bridge.php --port=COM3
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line interface (CLI).\n");
}

$options = getopt("", ["port::", "baud::", "url::", "whitelist::"]);
$port = $options['port'] ?? 'COM3';
$baud = (int)($options['baud'] ?? 115200);
$apiUrl = $options['url'] ?? 'http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php';
$whitelistUrl = $options['whitelist'] ?? 'http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/whitelist.php';

echo "==================================================\n";
echo " SDASFC PHP CLI Hybrid Serial Bridge\n";
echo " Target Port       : {$port}\n";
echo " Baud Rate         : {$baud}\n";
echo " Scan API URL      : {$apiUrl}\n";
echo " Whitelist API URL : {$whitelistUrl}\n";
echo "==================================================\n\n";

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Configure Windows COM Port mode
    exec("mode {$port}: BAUD={$baud} PARITY=n DATA=8 STOP=1 to=off rts=on dtr=on octs=off odsr=off idsr=off");
    $device = "\\\\.\\{$port}";
} else {
    // Linux / Mac port path
    exec("stty -F {$port} {$baud} cs8 -cstopb -parenb");
    $device = $port;
}

$handle = @fopen($device, 'r+');
if (!$handle) {
    die("[ERROR] Could not open serial port {$port}. Ensure ESP32 is connected and port is not in use.\n");
}

echo "[STATUS] Serial port opened successfully. Initializing sync...\n";
sleep(2); // Wait for ESP32 stabilization
syncWhitelist($handle, $whitelistUrl);
echo "[STATUS] Ready! Listening for RFID taps...\n\n";

while (true) {
    $line = fgets($handle);
    if ($line !== false) {
        $line = trim($line);
        if ($line === '') continue;

        echo "[SERIAL RX] {$line}\n";

        if (strpos($line, 'REQ:SYNC') !== false || $line === 'SYNC') {
            echo " -> [SYNC REQUEST DETECTED]\n";
            syncWhitelist($handle, $whitelistUrl);
        } elseif (preg_match('/UID:\s*([A-F0-9\s]+)/i', $line, $matches)) {
            $uid = trim($matches[1]);
            echo " -> Processing scan for UID: '{$uid}'\n";

            $isGranted = verifyRfidUid($apiUrl, $uid);

            if ($isGranted) {
                echo " -> ACCESS GRANTED. Replying 'GRANT'\n";
                fwrite($handle, "GRANT\n");
                fflush($handle);
            } else {
                echo " -> ACCESS DENIED. Replying 'DENY'\n";
                fwrite($handle, "DENY\n");
                fflush($handle);
            }
        } elseif (strpos($line, 'EVENT:') !== false) {
            echo " -> [SYSTEM EVENT DETECTED]: {$line}\n";
        }
    } else {
        usleep(20000); // 20ms sleep
    }
}

function syncWhitelist($handle, string $whitelistUrl): void
{
    echo "[SYNC] Fetching active authorized cards...\n";
    $ch = curl_init($whitelistUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 4
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (($data['status'] ?? '') === 'success' && !empty($data['uids'])) {
            $uidsCsv = implode(',', $data['uids']);
            fwrite($handle, "SYNC_WHITELIST:{$uidsCsv}\n");
            fflush($handle);
            echo "[SYNC] ✅ Synced " . count($data['uids']) . " active card(s) to ESP32 Flash Memory!\n";
            return;
        }
    }
    echo "[SYNC] ⚠️ Could not fetch whitelist or no active cards registered.\n";
}

function verifyRfidUid(string $apiUrl, string $uid): bool
{
    $ch = curl_init($apiUrl);
    $payload = json_encode(['rfid_uid' => $uid]);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 4
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        return ($data['access'] ?? '') === 'granted';
    }

    return false;
}
