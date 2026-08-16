<?php
// SDASFC — Hardware Wiring Diagram Page
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Auth.php';

Auth::requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SDASFC — Hardware Wiring Diagram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        iframe.diagram-frame {
            width: 100%;
            height: calc(100vh - 120px);
            border: none;
            border-radius: 12px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="m-0 fw-bold">SDASFC Hardware & Speaker Wiring Guide</h4>
            <a href="<?php echo BASE_URL; ?>/dashboard.php" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
        </div>
        <iframe class="diagram-frame" src="../arduino/wiring_diagram.html"></iframe>
    </div>
</body>
</html>
