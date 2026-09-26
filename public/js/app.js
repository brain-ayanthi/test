// Clinic Management System - global JS

// Live clock
function updateClock() {
    const el = document.getElementById('liveClock');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleTimeString('en-US', { hour12: true });
}
setInterval(updateClock, 1000);
updateClock();

// Sidebar toggle for mobile
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
        });
    }

    // Auto-dismiss flash messages
    setTimeout(() => {
        document.querySelectorAll('.flash-msg').forEach(el => el.remove());
    }, 5000);
});

// Number formatting helper
function formatCurrency(n) {
    return 'Rs ' + parseFloat(n).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// CSRF token helper for fetch
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content;
}
