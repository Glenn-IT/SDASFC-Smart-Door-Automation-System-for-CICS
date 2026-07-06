<?php

require_once __DIR__ . '/../app/core/Auth.php';

header('Location: ' . BASE_URL . '/' . (Auth::isLoggedIn() ? 'dashboard.php' : 'login.php'));
exit;
