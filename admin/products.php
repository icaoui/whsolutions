<?php
$adminTitle = 'Gestion des Produits';
require_once 'includes/header.php';

$success = $error = '';

// AJAX reorder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder') {
    header('Content-Type: application/json');
    $ids = json_decode($_POST['order'] ?? '[]', true);
    if (is_array($ids)) {
        $stmt = $pdo->prepare("UPDATE products SET sort_order = ? WHERE id = ?");
        foreach ($ids as $i => $id) { $stmt->execute([$i, intval($id)]); }
    }
    echo json_encode(['ok' => true]); exit;
}

// AJAX toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    header('Content-Type: application/json');
    $field = $_POST['field'] === 'is_featured' ? 'is_featured' : 'is_active';
    $pdo->prepare("UPDATE products SET $field = ? WHERE id = ?")->execute([intval($_POST['val']), intval($_POST['id'])]);
    logAdminActivity($pdo, $_SESSION['admin_id'], 'toggle_product', "$field=" . $_POST['val'] . " pour produit #" . $_POST['id']);
    echo json_encode(['ok' => true]); exit;
}

// Duplicate
if (isset($_GET['duplicate'])) {
    $orig = getProductById($pdo, intval($_GET['duplicate']));
    if ($orig) {
        $newSlug = $orig['slug'] . '-copie-' . time();
        $pdo->prepare("INSERT INTO products (name, slug, category_id, short_description, description, reference, image, is_featured, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,0,?)")
            ->execute(['[Copie] ' . $orig['name'], $newSlug, $orig['category_id'], $orig['short_description'], $orig['description'], $orig['reference'], $orig['image'], $orig['is_featured'], $orig['sort_order'] + 1]);
        $success = 'Produit dupliqué. Modifiez la copie ci-dessous.';
        logAdminActivity($pdo, $_SESSION['admin_id'], 'duplicate_product', "Duplication de: " . $orig['name']);
        header('Location: products.php?edit=' . $pdo->lastInsertId()); exit;
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $prod = getProductById($pdo, $id);
    if ($prod) {
        if (!empty($prod['image']) && file_exists(PRODUCTS_UPLOAD_PATH . $prod['image'])) {
            unlink(PRODUCTS_UPLOAD_PATH . $prod['image']);
        }
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        logAdminActivity($pdo, $_SESSION['admin_id'], 'delete_product', "Suppression: " . $prod['name']);
        $success = 'Produit supprimé avec succès.';
    }
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $id = intval($_POST['id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $slug = slugify($name);
    $categoryId = intval($_POST['category_id'] ?? 0);
    $shortDesc = sanitize($_POST['short_description'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    $ref = sanitize($_POST['reference'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $sortOrder = intval($_POST['sort_order'] ?? 0);

    $imageName = '';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5 * 1024 * 1024) {
            $imageName = $slug . '-' . time() . '.' . $ext;
            if (!is_dir(PRODUCTS_UPLOAD_PATH)) mkdir(PRODUCTS_UPLOAD_PATH, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], PRODUCTS_UPLOAD_PATH . $imageName);
        } else {
            $error = 'Image invalide. Formats: JPG, PNG, GIF, WEBP. Max 5 Mo.';
        }
    }

    if (empty($name)) {
        $error = 'Le nom du produit est obligatoire.';
    } elseif (!$error) {
        if ($id > 0) {
            $sql = "UPDATE products SET name=?, slug=?, category_id=?, short_description=?, description=?, reference=?, is_featured=?, is_active=?, sort_order=?";
            $params = [$name, $slug, $categoryId, $shortDesc, $desc, $ref, $isFeatured, $isActive, $sortOrder];
            if ($imageName) {
                $old = getProductById($pdo, $id);
                if ($old && !empty($old['image']) && file_exists(PRODUCTS_UPLOAD_PATH . $old['image'])) {
                    unlink(PRODUCTS_UPLOAD_PATH . $old['image']);
                }
                $sql .= ", image=?";
                $params[] = $imageName;
            }
            $sql .= " WHERE id=?";
            $params[] = $id;
            $pdo->prepare($sql)->execute($params);
            logAdminActivity($pdo, $_SESSION['admin_id'], 'edit_product', "Modification: $name");
            $success = 'Produit modifié avec succès.';
        } else {
            $pdo->prepare("INSERT INTO products (name, slug, category_id, short_description, description, reference, image, is_featured, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$name, $slug, $categoryId, $shortDesc, $desc, $ref, $imageName, $isFeatured, $isActive, $sortOrder]);
            logAdminActivity($pdo, $_SESSION['admin_id'], 'add_product', "Ajout: $name");
            $success = 'Produit ajouté avec succès.';
        }
    }
}

$categories = getCategories($pdo, false);
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.sort_order ASC, p.id DESC")->fetchAll();
$editProduct = null;
if (isset($_GET['edit'])) {
    $editProduct = getProductById($pdo, intval($_GET['edit']));
}

$totalViews = array_sum(array_column($products, 'views'));
$featuredCount = count(array_filter($products, fn($p) => $p['is_featured']));
$activeCount = count(array_filter($products, fn($p) => $p['is_active']));
?>
            $success = 'Produit modifié avec succès.';
        } else {
            $pdo->prepare("INSERT INTO products (name, slug, category_id, short_description, description, reference, image, is_featured, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$name, $slug, $categoryId, $shortDesc, $desc, $ref, $imageName, $isFeatured, $isActive, $sortOrder]);
            $success = 'Produit ajouté avec succès.';
        }
    }
}

$categories = getCategories($pdo, false);
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.sort_order ASC, p.id DESC")->fetchAll();

// Edit mode
$editProduct = null;
if (isset($_GET['edit'])) {
    $editProduct = getProductById($pdo, intval($_GET['edit']));
}
?>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

<!-- Stats -->
<div class="stats-row" style="display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin-bottom:25px;">
    <div class="card" style="text-align:center; padding:18px;">
        <div style="font-size:1.8rem; font-weight:800; color:var(--primary);"><?= count($products) ?></div>
        <div style="font-size:0.8rem; color:var(--gray-500);">Produits</div>
    </div>
    <div class="card" style="text-align:center; padding:18px;">
        <div style="font-size:1.8rem; font-weight:800; color:var(--secondary);"><?= $activeCount ?></div>
        <div style="font-size:0.8rem; color:var(--gray-500);">Actifs</div>
    </div>
    <div class="card" style="text-align:center; padding:18px;">
        <div style="font-size:1.8rem; font-weight:800; color:#FFE66D;"><?= $featuredCount ?></div>
        <div style="font-size:0.8rem; color:var(--gray-500);">Phares</div>
    </div>
    <div class="card" style="text-align:center; padding:18px;">
        <div style="font-size:1.8rem; font-weight:800; color:#25D366;"><?= number_format($totalViews) ?></div>
        <div style="font-size:0.8rem; color:var(--gray-500);">Vues Total</div>
    </div>
</div>

<!-- Form -->
<div class="card" style="margin-bottom:25px;">
    <h3><i class="fas fa-<?= $editProduct ? 'edit' : 'plus' ?>"></i> <?= $editProduct ? 'Modifier le Produit' : 'Ajouter un Produit' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $editProduct['id'] ?? 0 ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Nom du produit *</label>
                <input type="text" name="name" value="<?= sanitize($editProduct['name'] ?? '') ?>" required placeholder="Ex: Détergent alcalin concentré">
            </div>
            <div class="form-group">
                <label>Catégorie</label>
                <select name="category_id">
                    <option value="0">-- Sélectionner --</option>
                    <?php foreach($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($editProduct['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Description courte</label>
            <input type="text" name="short_description" value="<?= sanitize($editProduct['short_description'] ?? '') ?>" placeholder="Résumé en une ligne (affiché dans les cartes)">
        </div>
        <div class="form-group">
            <label>Description complète</label>
            <textarea name="description" rows="5" placeholder="Description détaillée du produit..."><?= sanitize($editProduct['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Référence</label><input type="text" name="reference" value="<?= sanitize($editProduct['reference'] ?? '') ?>" placeholder="Ex: WH-NET-001"></div>
            <div class="form-group"><label>Ordre d'affichage</label><input type="number" name="sort_order" value="<?= $editProduct['sort_order'] ?? 0 ?>" min="0"></div>
        </div>
        <div class="form-group">
            <label>Image produit</label>
            <div class="image-preview-zone <?= !empty($editProduct['image']) ? 'has-image' : '' ?>" id="imagePreview" onclick="document.getElementById('imageInput').click()">
                <span class="preview-text" id="previewText" <?= !empty($editProduct['image']) ? 'style="display:none"' : '' ?>>
                    <i class="fas fa-cloud-upload-alt"></i>
                    Cliquez ou glissez une image ici<br>
                    <small>JPG, PNG, GIF, WEBP — Max 5 Mo</small>
                </span>
                <?php if(!empty($editProduct['image'])): ?>
                <img id="previewImg" src="<?= SITE_URL ?>/uploads/products/<?= sanitize($editProduct['image']) ?>">
                <?php else: ?>
                <img id="previewImg" style="display:none">
                <?php endif; ?>
            </div>
            <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none" onchange="previewImage(this)">
        </div>
        <div class="form-row">
            <label class="checkbox-label"><input type="checkbox" name="is_featured" <?= ($editProduct['is_featured'] ?? 0) ? 'checked' : '' ?>> <i class="fas fa-star" style="color:#FFE66D;"></i> Produit phare</label>
            <label class="checkbox-label"><input type="checkbox" name="is_active" <?= ($editProduct['is_active'] ?? 1) ? 'checked' : '' ?> checked> <i class="fas fa-eye" style="color:var(--secondary);"></i> Actif (visible sur le site)</label>
        </div>
        <div style="display:flex; gap:10px; margin-top:10px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editProduct ? 'Modifier' : 'Ajouter' ?></button>
            <?php if($editProduct): ?><a href="products.php" class="btn-admin btn-admin-secondary">Annuler</a><?php endif; ?>
        </div>
    </form>
</div>

<!-- Products List -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
        <h3 style="margin:0;"><i class="fas fa-list"></i> Produits (<?= count($products) ?>)</h3>
        <div class="admin-search" style="margin:0; width:280px;">
            <i class="fas fa-search"></i>
            <input type="text" id="productSearch" placeholder="Rechercher un produit..." oninput="filterProducts(this.value)">
        </div>
    </div>
    <div style="overflow-x:auto;">
    <table class="admin-table" id="productsTable">
        <thead><tr><th style="width:60px">Image</th><th>Nom</th><th>Catégorie</th><th>Réf</th><th style="width:50px">Phare</th><th style="width:50px">Actif</th><th>Vues</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($products as $p): ?>
        <tr data-name="<?= strtolower(sanitize($p['name'])) ?>" data-cat="<?= strtolower(sanitize($p['category_name'] ?? '')) ?>">
            <td>
                <?php if(!empty($p['image'])): ?>
                <img src="<?= SITE_URL ?>/uploads/products/<?= sanitize($p['image']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                <?php else: ?>
                <div style="width:50px;height:50px;background:var(--gray-100);border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="color:var(--gray-400);"></i></div>
                <?php endif; ?>
            </td>
            <td><strong><?= sanitize($p['name']) ?></strong><br><small style="color:var(--gray-500);"><?= sanitize(mb_strimwidth($p['short_description'] ?? '', 0, 40, '...')) ?></small></td>
            <td><span class="badge badge-primary"><?= sanitize($p['category_name'] ?? 'N/A') ?></span></td>
            <td><code style="font-size:0.78rem;"><?= sanitize($p['reference'] ?? '-') ?></code></td>
            <td style="text-align:center;">
                <a href="#" onclick="toggleField(<?= $p['id'] ?>,'is_featured',<?= $p['is_featured'] ? 0 : 1 ?>);return false" class="toggle-switch <?= $p['is_featured'] ? 'active' : '' ?>" style="width:34px; height:18px;" title="Phare">
                    <span class="toggle-slider" style="width:14px;height:14px;"></span>
                </a>
            </td>
            <td style="text-align:center;">
                <a href="#" onclick="toggleField(<?= $p['id'] ?>,'is_active',<?= $p['is_active'] ? 0 : 1 ?>);return false" class="toggle-switch <?= $p['is_active'] ? 'active' : '' ?>" style="width:34px; height:18px;" title="Actif">
                    <span class="toggle-slider" style="width:14px;height:14px;"></span>
                </a>
            </td>
            <td><i class="fas fa-eye" style="color:var(--gray-400);margin-right:4px;font-size:0.75rem;"></i><?= number_format($p['views']) ?></td>
            <td class="actions">
                <a href="?edit=<?= $p['id'] ?>" class="btn-icon btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
                <a href="?duplicate=<?= $p['id'] ?>" class="btn-icon" style="color:var(--secondary);" title="Dupliquer"><i class="fas fa-copy"></i></a>
                <a href="?delete=<?= $p['id'] ?>" class="btn-icon btn-delete" title="Supprimer" onclick="return confirm('Supprimer ce produit ?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($products)): ?><tr><td colspan="8" style="text-align:center;">Aucun produit</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('previewImg');
    const text = document.getElementById('previewText');
    const zone = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            text.style.display = 'none';
            zone.classList.add('has-image');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
// Drag & drop on image zone
const zone = document.getElementById('imagePreview');
if (zone) {
    ['dragenter','dragover'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.style.borderColor = 'var(--secondary)'; }));
    ['dragleave','drop'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.style.borderColor = ''; }));
    zone.addEventListener('drop', e => {
        const input = document.getElementById('imageInput');
        input.files = e.dataTransfer.files;
        previewImage(input);
    });
}
function filterProducts(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#productsTable tbody tr').forEach(row => {
        const name = row.dataset.name || '';
        const cat = row.dataset.cat || '';
        row.style.display = (name.includes(q) || cat.includes(q)) ? '' : 'none';
    });
}
function toggleField(id, field, val) {
    const form = new FormData();
    form.append('action', 'toggle');
    form.append('id', id);
    form.append('field', field);
    form.append('val', val);
    fetch('products.php', { method: 'POST', body: form }).then(() => location.reload());
}
</script>

<?php require_once 'includes/footer.php'; ?>