<?php

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/models/ActivityLog.php';

if (Auth::isLoggedIn()) {
    ActivityLog::record(Auth::currentAdminId(), 'logout', 'Admin ' . Auth::currentAdminName() . ' logged out');
}

Auth::logout();
header('Location: ' . BASE_URL . '/login.php');
exit;
