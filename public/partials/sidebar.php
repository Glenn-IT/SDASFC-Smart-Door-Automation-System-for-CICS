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
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#fff" viewBox="0 0 16 16">
                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
            </svg>
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
