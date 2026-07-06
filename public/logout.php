<?php

require_once __DIR__ . '/../app/core/Auth.php';

Auth::logout();
header('Location: ' . BASE_URL . '/login.php');
exit;
