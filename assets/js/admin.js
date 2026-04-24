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

    // ===== RICH TEXT EDITOR =====
    const editor = document.getElementById('reportEditor');
    const hiddenInput = document.getElementById('reportContentHidden');
    if (editor && hiddenInput) {
        // Update word/char count
        function updateCounts() {
            const text = editor.innerText || '';
            const words = text.trim() ? text.trim().split(/\s+/).length : 0;
            const chars = text.length;
            const wc = document.getElementById('editorWordCount');
            const cc = document.getElementById('editorCharCount');
            if (wc) wc.textContent = words + ' mot' + (words !== 1 ? 's' : '');
            if (cc) cc.textContent = chars + ' caractère' + (chars !== 1 ? 's' : '');
        }
        editor.addEventListener('input', updateCounts);
        updateCounts();

        // Sync editor to hidden textarea before submit
        editor.closest('form').addEventListener('submit', function() {
            hiddenInput.value = editor.innerHTML;
        });

        // Toolbar buttons
        document.querySelectorAll('.toolbar-btn[data-command]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const cmd = this.dataset.command;
                editor.focus();

                if (cmd === 'createLink') {
                    const url = prompt('Entrez l\'URL du lien :', 'https://');
                    if (url) document.execCommand('createLink', false, url);
                } else if (cmd === 'insertTable') {
                    const rows = prompt('Nombre de lignes :', '3');
                    const cols = prompt('Nombre de colonnes :', '3');
                    if (rows && cols) {
                        let table = '<table><thead><tr>';
                        for (let c = 0; c < parseInt(cols); c++) table += '<th>En-tête</th>';
                        table += '</tr></thead><tbody>';
                        for (let r = 0; r < parseInt(rows) - 1; r++) {
                            table += '<tr>';
                            for (let c = 0; c < parseInt(cols); c++) table += '<td>&nbsp;</td>';
                            table += '</tr>';
                        }
                        table += '</tbody></table><p><br></p>';
                        document.execCommand('insertHTML', false, table);
                    }
                } else {
                    document.execCommand(cmd, false, null);
                }
                updateCounts();
            });
        });

        // Toolbar selects (formatBlock, fontSize)
        document.querySelectorAll('.toolbar-select[data-command]').forEach(sel => {
            sel.addEventListener('change', function() {
                editor.focus();
                const cmd = this.dataset.command;
                const val = this.value;
                if (cmd === 'formatBlock') {
                    document.execCommand('formatBlock', false, '<' + val + '>');
                } else {
                    document.execCommand(cmd, false, val);
                }
                updateCounts();
            });
        });

        // Color pickers
        document.querySelectorAll('.toolbar-color-input').forEach(input => {
            input.addEventListener('input', function() {
                editor.focus();
                document.execCommand(this.dataset.command, false, this.value);
            });
            // Prevent toolbar-btn click when clicking color input
            input.addEventListener('click', function(e) { e.stopPropagation(); });
        });

        // Track active formatting state on selection change
        document.addEventListener('selectionchange', function() {
            if (!editor.contains(document.activeElement) && document.activeElement !== editor) return;
            document.querySelectorAll('.toolbar-btn[data-command]').forEach(btn => {
                const cmd = btn.dataset.command;
                if (['bold','italic','underline','strikeThrough','insertUnorderedList','insertOrderedList','justifyLeft','justifyCenter','justifyRight','justifyFull'].includes(cmd)) {
                    try {
                        btn.classList.toggle('active', document.queryCommandState(cmd));
                    } catch(e) {}
                }
            });
        });
    }
});