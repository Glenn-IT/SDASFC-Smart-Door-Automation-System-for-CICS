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

    const originalText = btn.textContent;
    btn.disabled = true;

    const timer = setInterval(function () {
        btn.textContent = 'Try again in ' + seconds + 's';
        seconds--;

        if (seconds < 0) {
            clearInterval(timer);
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }, 1000);
});
