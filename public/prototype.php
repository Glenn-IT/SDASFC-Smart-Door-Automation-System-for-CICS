<?php
require_once __DIR__ . '/../app/core/Auth.php';

// Allow viewing prototype directly or within authenticated session
$isLoggedIn = Auth::isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDASFC - System Prototype & UI Wireframe Specification</title>
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>/assets/img/logo.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --cics-navy: #293681;
            --cics-navy-dark: #1c2557;
            --cics-blue: #4274d9;
            --cics-sky: #95ccdd;
            --cics-sky-light: #eaf3f8;
            --cics-mint: #d0e7e6;
            --cics-ink: #1c2440;
            --cics-muted: #64748b;
            --cics-border: #94a3b8;
            --cics-border-light: #cbd5e1;
            --cics-bg-subtle: #f8fafc;
            --cics-success: #10b981;
            --cics-success-bg: #ecfdf5;
            --cics-danger: #ef4444;
            --cics-danger-bg: #fef2f2;
            --cics-warning: #f59e0b;
            --cics-warning-bg: #fffbeb;
        }

        /* Color Scheme Modes */
        body.color-theme {
            --frame-border: #293681;
            --frame-inner-border: #4274d9;
            --banner-bg: #293681;
            --banner-text: #ffffff;
            --header-sub-bg: #e8eef9;
            --header-sub-text: #1c2557;
            --input-bg: #f8fafc;
            --input-border: #94a3b8;
            --btn-primary-bg: #4274d9;
            --btn-primary-text: #ffffff;
            --btn-secondary-bg: #ffffff;
            --btn-secondary-text: #293681;
            --btn-secondary-border: #293681;
            --arrow-color: #293681;
            --badge-granted-bg: #d1fae5;
            --badge-granted-text: #065f46;
            --badge-granted-border: #10b981;
            --badge-denied-bg: #fee2e2;
            --badge-denied-text: #991b1b;
            --badge-denied-border: #ef4444;
            --stat-card-bg: #f0f4fc;
            --table-th-bg: #e8eef9;
            --table-th-text: #1c2557;
            --highlight-bg: #e0e9f8;
        }

        body.monochrome-theme {
            --frame-border: #000000;
            --frame-inner-border: #000000;
            --banner-bg: #ffffff;
            --banner-text: #000000;
            --header-sub-bg: #ffffff;
            --header-sub-text: #000000;
            --input-bg: #ffffff;
            --input-border: #000000;
            --btn-primary-bg: #ffffff;
            --btn-primary-text: #000000;
            --btn-secondary-bg: #ffffff;
            --btn-secondary-text: #000000;
            --btn-secondary-border: #000000;
            --arrow-color: #000000;
            --badge-granted-bg: #ffffff;
            --badge-granted-text: #000000;
            --badge-granted-border: #000000;
            --badge-denied-bg: #ffffff;
            --badge-denied-text: #000000;
            --badge-denied-border: #000000;
            --stat-card-bg: #ffffff;
            --table-th-bg: #ffffff;
            --table-th-text: #000000;
            --highlight-bg: #f1f5f9;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #525659;
            color: #1c2440;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Control Bar (Hidden when printed) */
        .no-print-toolbar {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: #1c2440;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            border-bottom: 2px solid var(--cics-blue);
        }

        .toolbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.5px;
        }

        .toolbar-badge {
            background: var(--cics-blue);
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toolbar-btn {
            background: #293681;
            color: #fff;
            border: 1px solid #4274d9;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .toolbar-btn:hover {
            background: #4274d9;
            color: #fff;
        }

        .toolbar-btn-print {
            background: #10b981;
            border-color: #059669;
        }
        .toolbar-btn-print:hover {
            background: #059669;
        }

        .toolbar-select {
            background: #0f172a;
            color: #fff;
            border: 1px solid #334155;
            padding: 7px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        /* Printable Prototype Page Canvas */
        .prototype-canvas {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 0 60px;
            gap: 32px;
        }

        .prototype-page {
            width: 210mm;
            min-height: 297mm;
            padding: 14mm 16mm 14mm 16mm;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            position: relative;
            page-break-after: always;
            break-after: page;
        }

        /* Top Header in the style of prototype_system.pdf */
        .proto-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .proto-header .proto-tag {
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 2px;
            color: var(--cics-navy);
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .proto-header .proto-title {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: var(--cics-ink);
            text-transform: uppercase;
        }

        /* Main Double-Bordered Wireframe Containers */
        .wire-frame-box {
            border: 2px solid var(--frame-border);
            padding: 3px;
            background: #ffffff;
            margin-bottom: 2px;
        }

        .wire-frame-inner {
            border: 1px solid var(--frame-inner-border);
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Banner title box */
        .wire-banner {
            border: 1.5px solid var(--frame-border);
            background: var(--banner-bg);
            color: var(--banner-text);
            text-align: center;
            padding: 6px 12px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Subheader tabs bar */
        .wire-tabs {
            display: flex;
            border: 1px solid var(--frame-border);
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            overflow: hidden;
        }

        .wire-tab {
            flex: 1;
            padding: 5px 8px;
            background: var(--header-sub-bg);
            color: var(--header-sub-text);
            border-right: 1px solid var(--frame-border);
        }

        .wire-tab:last-child {
            border-right: none;
        }

        .wire-tab.active {
            background: var(--cics-navy);
            color: #ffffff;
        }

        /* Form Row & Field Layout */
        .wire-form-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.74rem;
        }

        .wire-label {
            width: 155px;
            font-weight: 600;
            color: var(--cics-ink);
            flex-shrink: 0;
        }

        .wire-input {
            flex: 1;
            height: 28px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            padding: 0 10px;
            font-size: 0.72rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #334155;
            border-radius: 2px;
        }

        .wire-input-val {
            font-family: 'Inter', sans-serif;
        }

        .wire-input-code {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .wire-input-icon {
            font-size: 0.75rem;
            color: var(--cics-muted);
        }

        /* Flow Arrow Between Panels */
        .wire-arrow-down {
            text-align: center;
            font-size: 1.4rem;
            line-height: 1;
            color: var(--arrow-color);
            margin: 10px 0;
            font-weight: 800;
        }

        /* Button elements */
        .wire-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 14px;
            font-size: 0.72rem;
            font-weight: 600;
            border: 1px solid var(--frame-border);
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
            border-radius: 2px;
            cursor: default;
        }

        .wire-btn-secondary {
            background: var(--btn-secondary-bg);
            color: var(--btn-secondary-text);
            border: 1px solid var(--btn-secondary-border);
        }

        .wire-btn-success {
            background: #10b981 !important;
            color: #ffffff !important;
            border-color: #059669 !important;
        }

        .wire-btn-danger {
            background: #ef4444 !important;
            color: #ffffff !important;
            border-color: #dc2626 !important;
        }

        .wire-btn-sm {
            padding: 2px 8px;
            font-size: 0.68rem;
        }

        .wire-action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
            font-size: 0.72rem;
        }

        .wire-link {
            color: var(--cics-blue);
            text-decoration: underline;
            font-size: 0.72rem;
            font-weight: 500;
        }

        /* Wireframe Table */
        .wire-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--frame-border);
            font-size: 0.69rem;
        }

        .wire-table th {
            border: 1px solid var(--frame-border);
            background: var(--table-th-bg);
            color: var(--table-th-text);
            padding: 5px 6px;
            font-weight: 700;
            text-align: center;
        }

        .wire-table td {
            border: 1px solid var(--frame-border);
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
        }

        .wire-table td.text-left {
            text-align: left;
        }

        /* Badges */
        .wire-badge {
            display: inline-block;
            padding: 2px 7px;
            font-size: 0.64rem;
            font-weight: 700;
            border-radius: 3px;
            border: 1px solid;
            text-transform: uppercase;
        }

        .wire-badge-granted {
            background: var(--badge-granted-bg);
            color: var(--badge-granted-text);
            border-color: var(--badge-granted-border);
        }

        .wire-badge-denied {
            background: var(--badge-denied-bg);
            color: var(--badge-denied-text);
            border-color: var(--badge-denied-border);
        }

        .wire-badge-blue {
            background: #dbeafe;
            color: #1e40af;
            border-color: #3b82f6;
        }

        .wire-badge-purple {
            background: #f3e8ff;
            color: #6b21a8;
            border-color: #a855f7;
        }

        .wire-badge-amber {
            background: #fef3c7;
            color: #92400e;
            border-color: #f59e0b;
        }

        /* Card grid */
        .wire-card-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .wire-stat-card {
            border: 1px solid var(--frame-border);
            background: var(--stat-card-bg);
            padding: 8px 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .wire-stat-card .stat-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--cics-muted);
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .wire-stat-card .stat-value {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--cics-navy);
        }

        /* Split layout with sidebar */
        .wire-layout-split {
            display: flex;
            gap: 10px;
        }

        .wire-sidebar {
            width: 140px;
            border: 1px solid var(--frame-border);
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 6px;
            background: #f8fafc;
            flex-shrink: 0;
        }

        .wire-sidebar-item {
            border: 1px solid var(--frame-border);
            background: #ffffff;
            padding: 5px 8px;
            font-size: 0.66rem;
            font-weight: 600;
            text-align: center;
            color: var(--cics-ink);
        }

        .wire-sidebar-item.active {
            background: var(--cics-navy);
            color: #ffffff;
            border-color: var(--cics-navy);
        }

        .wire-main-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Code/Terminal view */
        .wire-terminal {
            background: #0f172a;
            color: #38bdf8;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.66rem;
            padding: 10px;
            border: 1px solid var(--frame-border);
            line-height: 1.45;
            border-radius: 2px;
        }

        /* Page Footer Indicator */
        .proto-footer {
            margin-top: auto;
            padding-top: 8px;
            border-top: 1px dashed var(--cics-border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.65rem;
            color: var(--cics-muted);
            font-weight: 500;
        }

        .proto-footer-brand {
            font-weight: 700;
            color: var(--cics-navy);
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                margin: 0;
                padding: 0;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .prototype-canvas {
                padding: 0;
                gap: 0;
            }

            .prototype-page {
                box-shadow: none;
                margin: 0;
                width: 100% !important;
                min-height: 100vh !important;
                padding: 12mm 14mm !important;
                page-break-after: always !important;
                break-after: page !important;
            }

            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body class="color-theme">

    <!-- Screen Navigation Toolbar (No-Print) -->
    <header class="no-print-toolbar">
        <div class="toolbar-brand">
            <a href="<?= BASE_URL ?>/dashboard.php" style="color:#ffffff; text-decoration:none; display:flex; align-items:center; gap:8px;">
                <span>← Back to Portal</span>
            </a>
            <span>|</span>
            <span>SDASFC Prototype Suite</span>
            <span class="toolbar-badge">System Wireframes</span>
        </div>
        <div class="toolbar-actions">
            <select class="toolbar-select" id="pageJump" onchange="jumpToPage(this.value)">
                <option value="p1">Page 1: Authentication & Password Recovery</option>
                <option value="p2">Page 2: Dashboard Overview & Daily Metrics</option>
                <option value="p3">Page 3: Live Access Stream & Event Monitor</option>
                <option value="p4">Page 4: User Directory & Search Filters</option>
                <option value="p5">Page 5: Cardholder Enrollment Wizard</option>
                <option value="p6">Page 6: User Modification & Access Revocation</option>
                <option value="p7">Page 7: Access Log Audit & Filter Engine</option>
                <option value="p8">Page 8: Official Access Audit Slip (Print View)</option>
                <option value="p9">Page 9: Hardware Schematics & ESP32 Pinout</option>
                <option value="p10">Page 10: Serial Bridge Protocol & Failsafe</option>
                <option value="p11">Page 11: Admin Security Profile & Credentials</option>
                <option value="p12">Page 12: Architectural Flow & Color Guidelines</option>
            </select>
            <button class="toolbar-btn" onclick="toggleTheme()">
                <span id="themeBtnText">🎨 Theme: Uniform CICS Colors</span>
            </button>
            <a href="<?= BASE_URL ?>/../pdf/prototype_sdasfc.pdf" class="toolbar-btn" target="_blank" download="prototype_sdasfc.pdf">
                📥 Download PDF
            </a>
            <button class="toolbar-btn toolbar-btn-print" onclick="window.print()">
                🖨️ Print / Save as PDF
            </button>
        </div>
    </header>

    <main class="prototype-canvas">

        <!-- PAGE 1: AUTHENTICATION MODULE -->
        <section class="prototype-page" id="p1">
            <div class="proto-header">
                <div class="proto-tag">PROTOTYPE</div>
                <div class="proto-title">AUTHENTICATION & SECURITY MODULE</div>
            </div>

            <!-- Upper Wireframe: Admin Login -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Smart Door Automation System for CICS (SDASFC)</div>
                    <div class="wire-tabs">
                        <div class="wire-tab active">Administrator Portal Login</div>
                        <div class="wire-tab">System Operational Credentials</div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Username / Admin ID:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">admin</span>
                            <span class="wire-input-icon">👤</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Password:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">••••••••••••••••</span>
                            <span class="wire-input-icon">👁️</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Security Lockout Status:</div>
                        <div class="wire-input" style="background:#f1f5f9;">
                            <span class="wire-input-val" style="color:var(--cics-muted);">Active Protection: 30s lockout after 5 failed attempts</span>
                            <span class="wire-badge wire-badge-granted" style="font-size:0.6rem;">GUARD ACTIVE</span>
                        </div>
                    </div>

                    <div class="wire-action-row">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <input type="checkbox" checked style="accent-color:var(--cics-blue);">
                            <span style="font-size:0.72rem; color:var(--cics-ink);">Remember session on this browser</span>
                        </div>
                        <span class="wire-link">Forgot Password?</span>
                        <div class="wire-btn" style="padding:5px 22px;">Log In</div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Forgot Password Flow -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Forgot Password & Security Challenge Verification</div>
                    <div class="wire-tabs">
                        <div class="wire-tab active">Step 1: Account Lookup</div>
                        <div class="wire-tab active">Step 2: Security Question</div>
                        <div class="wire-tab">Step 3: Password Reset</div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Admin Username:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">admin</span>
                            <span class="wire-badge wire-badge-granted">VERIFIED</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Registered Security Question:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">What is the primary server room designation?</span>
                            <span class="wire-input-icon">🔒</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Security Answer:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">CICS-LAB-402</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">New Password:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">••••••••••••••••</span>
                            <span class="wire-input-icon">👁️</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Confirm New Password:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">••••••••••••••••</span>
                            <span class="wire-input-icon">👁️</span>
                        </div>
                    </div>

                    <div class="wire-action-row">
                        <span class="wire-link">Go Back to Login Page...</span>
                        <div class="wire-btn wire-btn-success">Verify & Reset Password</div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 1 of 12</span>
            </div>
        </section>

        <!-- PAGE 2: DASHBOARD & METRICS OVERVIEW -->
        <section class="prototype-page" id="p2">
            <div class="proto-header">
                <div class="proto-tag">ADMINISTRATIVE MODULE</div>
                <div class="proto-title">DASHBOARD & REAL-TIME MONITORING CENTER</div>
            </div>

            <!-- Upper Wireframe: Master Dashboard -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Admin Control Center — Door Access Telemetry</div>

                    <div class="wire-layout-split">
                        <!-- Navigation Sidebar -->
                        <div class="wire-sidebar">
                            <div class="wire-sidebar-item active">Dashboard Overview</div>
                            <div class="wire-sidebar-item">Manage Users</div>
                            <div class="wire-sidebar-item">Access Reports</div>
                            <div class="wire-sidebar-item">Hardware & Wiring</div>
                            <div class="wire-sidebar-item">Admin Profile</div>
                            <div class="wire-sidebar-item" style="color:var(--cics-danger);">Log out</div>
                        </div>

                        <!-- Main Dashboard Metrics -->
                        <div class="wire-main-panel">
                            <!-- 4 KPI Cards -->
                            <div class="wire-card-grid">
                                <div class="wire-stat-card">
                                    <span class="stat-label">Total Users</span>
                                    <span class="stat-value">148</span>
                                    <span style="font-size:0.58rem; color:var(--cics-muted); margin-top:2px;">Enrolled RFID</span>
                                </div>
                                <div class="wire-stat-card">
                                    <span class="stat-label">Today's Taps</span>
                                    <span class="stat-value" style="color:var(--cics-blue);">84</span>
                                    <span style="font-size:0.58rem; color:var(--cics-muted); margin-top:2px;">Total Scans</span>
                                </div>
                                <div class="wire-stat-card" style="border-color:#10b981;">
                                    <span class="stat-label" style="color:#059669;">Granted</span>
                                    <span class="stat-value" style="color:#10b981;">78</span>
                                    <span style="font-size:0.58rem; color:#059669; margin-top:2px;">92.8% Unlocked</span>
                                </div>
                                <div class="wire-stat-card" style="border-color:#ef4444;">
                                    <span class="stat-label" style="color:#dc2626;">Denied</span>
                                    <span class="stat-value" style="color:#ef4444;">6</span>
                                    <span style="font-size:0.58rem; color:#dc2626; margin-top:2px;">7.2% Rejected</span>
                                </div>
                            </div>

                            <!-- Visual 7-Day Trend Chart Wireframe -->
                            <div style="border:1px solid var(--frame-border); padding:8px; background:#ffffff;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.68rem; font-weight:700;">
                                    <span>7-Day Door Access Traffic (Granted vs Denied)</span>
                                    <span style="color:var(--cics-muted);">Chart.js Real-Time Feed</span>
                                </div>
                                <!-- Chart visual simulation -->
                                <div style="display:flex; align-items:flex-end; gap:12px; height:58px; padding-top:6px; border-bottom:1px solid var(--frame-border);">
                                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:2px;">
                                        <div style="width:14px; height:32px; background:#10b981; border-radius:2px;"></div>
                                        <span style="font-size:0.6rem;">Mon</span>
                                    </div>
                                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:2px;">
                                        <div style="width:14px; height:44px; background:#10b981; border-radius:2px;"></div>
                                        <span style="font-size:0.6rem;">Tue</span>
                                    </div>
                                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:2px;">
                                        <div style="width:14px; height:38px; background:#10b981; border-radius:2px;"></div>
                                        <span style="font-size:0.6rem;">Wed</span>
                                    </div>
                                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:2px;">
                                        <div style="width:14px; height:50px; background:#10b981; border-radius:2px;"></div>
                                        <span style="font-size:0.6rem;">Thu</span>
                                    </div>
                                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:2px;">
                                        <div style="width:14px; height:46px; background:#10b981; border-radius:2px;"></div>
                                        <span style="font-size:0.6rem;">Fri</span>
                                    </div>
                                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:2px;">
                                        <div style="width:14px; height:18px; background:#10b981; border-radius:2px;"></div>
                                        <span style="font-size:0.6rem;">Sat</span>
                                    </div>
                                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:2px;">
                                        <div style="width:14px; height:12px; background:#10b981; border-radius:2px;"></div>
                                        <span style="font-size:0.6rem;">Sun</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Live Door Status & Quick Controls -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Door Lock Controller & Telemetry State</div>

                    <div class="wire-form-row">
                        <div class="wire-label">Hardware Lock State:</div>
                        <div class="wire-input" style="background:#f8fafc;">
                            <span class="wire-input-val" style="font-weight:700;">SECURED — Solenoid Relay Normal (Active-Low)</span>
                            <span class="wire-badge wire-badge-granted">LOCKED ●</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Serial Bridge Interface:</div>
                        <div class="wire-input">
                            <span class="wire-input-code">COM3 @ 115200 Baud | PowerShell Host Active</span>
                            <span class="wire-badge wire-badge-blue">CONNECTED</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Audio Announcer State:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">DFPlayer Mini Online | Vol: 30 | SD Track Ready (0001, 0002)</span>
                            <span class="wire-badge wire-badge-purple">AUDIO READY</span>
                        </div>
                    </div>

                    <div class="wire-action-row">
                        <div style="font-size:0.7rem; color:var(--cics-muted);">
                            Last hardware ping: 2 seconds ago from ESP32 DOIT DevKit V1
                        </div>
                        <div style="display:flex; gap:8px;">
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">Trigger Test Beep</div>
                            <div class="wire-btn wire-btn-sm" style="background:var(--cics-navy);">Emergency Unlock (6000ms)</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 2 of 12</span>
            </div>
        </section>

        <!-- PAGE 3: LIVE ACCESS STREAM & EVENT MONITOR -->
        <section class="prototype-page" id="p3">
            <div class="proto-header">
                <div class="proto-tag">ADMINISTRATIVE MODULE</div>
                <div class="proto-title">LIVE ACCESS ACTIVITY & TAP INSPECTOR</div>
            </div>

            <!-- Upper Wireframe: Live Event Stream Feed -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Real-Time RFID Access Activity Stream (Door 01 - CICS Main Entrance)</div>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.7rem; margin-bottom:4px;">
                        <span style="color:var(--cics-muted);">Auto-refreshing via AJAX Polling (2000ms interval)</span>
                        <div style="display:flex; gap:6px;">
                            <span class="wire-badge wire-badge-granted">STREAM ACTIVE ●</span>
                            <span class="wire-btn wire-btn-secondary wire-btn-sm">Pause Feed</span>
                        </div>
                    </div>

                    <table class="wire-table">
                        <thead>
                            <tr>
                                <th style="width:75px;">Timestamp</th>
                                <th style="width:90px;">Scanned UID</th>
                                <th>Cardholder Name</th>
                                <th style="width:75px;">Role</th>
                                <th style="width:85px;">Access Result</th>
                                <th style="width:110px;">Hardware Reaction</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background:#f0fdf4;">
                                <td>14:10:05</td>
                                <td class="wire-input-code">0A 75 B4 02</td>
                                <td class="text-left" style="font-weight:600;">Prof. Glenn Delacruz</td>
                                <td><span class="wire-badge wire-badge-purple">Faculty</span></td>
                                <td><span class="wire-badge wire-badge-granted">GRANTED</span></td>
                                <td style="font-size:0.62rem; color:#065f46;">Relay Unlocked (6s) + MP3 Track 2</td>
                            </tr>
                            <tr style="background:#fef2f2;">
                                <td>14:02:18</td>
                                <td class="wire-input-code">FF 20 89 11</td>
                                <td class="text-left" style="color:var(--cics-danger);">Unknown Card (Unregistered)</td>
                                <td><span class="wire-badge" style="border-color:#94a3b8;">Guest</span></td>
                                <td><span class="wire-badge wire-badge-denied">DENIED</span></td>
                                <td style="font-size:0.62rem; color:#991b1b;">Buzzer Alarm + MP3 Track 1</td>
                            </tr>
                            <tr style="background:#f0fdf4;">
                                <td>13:45:50</td>
                                <td class="wire-input-code">E2 41 89 3F</td>
                                <td class="text-left" style="font-weight:600;">John Kevin Ramos</td>
                                <td><span class="wire-badge wire-badge-blue">Student</span></td>
                                <td><span class="wire-badge wire-badge-granted">GRANTED</span></td>
                                <td style="font-size:0.62rem; color:#065f46;">Relay Unlocked (6s) + MP3 Track 2</td>
                            </tr>
                            <tr style="background:#fef2f2;">
                                <td>13:30:12</td>
                                <td class="wire-input-code">1F 88 A3 90</td>
                                <td class="text-left" style="color:var(--cics-danger);">Carlos Reyes (Account Inactive)</td>
                                <td><span class="wire-badge wire-badge-amber">Staff</span></td>
                                <td><span class="wire-badge wire-badge-denied">DENIED</span></td>
                                <td style="font-size:0.62rem; color:#991b1b;">Buzzer Alarm + MP3 Track 1</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="wire-action-row">
                        <span style="font-size:0.68rem; color:var(--cics-muted);">Showing last 4 hardware access events.</span>
                        <div class="wire-btn wire-btn-secondary wire-btn-sm">View Full Access Logs Table</div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Latest Scan Inspector Card -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Active Hardware Tap Inspector & Decision Pipeline</div>

                    <div style="display:flex; gap:14px; align-items:center;">
                        <!-- Mock Photo Avatar -->
                        <div style="width:75px; height:85px; border:1.5px solid var(--frame-border); display:flex; flex-direction:column; align-items:center; justify-content:center; background:#f1f5f9; flex-shrink:0;">
                            <span style="font-size:1.8rem;">👨‍🏫</span>
                            <span style="font-size:0.55rem; color:var(--cics-muted); margin-top:4px;">ID PHOTO</span>
                        </div>

                        <!-- Tap Info Breakdown -->
                        <div style="flex:1; display:flex; flex-direction:column; gap:5px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:0.85rem; font-weight:800; color:var(--cics-navy);">Prof. Glenn Delacruz</span>
                                <span class="wire-badge wire-badge-granted" style="font-size:0.72rem; padding:3px 10px;">ACCESS GRANTED</span>
                            </div>
                            <div style="font-size:0.7rem; color:var(--cics-muted);">
                                Institutional ID: <strong style="color:var(--cics-ink);">CICS-FAC-001</strong> &nbsp;|&nbsp;
                                Role: <strong style="color:var(--cics-ink);">Faculty (Computer Science)</strong>
                            </div>
                            <div style="font-size:0.7rem; color:var(--cics-muted);">
                                Hardware RFID Tag UID: <span class="wire-input-code" style="color:var(--cics-navy); font-weight:700;">0A 75 B4 02</span>
                            </div>
                            <div style="font-size:0.68rem; background:#ecfdf5; border:1px solid #10b981; padding:4px 8px; border-radius:2px; color:#065f46;">
                                ✓ Relay Pin D26 Pulsed LOW for 6000ms | DFPlayer triggered Track 0002.mp3 ("Access Granted, Welcome")
                            </div>
                        </div>
                    </div>

                    <div class="wire-action-row" style="margin-top:8px;">
                        <span class="wire-link">Inspect Cardholder Profile...</span>
                        <div class="wire-btn wire-btn-secondary wire-btn-sm">Manual Override / Re-lock</div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 3 of 12</span>
            </div>
        </section>

        <!-- PAGE 4: USER MANAGEMENT — DIRECTORY & SEARCH -->
        <section class="prototype-page" id="p4">
            <div class="proto-header">
                <div class="proto-tag">USER MANAGEMENT MODULE</div>
                <div class="proto-title">CARDHOLDER DIRECTORY & SECURITY SEARCH</div>
            </div>

            <!-- Upper Wireframe: Directory Search & Filters -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Authorized Cardholder Management (Students, Faculty & Staff)</div>

                    <div style="display:flex; gap:8px; align-items:center;">
                        <div style="flex:1;">
                            <div class="wire-input">
                                <span class="wire-input-val" style="color:var(--cics-muted);">Search by name, ID number, or RFID UID...</span>
                                <span class="wire-input-icon">🔍</span>
                            </div>
                        </div>
                        <div class="wire-btn" style="background:var(--cics-navy); white-space:nowrap;">Search</div>
                        <div class="wire-btn wire-btn-secondary" style="white-space:nowrap;">Clear</div>
                        <div class="wire-btn wire-btn-success" style="white-space:nowrap;">+ Enroll New User</div>
                    </div>

                    <!-- Role & Status Filter Bar -->
                    <div class="wire-tabs">
                        <div class="wire-tab active">All Roles (148)</div>
                        <div class="wire-tab">Students (112)</div>
                        <div class="wire-tab">Faculty (24)</div>
                        <div class="wire-tab">Staff (12)</div>
                        <div class="wire-tab" style="background:#f1f5f9; color:var(--cics-navy);">Active Cards (142)</div>
                        <div class="wire-tab" style="background:#f1f5f9; color:var(--cics-danger);">Inactive / Revoked (6)</div>
                    </div>

                    <!-- Table of Users -->
                    <table class="wire-table">
                        <thead>
                            <tr>
                                <th style="width:30px;">#</th>
                                <th style="width:95px;">ID Number</th>
                                <th>Full Name</th>
                                <th style="width:95px;">RFID UID</th>
                                <th style="width:75px;">Role</th>
                                <th style="width:70px;">Status</th>
                                <th style="width:125px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="wire-input-code">CICS-FAC-001</td>
                                <td class="text-left" style="font-weight:600;">Prof. Glenn Delacruz</td>
                                <td class="wire-input-code">0A 75 B4 02</td>
                                <td><span class="wire-badge wire-badge-purple">Faculty</span></td>
                                <td><span class="wire-badge wire-badge-granted">Active</span></td>
                                <td>
                                    <span class="wire-btn wire-btn-secondary wire-btn-sm">Edit</span>
                                    <span class="wire-btn wire-btn-danger wire-btn-sm">Delete</span>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="wire-input-code">2023-0104-CS</td>
                                <td class="text-left" style="font-weight:600;">Juan Dela Cruz</td>
                                <td class="wire-input-code">93 39 6E 1B</td>
                                <td><span class="wire-badge wire-badge-blue">Student</span></td>
                                <td><span class="wire-badge wire-badge-granted">Active</span></td>
                                <td>
                                    <span class="wire-btn wire-btn-secondary wire-btn-sm">Edit</span>
                                    <span class="wire-btn wire-btn-danger wire-btn-sm">Delete</span>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td class="wire-input-code">2022-0922-IT</td>
                                <td class="text-left" style="font-weight:600;">Maria Santos</td>
                                <td class="wire-input-code">E2 41 89 3F</td>
                                <td><span class="wire-badge wire-badge-blue">Student</span></td>
                                <td><span class="wire-badge wire-badge-granted">Active</span></td>
                                <td>
                                    <span class="wire-btn wire-btn-secondary wire-btn-sm">Edit</span>
                                    <span class="wire-btn wire-btn-danger wire-btn-sm">Delete</span>
                                </td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td class="wire-input-code">CICS-STF-014</td>
                                <td class="text-left" style="font-weight:600;">Carlos Reyes</td>
                                <td class="wire-input-code">1F 88 A3 90</td>
                                <td><span class="wire-badge wire-badge-amber">Staff</span></td>
                                <td><span class="wire-badge wire-badge-denied">Inactive</span></td>
                                <td>
                                    <span class="wire-btn wire-btn-secondary wire-btn-sm">Edit</span>
                                    <span class="wire-btn wire-btn-danger wire-btn-sm">Delete</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="wire-action-row">
                        <span style="font-size:0.68rem; color:var(--cics-muted);">Showing 1 to 4 of 148 entries</span>
                        <div style="display:flex; gap:4px;">
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">Previous</div>
                            <div class="wire-btn wire-btn-sm" style="background:var(--cics-navy);">1</div>
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">2</div>
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">Next</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Cardholder Quick Summary & Audit Link -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Selected Cardholder Security Summary (RFID Tag #93 39 6E 1B)</div>

                    <div class="wire-form-row">
                        <div class="wire-label">Hardware Authorization:</div>
                        <div class="wire-input" style="background:#f0fdf4;">
                            <span class="wire-input-val" style="color:#065f46; font-weight:600;">Permanent Whitelist & Offline Emergency Bypass Key</span>
                            <span class="wire-badge wire-badge-granted">MASTER / ACTIVE</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Total Door Taps Logged:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">142 Swipes recorded (140 Granted, 2 Denied during maintenance)</span>
                            <span class="wire-link">Inspect Taps History...</span>
                        </div>
                    </div>

                    <div class="wire-action-row">
                        <div class="wire-btn wire-btn-secondary wire-btn-sm">Temporarily Deactivate Card Access</div>
                        <div class="wire-btn wire-btn-sm" style="background:var(--cics-blue);">Generate RFID Card Slip</div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 4 of 12</span>
            </div>
        </section>

        <!-- PAGE 5: USER REGISTRATION — ENROLLMENT WIZARD -->
        <section class="prototype-page" id="p5">
            <div class="proto-header">
                <div class="proto-tag">USER MANAGEMENT MODULE</div>
                <div class="proto-title">CARDHOLDER ENROLLMENT WIZARD (STEP 1 & 2)</div>
            </div>

            <!-- Upper Wireframe: Step 1 Identity Registration -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">New Cardholder Enrollment — Step 1: User Identity & Academic Role</div>
                    <div class="wire-tabs">
                        <div class="wire-tab active">1. Identity & Institutional Role</div>
                        <div class="wire-tab">2. Hardware RFID Binding & Tap</div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Institutional ID Number:</div>
                        <div class="wire-input">
                            <span class="wire-input-code">2024-CICS-0192</span>
                            <span class="wire-input-icon">🪪</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Full Name:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">Juan Dela Cruz</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Institutional Role:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">Student (BS Information Technology)</span>
                            <span class="wire-input-icon">▾</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Account Status:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">Active (Access Allowed immediately upon registration)</span>
                            <span class="wire-badge wire-badge-granted">ACTIVE</span>
                        </div>
                    </div>

                    <div style="background:#f1f5f9; padding:6px 10px; font-size:0.68rem; color:var(--cics-muted); border-left:3px solid var(--cics-blue);">
                        ℹ️ Notice: All cardholder records are synchronized to the local database and cached for fast sub-millisecond door clearance.
                    </div>

                    <div class="wire-action-row" style="justify-content:flex-end;">
                        <div class="wire-btn" style="background:var(--cics-navy);">Next Step: Bind RFID Card →</div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Step 2 Hardware RFID Binding -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">New Cardholder Enrollment — Step 2: Hardware RFID Card Binding</div>
                    <div class="wire-tabs">
                        <div class="wire-tab">1. Identity & Institutional Role</div>
                        <div class="wire-tab active">2. Hardware RFID Binding & Tap</div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">RFID Card UID:</div>
                        <div class="wire-input" style="border:1.5px solid var(--cics-blue); background:#eff6ff;">
                            <span class="wire-input-code" style="color:var(--cics-navy); font-weight:700;">0A 75 B4 02</span>
                            <span class="wire-badge wire-badge-granted">CAPTURED FROM HARDWARE</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Live Tap Listener:</div>
                        <div style="flex:1; display:flex; gap:8px;">
                            <div class="wire-btn" style="background:#2563eb; flex:1;">
                                📡 Fetch Last Scanned Card from Hardware Reader
                            </div>
                            <div class="wire-btn wire-btn-secondary" style="font-size:0.68rem;">
                                Clear Tag
                            </div>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Profile Photo (JPG/PNG):</div>
                        <div class="wire-input">
                            <span class="wire-input-val" style="color:var(--cics-muted);">juan_delacruz_id.jpg (240 KB)</span>
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">UPLOAD FILE</div>
                        </div>
                    </div>

                    <div style="background:#ecfdf5; border:1px solid #10b981; padding:6px 10px; font-size:0.68rem; color:#065f46; border-radius:2px;">
                        ✓ Scanner Signal Detected: RC522 Reader on COM3 received UID `0A 75 B4 02` at 14:10:05.
                    </div>

                    <div class="wire-action-row">
                        <div class="wire-btn wire-btn-secondary">Cancel</div>
                        <div class="wire-btn wire-btn-success" style="padding:6px 20px;">Complete Registration & Authorize Card</div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 5 of 12</span>
            </div>
        </section>

        <!-- PAGE 6: USER MODIFICATION & DELETION SAFETY -->
        <section class="prototype-page" id="p6">
            <div class="proto-header">
                <div class="proto-tag">USER MANAGEMENT MODULE</div>
                <div class="proto-title">MODIFY PROFILE & ACCESS REVOCATION</div>
            </div>

            <!-- Upper Wireframe: Edit Cardholder Details -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Edit Cardholder Profile & Re-assign RFID Keycard</div>

                    <div class="wire-form-row">
                        <div class="wire-label">Institutional ID Number:</div>
                        <div class="wire-input">
                            <span class="wire-input-code">2023-0104-CS</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Full Name:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">Juan Dela Cruz</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Institutional Role:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">Student</span>
                            <span class="wire-input-icon">▾</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Assigned RFID Tag UID:</div>
                        <div style="flex:1; display:flex; gap:8px;">
                            <div class="wire-input" style="flex:1;">
                                <span class="wire-input-code">0A 75 B4 02</span>
                            </div>
                            <div class="wire-btn wire-btn-secondary wire-btn-sm" style="white-space:nowrap;">
                                🔄 Re-Scan New Card
                            </div>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Access Permissions Status:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">Active (Authorized to open Solenoid Door Lock)</span>
                            <span class="wire-badge wire-badge-granted">ACTIVE</span>
                        </div>
                    </div>

                    <div class="wire-action-row">
                        <div class="wire-btn wire-btn-secondary">Cancel</div>
                        <div class="wire-btn" style="background:var(--cics-navy);">Save Profile Updates</div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Delete / Revoke Access Confirmation Dialog -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner" style="background:var(--cics-danger); color:#ffffff;">Revoke Door Access / Delete Cardholder</div>

                    <div style="background:#fef2f2; border:1px solid #ef4444; padding:12px; border-radius:2px; text-align:center;">
                        <div style="font-size:0.9rem; font-weight:800; color:#991b1b; margin-bottom:4px;">
                            ⚠️ Confirm Access Revocation for Cardholder?
                        </div>
                        <div style="font-size:0.72rem; color:#7f1d1d; line-height:1.4;">
                            Are you sure you want to remove <strong>Juan Dela Cruz (2023-0104-CS)</strong>?
                            <br>
                            Their physical RFID card <code>0A 75 B4 02</code> will immediately be blocked and rejected by the door lock.
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Revocation Reason:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">Graduated / Transferred / Card Lost</span>
                            <span class="wire-input-icon">▾</span>
                        </div>
                    </div>

                    <div class="wire-action-row">
                        <div class="wire-btn wire-btn-secondary">Cancel & Keep Active</div>
                        <div style="display:flex; gap:8px;">
                            <div class="wire-btn wire-btn-secondary" style="color:var(--cics-warning); border-color:var(--cics-warning);">
                                Set Inactive (Soft Delete - Preserves Logs)
                            </div>
                            <div class="wire-btn wire-btn-danger">
                                Confirm Permanent Delete
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 6 of 12</span>
            </div>
        </section>

        <!-- PAGE 7: AUDIT & REPORTING — ACCESS LOG AUDIT MODULE -->
        <section class="prototype-page" id="p7">
            <div class="proto-header">
                <div class="proto-tag">SECURITY AUDIT & REPORTING</div>
                <div class="proto-title">ACCESS LOG AUDIT & MULTI-PARAMETRIC FILTER</div>
            </div>

            <!-- Upper Wireframe: Filter Controls & Export Engine -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Door Access History & Multi-Criteria Log Audit</div>

                    <!-- Filter Form Grid -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        <div class="wire-form-row">
                            <div class="wire-label" style="width:110px;">Date Range:</div>
                            <div class="wire-input">
                                <span class="wire-input-val">2026-09-01 to 2026-09-21</span>
                                <span class="wire-input-icon">📅</span>
                            </div>
                        </div>

                        <div class="wire-form-row">
                            <div class="wire-label" style="width:110px;">Access Result:</div>
                            <div class="wire-input">
                                <span class="wire-input-val">All (Granted & Denied)</span>
                                <span class="wire-input-icon">▾</span>
                            </div>
                        </div>

                        <div class="wire-form-row">
                            <div class="wire-label" style="width:110px;">Role Filter:</div>
                            <div class="wire-input">
                                <span class="wire-input-val">All Roles (Student, Faculty, Staff)</span>
                                <span class="wire-input-icon">▾</span>
                            </div>
                        </div>

                        <div class="wire-form-row">
                            <div class="wire-label" style="width:110px;">Search Keyword:</div>
                            <div class="wire-input">
                                <span class="wire-input-val" style="color:var(--cics-muted);">Filter by Name, ID, or UID...</span>
                                <span class="wire-input-icon">🔍</span>
                            </div>
                        </div>
                    </div>

                    <!-- Metric Filter Summary Strip -->
                    <div style="display:flex; justify-content:space-between; background:#e8eef9; padding:6px 10px; border:1px solid var(--cics-blue); font-size:0.68rem; font-weight:700;">
                        <span>Filtered Taps: <strong style="color:var(--cics-navy);">1,420</strong></span>
                        <span>Granted Swipes: <strong style="color:#10b981;">1,350 (95.1%)</strong></span>
                        <span>Denied / Rejections: <strong style="color:#ef4444;">70 (4.9%)</strong></span>
                        <span>Unregistered Cards: <strong style="color:var(--cics-warning);">42</strong></span>
                    </div>

                    <div class="wire-action-row">
                        <div style="display:flex; gap:6px;">
                            <div class="wire-btn" style="background:var(--cics-navy);">Apply Filters</div>
                            <div class="wire-btn wire-btn-secondary">Reset</div>
                        </div>
                        <div style="display:flex; gap:6px;">
                            <div class="wire-btn wire-btn-secondary">📊 Export to CSV</div>
                            <div class="wire-btn wire-btn-secondary">🖨️ Print Report</div>
                            <div class="wire-btn wire-btn-success">📑 Export Official PDF</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Detailed Audit Records Table -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Verified Security Access Trail (Door 01 - CICS Main Door)</div>

                    <table class="wire-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">Log ID</th>
                                <th style="width:115px;">Date & Time</th>
                                <th>Cardholder / Scanned Identity</th>
                                <th style="width:85px;">RFID UID</th>
                                <th style="width:70px;">Role</th>
                                <th style="width:75px;">Result</th>
                                <th style="width:120px;">Access Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#1420</td>
                                <td>2026-09-21 14:10:05</td>
                                <td class="text-left" style="font-weight:600;">Prof. Glenn Delacruz</td>
                                <td class="wire-input-code">0A 75 B4 02</td>
                                <td><span class="wire-badge wire-badge-purple">Faculty</span></td>
                                <td><span class="wire-badge wire-badge-granted">GRANTED</span></td>
                                <td class="text-left" style="color:#065f46;">Authorized Access (OK)</td>
                            </tr>
                            <tr>
                                <td>#1419</td>
                                <td>2026-09-21 14:02:18</td>
                                <td class="text-left" style="color:var(--cics-danger);">Unknown Cardholder</td>
                                <td class="wire-input-code">FF 20 89 11</td>
                                <td><span class="wire-badge" style="border-color:#94a3b8;">None</span></td>
                                <td><span class="wire-badge wire-badge-denied">DENIED</span></td>
                                <td class="text-left" style="color:#991b1b;">Unknown Card UID</td>
                            </tr>
                            <tr>
                                <td>#1418</td>
                                <td>2026-09-21 13:45:50</td>
                                <td class="text-left" style="font-weight:600;">Carlos Reyes</td>
                                <td class="wire-input-code">1F 88 A3 90</td>
                                <td><span class="wire-badge wire-badge-amber">Staff</span></td>
                                <td><span class="wire-badge wire-badge-denied">DENIED</span></td>
                                <td class="text-left" style="color:#991b1b;">Inactive User Account</td>
                            </tr>
                            <tr>
                                <td>#1417</td>
                                <td>2026-09-21 13:30:12</td>
                                <td class="text-left" style="font-weight:600;">Maria Santos</td>
                                <td class="wire-input-code">E2 41 89 3F</td>
                                <td><span class="wire-badge wire-badge-blue">Student</span></td>
                                <td><span class="wire-badge wire-badge-granted">GRANTED</span></td>
                                <td class="text-left" style="color:#065f46;">Authorized Access (OK)</td>
                            </tr>
                            <tr>
                                <td>#1416</td>
                                <td>2026-09-21 12:15:30</td>
                                <td class="text-left" style="font-weight:600;">System Emergency Key</td>
                                <td class="wire-input-code">93 39 6E 1B</td>
                                <td><span class="wire-badge wire-badge-granted">Admin</span></td>
                                <td><span class="wire-badge wire-badge-granted">GRANTED</span></td>
                                <td class="text-left" style="color:#065f46;">Master Card Bypass</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="wire-action-row">
                        <span style="font-size:0.68rem; color:var(--cics-muted);">Showing 1 to 5 of 1,420 filtered records</span>
                        <div style="display:flex; gap:4px;">
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">« First</div>
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">‹ Prev</div>
                            <div class="wire-btn wire-btn-sm" style="background:var(--cics-navy);">1</div>
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">2</div>
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">3</div>
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">Next ›</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 7 of 12</span>
            </div>
        </section>

        <!-- PAGE 8: AUDIT & REPORTING — OFFICIAL PRINTABLE AUDIT SLIP -->
        <section class="prototype-page" id="p8">
            <div class="proto-header">
                <div class="proto-tag">SECURITY AUDIT & REPORTING</div>
                <div class="proto-title">OFFICIAL ACCESS AUDIT SLIP (FORMAL PRINT VIEW)</div>
            </div>

            <!-- Upper Wireframe: Institutional Header & Metadata -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div style="text-align:center; padding:6px 0; border-bottom:1.5px solid var(--frame-border);">
                        <div style="font-size:0.75rem; font-weight:800; letter-spacing:1px; color:var(--cics-navy);">
                            CAGAYAN STATE UNIVERSITY — PIAT CAMPUS
                        </div>
                        <div style="font-size:0.68rem; font-weight:700; color:var(--cics-ink); margin-top:1px;">
                            College of Information and Computing Sciences (CICS)
                        </div>
                        <div style="font-size:0.62rem; color:var(--cics-muted); text-transform:uppercase; letter-spacing:0.5px;">
                            Smart Door Automation System (SDASFC) Access Audit Record
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:0.7rem; margin-top:4px;">
                        <div>
                            <div><strong>Slip Reference No:</strong> <span class="wire-input-code">CSU-SDASFC-2026-0921</span></div>
                            <div style="margin-top:2px;"><strong>Audit Scope:</strong> CICS Main Computer Lab Door 01</div>
                            <div style="margin-top:2px;"><strong>Reporting Period:</strong> 2026-09-01 to 2026-09-21</div>
                        </div>
                        <div>
                            <div><strong>Date Generated:</strong> 2026-09-21 15:30:00</div>
                            <div style="margin-top:2px;"><strong>Generated By:</strong> Administrator (Engr. Glenn Delacruz)</div>
                            <div style="margin-top:2px;"><strong>Integrity Status:</strong> <span class="wire-badge wire-badge-granted">CRYPTOGRAPHICALLY VERIFIED</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Statistical Summary, Audit Log Extract & Signatures -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Official Summary & Departmental Clearance Sign-Off</div>

                    <!-- 3-Column Statistical Summary -->
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:6px;">
                        <div style="border:1px solid var(--frame-border); padding:6px; text-align:center; background:#f8fafc;">
                            <div style="font-size:0.62rem; color:var(--cics-muted); font-weight:600;">TOTAL ATTEMPTS</div>
                            <div style="font-size:1.1rem; font-weight:800; color:var(--cics-navy);">1,420</div>
                        </div>
                        <div style="border:1px solid var(--frame-border); padding:6px; text-align:center; background:#f0fdf4;">
                            <div style="font-size:0.62rem; color:#065f46; font-weight:600;">CLEARED / UNLOCKED</div>
                            <div style="font-size:1.1rem; font-weight:800; color:#10b981;">1,350</div>
                        </div>
                        <div style="border:1px solid var(--frame-border); padding:6px; text-align:center; background:#fef2f2;">
                            <div style="font-size:0.62rem; color:#991b1b; font-weight:600;">SECURITY DENIALS</div>
                            <div style="font-size:1.1rem; font-weight:800; color:#ef4444;">70</div>
                        </div>
                    </div>

                    <!-- Security Incident Breakdown -->
                    <div style="border:1px solid var(--frame-border); padding:8px; font-size:0.68rem; background:#ffffff;">
                        <strong style="color:var(--cics-navy);">Security Audit Observations & Incident Notes:</strong>
                        <ul style="margin-left:16px; margin-top:3px; line-height:1.4; color:#334155;">
                            <li>Recorded 42 unauthorized swipes from non-registered RFID keycards (all denied with Track 0001 audio alert).</li>
                            <li>Hardware offline emergency master card (<code>93 39 6E 1B</code>) successfully authorized during network test.</li>
                            <li>Relay solenoid lock operated normally with zero brownout resets during the audit window.</li>
                        </ul>
                    </div>

                    <!-- Official Signatory Section -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:14px; padding-top:10px;">
                        <div style="text-align:center;">
                            <div style="height:25px; border-bottom:1px solid var(--frame-border); width:80%; margin:0 auto;"></div>
                            <div style="font-size:0.68rem; font-weight:700; color:var(--cics-ink); margin-top:3px;">
                                ENGR. GLENN DELACRUZ
                            </div>
                            <div style="font-size:0.6rem; color:var(--cics-muted);">System Administrator / Hardware Engineer</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="height:25px; border-bottom:1px solid var(--frame-border); width:80%; margin:0 auto;"></div>
                            <div style="font-size:0.68rem; font-weight:700; color:var(--cics-ink); margin-top:3px;">
                                CICS COLLEGE DEAN
                            </div>
                            <div style="font-size:0.6rem; color:var(--cics-muted);">College of Information and Computing Sciences</div>
                        </div>
                    </div>

                    <div class="wire-action-row" style="margin-top:10px;">
                        <span class="wire-btn wire-btn-secondary">Close Slip View</span>
                        <div class="wire-btn wire-btn-success">🖨️ Print Formal Slip (A4)</div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 8 of 12</span>
            </div>
        </section>

        <!-- PAGE 9: HARDWARE MODULE — ESP32 PINOUT & SCHEMATICS -->
        <section class="prototype-page" id="p9">
            <div class="proto-header">
                <div class="proto-tag">HARDWARE INTEGRATION MODULE</div>
                <div class="proto-title">ESP32 CIRCUIT PINOUT & SCHEMATICS VIEWER</div>
            </div>

            <!-- Upper Wireframe: Wiring Mode Selector & Specifications -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Microcontroller Interface & Pin Allocation Matrix</div>
                    <div class="wire-tabs">
                        <div class="wire-tab active">● Breadboard Prototyping Mode</div>
                        <div class="wire-tab">○ Direct PCB Wiring (Without Breadboard)</div>
                    </div>

                    <table class="wire-table">
                        <thead>
                            <tr>
                                <th style="width:120px;">Hardware Subsystem</th>
                                <th style="width:110px;">ESP32 Pinout</th>
                                <th style="width:100px;">Operating Voltage</th>
                                <th>Operational Specification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-left" style="font-weight:700;">RFID-RC522 Scanner</td>
                                <td class="wire-input-code">SDA:D21, SCK:D18, MOSI:D23, MISO:D19</td>
                                <td>3.3V DC (Regulated)</td>
                                <td class="text-left">13.56 MHz SPI Reader, reads MIFARE Classic 1K Cards</td>
                            </tr>
                            <tr>
                                <td class="text-left" style="font-weight:700;">1-Channel 5V Relay</td>
                                <td class="wire-input-code">IN: GPIO D26</td>
                                <td>5V DC (Coil)</td>
                                <td class="text-left">Active-LOW trigger controls 12V Solenoid (6000ms pulse)</td>
                            </tr>
                            <tr>
                                <td class="text-left" style="font-weight:700;">DFPlayer Mini MP3</td>
                                <td class="wire-input-code">RX: GPIO D17, TX: GPIO D16</td>
                                <td>5V DC (VCC)</td>
                                <td class="text-left">Serial2 UART @ 9600 Baud, 1K resistor on RX pin, Vol: 30</td>
                            </tr>
                            <tr>
                                <td class="text-left" style="font-weight:700;">Status Indicator & Buzzer</td>
                                <td class="wire-input-code">Buzzer: D4, Green: D2, Red: D15</td>
                                <td>3.3V / 5V DC</td>
                                <td class="text-left">Auditory tone and visual feedback on clearance decision</td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="background:#eaf3f8; padding:6px 10px; font-size:0.68rem; color:var(--cics-navy); border-left:3px solid var(--cics-blue);">
                        ⚡ Brownout Protection: Dedicated 12V/2A power supply stepped down to 5V via LM2596 buck converter prevents ESP32 reboots during relay inrush.
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Interactive Schematic Visualizer -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Interactive Schematic Circuit Diagram Viewport</div>

                    <!-- Schematic Visual Diagram Simulation -->
                    <div style="border:1.5px solid var(--frame-border); padding:10px; background:#f8fafc; display:flex; justify-content:space-between; align-items:center; gap:8px;">
                        <div style="border:1px solid #3b82f6; background:#eff6ff; padding:8px; border-radius:4px; text-align:center; flex:1;">
                            <div style="font-size:0.68rem; font-weight:800; color:#1e40af;">RC522 RFID</div>
                            <div style="font-size:0.58rem; color:var(--cics-muted);">SPI Interface</div>
                            <div style="font-size:0.62rem; color:#1e40af; margin-top:2px;">3.3V, GND, D21, D18</div>
                        </div>

                        <span style="color:var(--cics-blue); font-weight:800;">⟷</span>

                        <div style="border:2px solid var(--cics-navy); background:#ffffff; padding:10px; border-radius:4px; text-align:center; flex:1.2; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                            <div style="font-size:0.75rem; font-weight:800; color:var(--cics-navy);">ESP32 DEVKIT V1</div>
                            <div style="font-size:0.58rem; color:#059669; font-weight:700;">115200 BAUD CORE</div>
                            <div style="font-size:0.62rem; color:var(--cics-ink); margin-top:2px;">GPIO 26 / 16 / 17 / 21</div>
                        </div>

                        <span style="color:var(--cics-blue); font-weight:800;">⟶</span>

                        <div style="display:flex; flex-direction:column; gap:6px; flex:1.2;">
                            <div style="border:1px solid #10b981; background:#f0fdf4; padding:5px; border-radius:3px; text-align:center;">
                                <div style="font-size:0.65rem; font-weight:700; color:#065f46;">5V Relay + 12V Solenoid</div>
                                <div style="font-size:0.58rem; color:#065f46;">GPIO D26 (6000ms Hold)</div>
                            </div>
                            <div style="border:1px solid #a855f7; background:#faf5ff; padding:5px; border-radius:3px; text-align:center;">
                                <div style="font-size:0.65rem; font-weight:700; color:#6b21a8;">DFPlayer Mini + Speaker</div>
                                <div style="font-size:0.58rem; color:#6b21a8;">UART2 (D16/D17) Vol:30</div>
                            </div>
                        </div>
                    </div>

                    <div class="wire-action-row">
                        <span class="wire-link">Switch to Direct PCB Wiring Diagram...</span>
                        <div style="display:flex; gap:6px;">
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">Download SVG Diagram</div>
                            <div class="wire-btn wire-btn-sm" style="background:var(--cics-navy);">Open Fullscreen Schematic</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 9 of 12</span>
            </div>
        </section>

        <!-- PAGE 10: HARDWARE COMMUNICATION — SERIAL BRIDGE -->
        <section class="prototype-page" id="p10">
            <div class="proto-header">
                <div class="proto-tag">HARDWARE INTEGRATION MODULE</div>
                <div class="proto-title">SERIAL BRIDGE PROTOCOL & OFFLINE FAILSAFE</div>
            </div>

            <!-- Upper Wireframe: Serial Bridge Terminal -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Serial Bridge Communication Engine (PowerShell / Python / PHP)</div>
                    <div style="display:flex; justify-content:space-between; font-size:0.7rem;">
                        <span>Port: <strong class="wire-input-code">COM3</strong> &nbsp;|&nbsp; Baud: <strong class="wire-input-code">115200</strong> &nbsp;|&nbsp; Buffer: <strong class="wire-input-code">8-N-1</strong></span>
                        <span class="wire-badge wire-badge-granted">BRIDGE ONLINE ●</span>
                    </div>

                    <!-- Terminal Output Box -->
                    <div class="wire-terminal">
                        [BRIDGE 14:10:00] Initialized serial bridge on COM3 at 115200 baud.<br>
                        [ESP32  14:10:02] SDASFC Firmware v2.4 initialized. Ready for RFID scans.<br>
                        [ESP32  14:10:05] UID:0A 75 B4 02<br>
                        [BRIDGE 14:10:05] Captured UID '0A 75 B4 02'. Forwarding HTTP POST to /public/api/rfid_scan.php...<br>
                        [SERVER 14:10:05] HTTP 200 OK -> {"access":"granted","name":"Glenn Delacruz","relay_ms":6000}<br>
                        [BRIDGE 14:10:05] Transmitting decision command to ESP32: 'GRANT'<br>
                        [ESP32  14:10:05] COMMAND: GRANT -> Relay Pin 26 LOW (6000ms). Audio Track 0002 triggered.
                    </div>

                    <div class="wire-action-row">
                        <span style="font-size:0.68rem; color:var(--cics-muted);">Bridges: serial_bridge.ps1, serial_bridge.py, serial_bridge.php</span>
                        <div style="display:flex; gap:6px;">
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">Clear Terminal</div>
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">Restart Bridge Daemon</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Master Emergency Key Card Failsafe -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Hardware-Level Offline Failsafe & Emergency Master Bypass</div>

                    <div class="wire-form-row">
                        <div class="wire-label">Master Key Card UID:</div>
                        <div class="wire-input" style="border:1.5px solid #10b981; background:#f0fdf4;">
                            <span class="wire-input-code" style="color:#065f46; font-weight:800;">93 39 6E 1B</span>
                            <span class="wire-badge wire-badge-granted">FIRMWARE HARDCODED</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Autonomous Bypass Logic:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">If PC bridge / web server is offline, ESP32 unlocks relay locally.</span>
                            <span class="wire-badge wire-badge-purple">FAIL-SAFE</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Relay Unlock Duration:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">6,000 milliseconds (UNLOCK_HOLD_MS = 6000)</span>
                            <span class="wire-input-code">6.0s</span>
                        </div>
                    </div>

                    <div class="wire-action-row">
                        <div style="font-size:0.68rem; color:var(--cics-muted);">Diagnostic Simulated Test Controls:</div>
                        <div style="display:flex; gap:6px;">
                            <div class="wire-btn wire-btn-secondary wire-btn-sm">Send 'DENY' Test</div>
                            <div class="wire-btn wire-btn-sm" style="background:#10b981;">Send 'GRANT' Test</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 10 of 12</span>
            </div>
        </section>

        <!-- PAGE 11: ADMINISTRATOR PROFILE & SECURITY -->
        <section class="prototype-page" id="p11">
            <div class="proto-header">
                <div class="proto-tag">ADMINISTRATIVE MODULE</div>
                <div class="proto-title">ADMINISTRATOR CREDENTIALS & SECURITY PROFILE</div>
            </div>

            <!-- Upper Wireframe: Admin Profile Info -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Administrator Profile & Security Challenge Configuration</div>

                    <div class="wire-form-row">
                        <div class="wire-label">Username:</div>
                        <div class="wire-input" style="background:#f1f5f9;">
                            <span class="wire-input-val" style="font-weight:700;">admin</span>
                            <span class="wire-badge" style="border-color:#94a3b8;">SYSTEM ROOT</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Full Name:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">Engr. Glenn Delacruz</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Configured Security Question:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">What is the primary server room designation?</span>
                            <span class="wire-input-icon">▾</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Security Answer:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">•••••••••••••••• (Hash stored via Bcrypt)</span>
                            <span class="wire-badge wire-badge-granted">ENCRYPTED</span>
                        </div>
                    </div>

                    <div class="wire-action-row" style="justify-content:flex-end;">
                        <div class="wire-btn" style="background:var(--cics-navy);">Save Profile & Security Question</div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Password Update & Session Logout -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Update Master Password & Session Security</div>

                    <div class="wire-form-row">
                        <div class="wire-label">Current Password:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">••••••••••••••••</span>
                            <span class="wire-input-icon">👁️</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">New Password:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">••••••••••••••••</span>
                            <span class="wire-input-icon">👁️</span>
                        </div>
                    </div>

                    <div class="wire-form-row">
                        <div class="wire-label">Confirm New Password:</div>
                        <div class="wire-input">
                            <span class="wire-input-val">••••••••••••••••</span>
                            <span class="wire-input-icon">👁️</span>
                        </div>
                    </div>

                    <div style="background:#f8fafc; border:1px solid var(--cics-border-light); padding:6px 10px; font-size:0.68rem; display:flex; gap:16px; color:var(--cics-muted);">
                        <span>✓ Minimum 8 characters</span>
                        <span>✓ Uppercase & lowercase</span>
                        <span>✓ Numbers & special symbols</span>
                    </div>

                    <div class="wire-action-row">
                        <div class="wire-btn wire-btn-secondary" style="color:var(--cics-danger); border-color:var(--cics-danger);">
                            Log Out of All Active Sessions
                        </div>
                        <div style="display:flex; gap:8px;">
                            <div class="wire-btn wire-btn-secondary">Log Out of System</div>
                            <div class="wire-btn wire-btn-success">Update Password</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 11 of 12</span>
            </div>
        </section>

        <!-- PAGE 12: SYSTEM ARCHITECTURE & UNIFORM COLOR DESIGN -->
        <section class="prototype-page" id="p12">
            <div class="proto-header">
                <div class="proto-tag">SPECIFICATION & DESIGN SYSTEM</div>
                <div class="proto-title">SYSTEM ARCHITECTURE & UNIFORM COLOR PALETTE</div>
            </div>

            <!-- Upper Wireframe: Architectural Data Flow Diagram -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">End-to-End Hardware & Software Access Control Pipeline</div>

                    <!-- Flow Steps Box -->
                    <div style="display:flex; flex-direction:column; gap:6px; font-size:0.68rem;">
                        <div style="display:flex; align-items:center; gap:8px; background:#eff6ff; padding:6px 10px; border:1px solid #bfdbfe; border-radius:3px;">
                            <span style="font-weight:800; color:#1e40af; width:65px;">STEP 1:</span>
                            <span style="color:#1e3a8a;">Cardholder taps physical RFID card on <strong>RC522 Scanner</strong> (SPI Interface, 13.56 MHz).</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; background:#f8fafc; padding:6px 10px; border:1px solid #cbd5e1; border-radius:3px;">
                            <span style="font-weight:800; color:var(--cics-navy); width:65px;">STEP 2:</span>
                            <span><strong>ESP32 Microcontroller</strong> captures UID, checks Master Key bypass, then sends <code>UID:&lt;HEX&gt;</code> over USB Serial (115200 baud).</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; background:#eff6ff; padding:6px 10px; border:1px solid #bfdbfe; border-radius:3px;">
                            <span style="font-weight:800; color:#1e40af; width:65px;">STEP 3:</span>
                            <span><strong>Serial Bridge Daemon</strong> receives serial line and issues HTTP POST JSON to <code>/public/api/rfid_scan.php</code>.</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; background:#f8fafc; padding:6px 10px; border:1px solid #cbd5e1; border-radius:3px;">
                            <span style="font-weight:800; color:var(--cics-navy); width:65px;">STEP 4:</span>
                            <span><strong>PHP AccessController</strong> queries MySQL <code>users</code> table, checks status (Active/Inactive), logs tap into <code>access_logs</code>.</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; background:#ecfdf5; padding:6px 10px; border:1px solid #6ee7b7; border-radius:3px;">
                            <span style="font-weight:800; color:#065f46; width:65px;">STEP 5:</span>
                            <span style="color:#064e3b;">Bridge sends <code>GRANT</code> or <code>DENY</code> to ESP32. Relay unlocks solenoid for <strong>6000ms</strong>; DFPlayer plays Track 2 or 1.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wire-arrow-down">↓</div>

            <!-- Lower Wireframe: Uniform Color Palette & UI Design System -->
            <div class="wire-frame-box">
                <div class="wire-frame-inner">
                    <div class="wire-banner">Uniform Color System & Component Design Specifications</div>

                    <!-- Color Swatches Grid -->
                    <div style="display:grid; grid-template-columns:repeat(6, 1fr); gap:6px; text-align:center;">
                        <div style="border:1px solid #94a3b8; padding:6px 4px; border-radius:3px; background:#ffffff;">
                            <div style="height:24px; background:#293681; border-radius:2px; margin-bottom:4px;"></div>
                            <div style="font-size:0.62rem; font-weight:700; color:#1c2440;">CICS Navy</div>
                            <div style="font-size:0.55rem; color:#64748b;">#293681</div>
                        </div>

                        <div style="border:1px solid #94a3b8; padding:6px 4px; border-radius:3px; background:#ffffff;">
                            <div style="height:24px; background:#4274d9; border-radius:2px; margin-bottom:4px;"></div>
                            <div style="font-size:0.62rem; font-weight:700; color:#1c2440;">Accent Blue</div>
                            <div style="font-size:0.55rem; color:#64748b;">#4274d9</div>
                        </div>

                        <div style="border:1px solid #94a3b8; padding:6px 4px; border-radius:3px; background:#ffffff;">
                            <div style="height:24px; background:#95ccdd; border-radius:2px; margin-bottom:4px;"></div>
                            <div style="font-size:0.62rem; font-weight:700; color:#1c2440;">Sky Light</div>
                            <div style="font-size:0.55rem; color:#64748b;">#95ccdd</div>
                        </div>

                        <div style="border:1px solid #94a3b8; padding:6px 4px; border-radius:3px; background:#ffffff;">
                            <div style="height:24px; background:#10b981; border-radius:2px; margin-bottom:4px;"></div>
                            <div style="font-size:0.62rem; font-weight:700; color:#1c2440;">Granted</div>
                            <div style="font-size:0.55rem; color:#64748b;">#10b981</div>
                        </div>

                        <div style="border:1px solid #94a3b8; padding:6px 4px; border-radius:3px; background:#ffffff;">
                            <div style="height:24px; background:#ef4444; border-radius:2px; margin-bottom:4px;"></div>
                            <div style="font-size:0.62rem; font-weight:700; color:#1c2440;">Denied</div>
                            <div style="font-size:0.55rem; color:#64748b;">#ef4444</div>
                        </div>

                        <div style="border:1px solid #94a3b8; padding:6px 4px; border-radius:3px; background:#ffffff;">
                            <div style="height:24px; background:#1c2440; border-radius:2px; margin-bottom:4px;"></div>
                            <div style="font-size:0.62rem; font-weight:700; color:#1c2440;">Slate Ink</div>
                            <div style="font-size:0.55rem; color:#64748b;">#1c2440</div>
                        </div>
                    </div>

                    <!-- Guidelines and typography note -->
                    <div style="font-size:0.68rem; color:#334155; line-height:1.45; background:#f8fafc; padding:8px; border:1px solid var(--cics-border-light);">
                        <strong>Uniformity Directives:</strong>
                        All screens follow the dual-line wireframe container convention. High-priority interactive buttons strictly use CICS Navy (<code>#293681</code>) or Accent Blue (<code>#4274d9</code>). Positive validations and door unlocks employ Emerald Green (<code>#10b981</code>). Lockout alerts and security denials employ Coral Red (<code>#ef4444</code>). Monospaced code styling applies uniformly to all RFID UIDs, ports, and hardware parameters.
                    </div>

                    <div class="wire-action-row">
                        <span style="font-size:0.65rem; color:var(--cics-muted);">Cagayan State University — College of Information and Computing Sciences</span>
                        <div class="wire-btn wire-btn-success" onclick="window.print()">
                            Print Complete 12-Page Prototype Document (PDF)
                        </div>
                    </div>
                </div>
            </div>

            <div class="proto-footer">
                <span class="proto-footer-brand">SDASFC Prototype — Smart Door Automation System for CICS</span>
                <span>Page 12 of 12</span>
            </div>
        </section>

    </main>

    <script>
        function toggleTheme() {
            const body = document.body;
            const btnText = document.getElementById('themeBtnText');
            if (body.classList.contains('color-theme')) {
                body.classList.remove('color-theme');
                body.classList.add('monochrome-theme');
                btnText.textContent = '🎨 Theme: Blueprint Monochrome';
            } else {
                body.classList.remove('monochrome-theme');
                body.classList.add('color-theme');
                btnText.textContent = '🎨 Theme: Uniform CICS Colors';
            }
        }

        function jumpToPage(pageId) {
            const el = document.getElementById(pageId);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>
</body>
</html>
