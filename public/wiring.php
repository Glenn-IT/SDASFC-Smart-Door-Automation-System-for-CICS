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
            <h4 class="m-0 fw-bold">SDASFC Hardware & Wiring Diagrams</h4>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-primary active" id="btnBreadboard" onclick="switchDiagram('breadboard')">With Breadboard</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnDirect" onclick="switchDiagram('direct')">Without Breadboard</button>
                </div>
                <a href="<?php echo BASE_URL; ?>/dashboard.php" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
            </div>
        </div>
        <iframe id="diagramFrame" class="diagram-frame" src="../arduino/wiring_diagram_with_breadboard/wiring_diagram.html"></iframe>
    </div>

    <script>
    function switchDiagram(type) {
        const frame = document.getElementById('diagramFrame');
        const btnB = document.getElementById('btnBreadboard');
        const btnD = document.getElementById('btnDirect');

        if (type === 'breadboard') {
            frame.src = '../arduino/wiring_diagram_with_breadboard/wiring_diagram.html';
            btnB.classList.add('btn-primary', 'active');
            btnB.classList.remove('btn-outline-primary');
            btnD.classList.remove('btn-primary', 'active');
            btnD.classList.add('btn-outline-primary');
        } else {
            frame.src = '../arduino/wiring_diagram_without_breadboard/wiring_diagra_wb.html';
            btnD.classList.add('btn-primary', 'active');
            btnD.classList.remove('btn-outline-primary');
            btnB.classList.remove('btn-primary', 'active');
            btnB.classList.add('btn-outline-primary');
        }
    }
    </script>
</body>
</html>
