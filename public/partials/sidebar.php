<?php
$currentPage = $_SERVER['SCRIPT_NAME'];

function navLink(string $file, string $label, string $currentPage): string
{
    $active = str_ends_with($currentPage, '/' . $file) ? ' active' : '';
    return '<a href="' . BASE_URL . '/' . $file . '" class="nav-link text-white' . $active . '">' . $label . '</a>';
}
?>
<div class="app-sidebar text-white p-3">
    <div class="app-sidebar-brand mb-4">
        <span class="app-sidebar-brand-icon">
            <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="SDASFC Logo">
        </span>
        <span>SDASFC</span>
    </div>
    <nav class="nav nav-pills flex-column gap-1">
        <?= navLink('dashboard.php', 'Dashboard', $currentPage) ?>
        <?= navLink('users/index.php', 'Manage Users', $currentPage) ?>
        <?= navLink('schedules/index.php', 'Schedules', $currentPage) ?>
        <?= navLink('reports/index.php', 'Reports', $currentPage) ?>
    </nav>
</div>
