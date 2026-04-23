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
        logAdminActivity($pdo, $_SESSION['admin_id'], 'delete_category', "Suppression catégorie #$id");
        $success = 'Catégorie supprimée.';
    }
}

// Toggle active
if (isset($_GET['toggle']) && isset($_GET['val'])) {
    $pdo->prepare("UPDATE categories SET is_active = ? WHERE id = ?")->execute([intval($_GET['val']), intval($_GET['toggle'])]);
    logAdminActivity($pdo, $_SESSION['admin_id'], 'toggle_category', "Activation/désactivation catégorie #" . $_GET['toggle']);
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

    // Handle image upload
    $imageName = '';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5 * 1024 * 1024) {
            $imageName = 'cat-' . $slug . '-' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/categories/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        } else {
            $error = 'Image invalide. Formats: JPG, PNG, GIF, WEBP. Max 5 Mo.';
        }
    }

    // Remove image if requested
    if (isset($_POST['remove_image']) && $id > 0) {
        $old = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
        $old->execute([$id]);
        $oldImg = $old->fetchColumn();
        if ($oldImg && file_exists(__DIR__ . '/../uploads/categories/' . $oldImg)) {
            unlink(__DIR__ . '/../uploads/categories/' . $oldImg);
        }
        $pdo->prepare("UPDATE categories SET image = NULL WHERE id = ?")->execute([$id]);
    }

    if (empty($name)) {
        $error = 'Le nom est obligatoire.';
    } elseif (!$error) {
        if ($id > 0) {
            $sql = "UPDATE categories SET name=?, slug=?, description=?, icon=?, is_active=?, sort_order=?";
            $params = [$name, $slug, $desc, $icon, $isActive, $sortOrder];
            if ($imageName) {
                // Delete old image
                $old = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
                $old->execute([$id]);
                $oldImg = $old->fetchColumn();
                if ($oldImg && file_exists(__DIR__ . '/../uploads/categories/' . $oldImg)) {
                    unlink(__DIR__ . '/../uploads/categories/' . $oldImg);
                }
                $sql .= ", image=?";
                $params[] = $imageName;
            }
            $sql .= " WHERE id=?";
            $params[] = $id;
            $pdo->prepare($sql)->execute($params);
            logAdminActivity($pdo, $_SESSION['admin_id'], 'edit_category', "Modification: $name");
            $success = 'Catégorie modifiée avec succès.';
        } else {
            $pdo->prepare("INSERT INTO categories (name, slug, description, icon, image, is_active, sort_order) VALUES (?,?,?,?,?,?,?)")
                ->execute([$name, $slug, $desc, $icon, $imageName ?: null, $isActive, $sortOrder]);
            logAdminActivity($pdo, $_SESSION['admin_id'], 'add_category', "Ajout: $name");
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
    <form method="POST" enctype="multipart/form-data">
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
        <div class="form-group">
            <label><i class="fas fa-image"></i> Image de la Catégorie</label>
            <div class="image-preview-zone" id="catImgZone" onclick="document.getElementById('catImgInput').click()">
                <?php if(!empty($editCat['image'])): ?>
                <img src="<?= SITE_URL ?>/uploads/categories/<?= sanitize($editCat['image']) ?>" alt="Image catégorie" id="catImgPreview">
                <?php else: ?>
                <div class="preview-text" id="catImgText"><i class="fas fa-cloud-upload-alt"></i> Cliquez ou glissez une image ici<br><small>JPG, PNG, GIF, WEBP - Max 5 Mo</small></div>
                <img src="" alt="" id="catImgPreview" style="display:none;">
                <?php endif; ?>
            </div>
            <input type="file" name="image" id="catImgInput" accept="image/*" style="display:none;" onchange="previewCatImg(this)">
            <?php if(!empty($editCat['image'])): ?>
            <label style="margin-top:8px; font-size:0.85rem; cursor:pointer; color:var(--danger);"><input type="checkbox" name="remove_image" value="1"> <i class="fas fa-trash-alt"></i> Supprimer l'image actuelle</label>
            <?php endif; ?>
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
            <div class="sortable-icon">
                <?php if(!empty($c['image'])): ?>
                <img src="<?= SITE_URL ?>/uploads/categories/<?= sanitize($c['image']) ?>" alt="" style="width:40px;height:40px;border-radius:10px;object-fit:cover;">
                <?php else: ?>
                <i class="<?= sanitize($c['icon']) ?>"></i>
                <?php endif; ?>
            </div>
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
// Image preview
function previewCatImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('catImgPreview');
            const text = document.getElementById('catImgText');
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (text) text.style.display = 'none';
            document.getElementById('catImgZone').classList.add('has-image');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
// Drag & Drop on image zone
const zone = document.getElementById('catImgZone');
if (zone) {
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = 'var(--secondary)'; });
    zone.addEventListener('dragleave', () => { zone.style.borderColor = ''; });
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.style.borderColor = '';
        const input = document.getElementById('catImgInput');
        input.files = e.dataTransfer.files;
        previewCatImg(input);
    });
}

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