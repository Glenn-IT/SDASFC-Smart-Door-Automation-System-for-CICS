<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/version.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Construction - SDASFC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow-sm text-center" style="width: 100%; max-width: 420px;">
            <div class="card-body p-4">
                <div style="font-size: 3rem;">&#128679;</div>
                <span class="badge bg-secondary mb-2"><?= htmlspecialchars(CURRENT_VERSION) ?></span>
                <h4 class="card-title mb-2">Page Under Construction</h4>
                <p class="text-muted mb-4">This feature hasn't been unlocked yet in the current presentation version. Please check back in a later release.</p>
                <a href="<?= BASE_URL ?>/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
exit;
