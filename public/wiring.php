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
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
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
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-semibold" id="btnProtection" onclick="switchDiagram('protection')">🛡️ Lock Protection & Diode Guide</button>
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
        const btnP = document.getElementById('btnProtection');

        // Reset all buttons to inactive outline
        btnB.className = 'btn btn-sm btn-outline-primary';
        btnD.className = 'btn btn-sm btn-outline-primary';
        btnP.className = 'btn btn-sm btn-outline-warning text-dark fw-semibold';

        if (type === 'breadboard') {
            frame.src = '../arduino/wiring_diagram_with_breadboard/wiring_diagram.html';
            btnB.className = 'btn btn-sm btn-primary active';
        } else if (type === 'direct') {
            frame.src = '../arduino/wiring_diagram_without_breadboard/wiring_diagra_wb.html';
            btnD.className = 'btn btn-sm btn-primary active';
        } else if (type === 'protection') {
            frame.src = '../arduino/wiring_diagram_protection/wiring_diagram_protection.html';
            btnP.className = 'btn btn-sm btn-warning text-dark fw-bold active';
        }
    }
    </script>
</body>
</html>
