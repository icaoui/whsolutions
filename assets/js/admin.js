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

    // ===== TOAST NOTIFICATION SYSTEM =====
    window.showToast = function(type, title, message) {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        const icons = { success: 'fa-check', error: 'fa-times', warning: 'fa-exclamation' };
        toast.innerHTML = `
            <div class="toast-icon"><i class="fas ${icons[type] || 'fa-info'}"></i></div>
            <div class="toast-body"><strong>${title}</strong><small>${message || ''}</small></div>
            <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            <div class="toast-progress"></div>
        `;
        toast.style.position = 'relative';
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 4000);
    };

    // Auto-convert .alert elements to toasts
    document.querySelectorAll('.alert-success, .alert-danger, .alert-error').forEach(el => {
        const isSuccess = el.classList.contains('alert-success');
        const text = el.textContent.trim();
        if (text) {
            showToast(isSuccess ? 'success' : 'error', isSuccess ? 'Succès' : 'Erreur', text);
            el.style.display = 'none';
        }
    });

    // ===== SPOTLIGHT SEARCH (Ctrl+K) =====
    const pages = [
        { name: 'Dashboard', url: 'index.php', icon: 'fa-chart-line', key: 'dashboard accueil' },
        { name: 'Produits', url: 'products.php', icon: 'fa-box-open', key: 'produits articles' },
        { name: 'Catégories', url: 'categories.php', icon: 'fa-th-large', key: 'catégories' },
        { name: 'Messages', url: 'messages.php', icon: 'fa-envelope', key: 'messages contact' },
        { name: 'Demandes', url: 'inquiries.php', icon: 'fa-question-circle', key: 'demandes devis' },
        { name: 'Packages', url: 'packages.php', icon: 'fa-gem', key: 'packages propositions valeur' },
        { name: 'Clients Packages', url: 'customer_packages.php', icon: 'fa-user-tag', key: 'clients abonnements' },
        { name: 'Visiteurs', url: 'visitors.php', icon: 'fa-users', key: 'visiteurs analytics' },
        { name: 'Rapports', url: 'reports.php', icon: 'fa-file-alt', key: 'rapports export' },
        { name: 'Paramètres', url: 'settings.php', icon: 'fa-cog', key: 'paramètres configuration' },
        { name: 'Utilisateurs', url: 'users.php', icon: 'fa-user-shield', key: 'utilisateurs admin' },
        { name: 'Journal d\'activité', url: 'activity.php', icon: 'fa-history', key: 'journal activité log' },
        { name: 'Voir le site', url: '../', icon: 'fa-globe', key: 'site frontend' },
    ];

    // Build spotlight
    const spotOverlay = document.createElement('div');
    spotOverlay.className = 'spotlight-overlay';
    spotOverlay.innerHTML = `
        <div class="spotlight-box">
            <div style="position:relative;">
                <i class="fas fa-search spotlight-icon"></i>
                <input class="spotlight-input" placeholder="Rechercher une page..." autocomplete="off">
            </div>
            <div class="spotlight-results"></div>
            <div class="spotlight-hint">↑↓ Naviguer &nbsp;·&nbsp; ↵ Ouvrir &nbsp;·&nbsp; Esc Fermer</div>
        </div>
    `;
    document.body.appendChild(spotOverlay);

    const spotInput = spotOverlay.querySelector('.spotlight-input');
    const spotResults = spotOverlay.querySelector('.spotlight-results');
    let spotActive = -1;

    function openSpotlight() {
        spotOverlay.classList.add('active');
        spotInput.value = '';
        spotActive = -1;
        renderSpotlight('');
        setTimeout(() => spotInput.focus(), 100);
    }
    function closeSpotlight() {
        spotOverlay.classList.remove('active');
    }

    function renderSpotlight(query) {
        const q = query.toLowerCase();
        const filtered = q ? pages.filter(p => p.name.toLowerCase().includes(q) || p.key.includes(q)) : pages;
        spotResults.innerHTML = filtered.map((p, i) =>
            `<div class="spotlight-item${i === spotActive ? ' active' : ''}" data-url="${p.url}">
                <i class="fas ${p.icon}"></i>
                <span>${p.name}</span>
            </div>`
        ).join('') || '<div style="padding:20px;text-align:center;color:#aaa;">Aucun résultat</div>';

        spotResults.querySelectorAll('.spotlight-item').forEach(item => {
            item.addEventListener('click', () => { window.location.href = item.dataset.url; });
        });
    }

    spotInput.addEventListener('input', () => { spotActive = -1; renderSpotlight(spotInput.value); });
    spotInput.addEventListener('keydown', (e) => {
        const items = spotResults.querySelectorAll('.spotlight-item');
        if (e.key === 'ArrowDown') { e.preventDefault(); spotActive = Math.min(spotActive + 1, items.length - 1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); spotActive = Math.max(spotActive - 1, 0); }
        else if (e.key === 'Enter' && items[spotActive]) { window.location.href = items[spotActive].dataset.url; }
        else if (e.key === 'Escape') { closeSpotlight(); }
        items.forEach((it, i) => it.classList.toggle('active', i === spotActive));
        if (items[spotActive]) items[spotActive].scrollIntoView({ block: 'nearest' });
    });

    spotOverlay.addEventListener('click', (e) => { if (e.target === spotOverlay) closeSpotlight(); });

    // Keyboard shortcut: Ctrl+K
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            spotOverlay.classList.contains('active') ? closeSpotlight() : openSpotlight();
        }
        if (e.key === 'Escape') closeSpotlight();
    });

    // Bind search button in header
    const searchBtn = document.getElementById('spotlightBtn');
    if (searchBtn) searchBtn.addEventListener('click', openSpotlight);

    // ===== SIDEBAR COLLAPSE (desktop) =====
    const collapseBtn = document.getElementById('sidebarCollapse');
    if (collapseBtn) {
        const isCollapsed = localStorage.getItem('sidebar_collapsed') === '1';
        if (isCollapsed) document.body.classList.add('sidebar-collapse');
        collapseBtn.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapse');
            localStorage.setItem('sidebar_collapsed', document.body.classList.contains('sidebar-collapse') ? '1' : '0');
        });
    }

    // ===== TABLE ROW HOVER HIGHLIGHT =====
    document.querySelectorAll('.admin-table tbody tr').forEach(row => {
        row.style.cursor = 'default';
        row.style.transition = 'background 0.15s';
    });

    // ===== AUTO-HIDE ALERTS =====
    document.querySelectorAll('.alert').forEach(alert => {
        if (alert.style.display !== 'none') {
            setTimeout(() => { alert.style.transition = 'opacity 0.5s'; alert.style.opacity = '0'; setTimeout(() => alert.remove(), 500); }, 5000);
        }
    });
});