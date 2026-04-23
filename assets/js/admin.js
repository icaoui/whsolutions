document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    // Create overlay for mobile
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // Auto-close sidebar on nav click (mobile)
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', () => {
            if (window.innerWidth <= 992) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            }
        });
    });

    // Confirm delete actions
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!this.closest('form') && !this.getAttribute('onclick')) {
                if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                    e.preventDefault();
                }
            }
        });
    });
});