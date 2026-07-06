<?php
/** @var string $pageTitle */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SDASFC') ?> - SDASFC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-grow-1">
        <nav class="navbar navbar-light bg-white border-bottom px-3">
            <span class="navbar-text fw-semibold"><?= htmlspecialchars($pageTitle ?? '') ?></span>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>/profile.php" class="text-muted small text-decoration-none"><?= htmlspecialchars(Auth::currentAdminName()) ?></a>
                <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
            </div>
        </nav>
        <main class="p-4">
