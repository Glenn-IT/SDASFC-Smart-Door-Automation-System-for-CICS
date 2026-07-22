// Toggles visibility for any password input marked up as:
// <input type="password" id="X"> <button type="button" data-toggle-password="X">
document.addEventListener('click', function (event) {
    const btn = event.target.closest('[data-toggle-password]');
    if (!btn) return;

    const input = document.getElementById(btn.getAttribute('data-toggle-password'));
    if (!input) return;

    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    btn.textContent = showing ? 'Show' : 'Hide';
});

// Disables a button showing a countdown, marked up as:
// <button data-lockout-seconds="30">Log In</button>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.querySelector('[data-lockout-seconds]');
    if (!btn) return;

    let seconds = parseInt(btn.getAttribute('data-lockout-seconds'), 10);
    if (!seconds || seconds <= 0) return;

    const form = btn.closest('form');
    const otherControls = form
        ? Array.from(form.querySelectorAll('input, button')).filter(function (el) { return el !== btn; })
        : [];

    const originalText = btn.textContent;
    btn.disabled = true;
    otherControls.forEach(function (el) { el.disabled = true; });

    const timer = setInterval(function () {
        btn.textContent = 'Try again in ' + seconds + 's';
        seconds--;

        if (seconds < 0) {
            clearInterval(timer);
            btn.disabled = false;
            btn.textContent = originalText;
            otherControls.forEach(function (el) { el.disabled = false; });
        }
    }, 1000);
});

// Restores the sidebar's collapsed/expanded state on page load, marked up as:
// <div class="app-sidebar">
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.app-sidebar');
    if (!sidebar) return;

    if (localStorage.getItem('sidebarCollapsed') === '1') {
        sidebar.classList.add('collapsed');
    }
});

// Toggles the sidebar collapsed/expanded, marked up as:
// <button data-sidebar-toggle> next to <div class="app-sidebar">
document.addEventListener('click', function (event) {
    const btn = event.target.closest('[data-sidebar-toggle]');
    if (!btn) return;

    const sidebar = document.querySelector('.app-sidebar');
    if (!sidebar) return;

    sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
});
