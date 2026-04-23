<?php
$adminTitle = 'Gestion des Catégories';
require_once 'includes/header.php';

$success = $error = '';

// AJAX reorder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder') {
    header('Content-Type: application/json');
    $ids = json_decode($_POST['order'] ?? '[]', true);
    if (is_array($ids)) {
        $stmt = $pdo->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
        foreach ($ids as $i => $id) {
            $stmt->execute([$i, intval($id)]);
        }
    }
    echo json_encode(['ok' => true]);
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $count->execute([$id]);
    if ($count->fetchColumn() > 0) {
        $error = 'Impossible de supprimer : des produits sont liés à cette catégorie.';
    } else {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        $success = 'Catégorie supprimée.';
    }
}

// Toggle active
if (isset($_GET['toggle']) && isset($_GET['val'])) {
    $pdo->prepare("UPDATE categories SET is_active = ? WHERE id = ?")->execute([intval($_GET['val']), intval($_GET['toggle'])]);
    header('Location: categories.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'reorder') {
    $id = intval($_POST['id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $slug = slugify($name);
    $desc = sanitize($_POST['description'] ?? '');
    $icon = sanitize($_POST['icon'] ?? 'fas fa-box');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $sortOrder = intval($_POST['sort_order'] ?? 0);

    if (empty($name)) {
        $error = 'Le nom est obligatoire.';
    } else {
        if ($id > 0) {
            $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, icon=?, is_active=?, sort_order=? WHERE id=?")
                ->execute([$name, $slug, $desc, $icon, $isActive, $sortOrder, $id]);
            $success = 'Catégorie modifiée avec succès.';
        } else {
            $pdo->prepare("INSERT INTO categories (name, slug, description, icon, is_active, sort_order) VALUES (?,?,?,?,?,?)")
                ->execute([$name, $slug, $desc, $icon, $isActive, $sortOrder]);
            $success = 'Catégorie ajoutée avec succès.';
        }
    }
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.sort_order ASC")->fetchAll();
$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editCat = $stmt->fetch();
}

$popularIcons = ['fas fa-spray-can','fas fa-water','fas fa-hands-wash','fas fa-broom','fas fa-box-open','fas fa-trash-alt','fas fa-toilet-paper','fas fa-bug','fas fa-pump-soap','fas fa-flask','fas fa-shield-alt','fas fa-leaf','fas fa-industry','fas fa-truck','fas fa-warehouse','fas fa-tools'];
?>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

<!-- Stats Cards -->
<div class="stats-row" style="display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-bottom:25px;">
    <div class="card" style="text-align:center; padding:20px;">
        <div style="font-size:2rem; font-weight:800; color:var(--secondary);"><?= count($categories) ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Catégories</div>
    </div>
    <div class="card" style="text-align:center; padding:20px;">
        <div style="font-size:2rem; font-weight:800; color:var(--primary);"><?= array_sum(array_column($categories, 'product_count')) ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Produits Total</div>
    </div>
    <div class="card" style="text-align:center; padding:20px;">
        <div style="font-size:2rem; font-weight:800; color:#25D366;"><?= count(array_filter($categories, fn($c) => $c['is_active'])) ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Actives</div>
    </div>
</div>

<!-- Form -->
<div class="card" style="margin-bottom:25px;">
    <h3><i class="fas fa-<?= $editCat ? 'edit' : 'plus' ?>"></i> <?= $editCat ? 'Modifier' : 'Ajouter' ?> une Catégorie</h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editCat['id'] ?? 0 ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Nom *</label>
                <input type="text" name="name" value="<?= sanitize($editCat['name'] ?? '') ?>" required placeholder="Ex: Nettoyage et Désinfection">
            </div>
            <div class="form-group">
                <label>Icône (Font Awesome) <span id="iconPreview" style="margin-left:8px;"><i class="<?= sanitize($editCat['icon'] ?? 'fas fa-box') ?>" style="color:var(--secondary);"></i></span></label>
                <input type="text" name="icon" id="iconInput" value="<?= sanitize($editCat['icon'] ?? 'fas fa-box') ?>" placeholder="fas fa-box" oninput="document.getElementById('iconPreview').innerHTML='<i class=\''+this.value+'\' style=\'color:var(--secondary);\'></i>'">
                <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:8px;">
                    <?php foreach($popularIcons as $ico): ?>
                    <button type="button" class="icon-pick-btn" onclick="document.getElementById('iconInput').value='<?= $ico ?>';document.getElementById('iconPreview').innerHTML='<i class=\'<?= $ico ?>\' style=\'color:var(--secondary);\'></i>'" title="<?= $ico ?>">
                        <i class="<?= $ico ?>"></i>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Décrivez cette catégorie..."><?= sanitize($editCat['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Ordre d'affichage</label><input type="number" name="sort_order" value="<?= $editCat['sort_order'] ?? 0 ?>" min="0"></div>
            <label class="checkbox-label"><input type="checkbox" name="is_active" <?= ($editCat['is_active'] ?? 1) ? 'checked' : '' ?> checked> Active</label>
        </div>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editCat ? 'Modifier' : 'Ajouter' ?></button>
        <?php if($editCat): ?><a href="categories.php" class="btn-admin btn-admin-secondary">Annuler</a><?php endif; ?>
    </form>
</div>

<!-- Categories List -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
        <h3 style="margin:0;"><i class="fas fa-list"></i> Catégories (<?= count($categories) ?>)</h3>
        <span style="font-size:0.82rem; color:var(--gray-500);"><i class="fas fa-grip-vertical"></i> Glissez pour réorganiser</span>
    </div>
    <div id="categoriesSortable">
        <?php foreach($categories as $c): ?>
        <div class="sortable-item" data-id="<?= $c['id'] ?>">
            <div class="sortable-handle"><i class="fas fa-grip-vertical"></i></div>
            <div class="sortable-icon"><i class="<?= sanitize($c['icon']) ?>"></i></div>
            <div class="sortable-info">
                <strong><?= sanitize($c['name']) ?></strong>
                <span><?= sanitize(mb_strimwidth($c['description'], 0, 60, '...')) ?></span>
            </div>
            <div class="sortable-meta">
                <span class="badge badge-primary"><?= $c['product_count'] ?> produits</span>
                <a href="?toggle=<?= $c['id'] ?>&val=<?= $c['is_active'] ? 0 : 1 ?>" class="toggle-switch <?= $c['is_active'] ? 'active' : '' ?>" title="<?= $c['is_active'] ? 'Désactiver' : 'Activer' ?>">
                    <span class="toggle-slider"></span>
                </a>
            </div>
            <div class="sortable-actions">
                <a href="?edit=<?= $c['id'] ?>" class="btn-icon btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
                <a href="?delete=<?= $c['id'] ?>" class="btn-icon btn-delete" title="Supprimer" onclick="return confirm('Supprimer cette catégorie ?')"><i class="fas fa-trash"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Drag & Drop Reorder
(function() {
    const container = document.getElementById('categoriesSortable');
    let dragged = null;
    container.querySelectorAll('.sortable-item').forEach(item => {
        const handle = item.querySelector('.sortable-handle');
        handle.addEventListener('mousedown', () => { dragged = item; item.classList.add('dragging'); });
        item.addEventListener('dragover', e => {
            e.preventDefault();
            if (dragged && dragged !== item) {
                const rect = item.getBoundingClientRect();
                const mid = rect.top + rect.height / 2;
                if (e.clientY < mid) container.insertBefore(dragged, item);
                else container.insertBefore(dragged, item.nextSibling);
            }
        });
    });
    container.querySelectorAll('.sortable-item').forEach(item => {
        item.setAttribute('draggable', 'true');
        item.addEventListener('dragstart', e => { dragged = item; item.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; });
        item.addEventListener('dragend', () => {
            item.classList.remove('dragging');
            const ids = [...container.querySelectorAll('.sortable-item')].map(el => el.dataset.id);
            const form = new FormData();
            form.append('action', 'reorder');
            form.append('order', JSON.stringify(ids));
            fetch('categories.php', { method: 'POST', body: form });
        });
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>