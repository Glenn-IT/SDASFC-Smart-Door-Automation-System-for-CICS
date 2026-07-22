<?php
/** @var string $pageTitle */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SDASFC') ?> - SDASFC</title>
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>/assets/img/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-grow-1">
        <nav class="navbar app-navbar navbar-light border-bottom px-3">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="app-sidebar-toggle" data-sidebar-toggle aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                        <path d="M3 6h18M3 12h18M3 18h18"/>
                    </svg>
                </button>
                <span class="navbar-text fw-semibold"><?= htmlspecialchars($pageTitle ?? '') ?></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>/profile.php" class="text-decoration-none small fw-semibold" style="color: var(--app-navy);"><?= htmlspecialchars(Auth::currentAdminName()) ?></a>
            </div>
        </nav>
        <main class="p-4">
