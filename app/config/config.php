<?php

date_default_timezone_set('Asia/Manila');

define('DB_HOST', 'localhost');
define('DB_NAME', 'sdasfc');
define('DB_USER', 'root');
define('DB_PASS', '');

// Dynamically detect BASE_URL so assets work on ANY device, IP (192.168.x.x, localhost), or folder name
if (!defined('BASE_URL')) {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $publicPos = strpos($scriptName, '/public');
    if ($publicPos !== false) {
        $baseUrl = substr($scriptName, 0, $publicPos + 7);
    } elseif (php_sapi_name() === 'cli' || empty($scriptName)) {
        $baseUrl = '/SDASFC-Smart-Door-Automation-System-for-CICS/public';
    } else {
        $baseUrl = '';
    }
    define('BASE_URL', rtrim($baseUrl, '/'));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
