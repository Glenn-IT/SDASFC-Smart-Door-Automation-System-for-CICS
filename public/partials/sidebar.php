<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);

function navLink(string $file, string $label, string $currentPage): string
{
    $active = $currentPage === $file ? ' active' : '';
    return '<a href="' . BASE_URL . '/' . $file . '" class="nav-link text-white' . $active . '">' . $label . '</a>';
}
?>
<div class="bg-dark text-white vh-100 p-3" style="width: 220px;">
    <h5 class="mb-4">SDASFC</h5>
    <nav class="nav nav-pills flex-column gap-1">
        <?= navLink('dashboard.php', 'Dashboard', $currentPage) ?>
        <?= navLink('users/index.php', 'Manage Users', $currentPage) ?>
        <?= navLink('schedules/index.php', 'Schedules', $currentPage) ?>
        <?= navLink('reports/index.php', 'Reports', $currentPage) ?>
    </nav>
</div>
