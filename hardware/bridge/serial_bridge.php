<?php
/**
 * SDASFC — PHP CLI Serial Bridge Script
 * Alternative PHP-native serial bridge script for environments without Python.
 * Run via CLI: php serial_bridge.php --port=COM3
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line interface (CLI).\n");
}

$options = getopt("", ["port::", "baud::", "url::"]);
$port = $options['port'] ?? 'COM3';
$baud = (int)($options['baud'] ?? 9600);
$apiUrl = $options['url'] ?? 'http://localhost/SDASFC-Smart-Door-Automation-System-for-CICS/public/api/rfid_scan.php';

echo "==================================================\n";
echo " SDASFC PHP CLI Serial Bridge\n";
echo " Target Port  : {$port}\n";
echo " Baud Rate    : {$baud}\n";
echo " API Endpoint : {$apiUrl}\n";
echo "==================================================\n\n";

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Configure Windows COM Port mode
    exec("mode {$port} BAUD={$baud} PARITY=n DATA=8 STOP=1 xon=off to=on");
    $device = "\\\\.\\{$port}";
} else {
    // Linux / Mac port path
    exec("stty -F {$port} {$baud} cs8 -cstopb -parenb");
    $device = $port;
}

$handle = @fopen($device, 'r+');
if (!$handle) {
    die("[ERROR] Could not open serial port {$port}. Ensure Arduino is connected and port is not in use.\n");
}

stream_set_blocking($handle, false);
echo "[STATUS] Serial port opened successfully. Listening for RFID taps...\n\n";

$buffer = '';

while (true) {
    $char = fread($handle, 128);
    if ($char !== false && strlen($char) > 0) {
        $buffer .= $char;
        while (($pos = strpos($buffer, "\n")) !== false) {
            $line = trim(substr($buffer, 0, $pos));
            $buffer = substr($buffer, $pos + 1);

            if ($line === '') continue;

            echo "[SERIAL RX] {$line}\n";

            if (strpos($line, 'UID:') === 0) {
                $uid = trim(substr($line, 4));
                echo " -> Processing scan for UID: '{$uid}'\n";

                $isGranted = verifyRfidUid($apiUrl, $uid);

                if ($isGranted) {
                    echo " -> ACCESS GRANTED. Replying 'GRANT'\n";
                    fwrite($handle, "GRANT\n");
                } else {
                    echo " -> ACCESS DENIED. Replying 'DENY'\n";
                    fwrite($handle, "DENY\n");
                }
            }
        }
    }
    usleep(50000); // 50ms sleep
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
        CURLOPT_TIMEOUT => 5
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
