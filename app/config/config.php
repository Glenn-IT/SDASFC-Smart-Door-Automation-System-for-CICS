<?php

date_default_timezone_set('Asia/Manila');

define('DB_HOST', 'localhost');
define('DB_NAME', 'sdasfc');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', '/SDASFC-Smart-Door-Automation-System-for-CICS/public');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
