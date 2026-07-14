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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #101635;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .uc-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5);
            max-width: 420px;
            width: 100%;
        }
        .uc-banner {
            height: 90px;
            border-radius: 20px 20px 0 0;
            background: linear-gradient(135deg, #293681 0%, #4274d9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
        }
        .uc-badge {
            background: #eef1fb;
            color: #293681;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="uc-card text-center">
            <div class="uc-banner">&#128679;</div>
            <div class="p-4">
                <span class="badge uc-badge mb-2"><?= htmlspecialchars(CURRENT_VERSION) ?></span>
                <h4 class="card-title mb-2 fw-bold" style="color: #1c2440;">Page Under Construction</h4>
                <p class="text-muted mb-4">This feature hasn't been unlocked yet in the current presentation version. Please check back in a later release.</p>
                <a href="<?= BASE_URL ?>/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
exit;
