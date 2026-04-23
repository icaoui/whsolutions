<?php
$adminTitle = 'Gestion des Produits';
require_once 'includes/header.php';

$success = $error = '';

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $prod = getProductById($pdo, $id);
    if ($prod) {
        if (!empty($prod['image']) && file_exists(PRODUCTS_UPLOAD_PATH . $prod['image'])) {
            unlink(PRODUCTS_UPLOAD_PATH . $prod['image']);
        }
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $success = 'Produit supprimé avec succès.';
    }
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Image upload
    $imageName = '';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $imageName = $slug . '-' . time() . '.' . $ext;
            if (!is_dir(PRODUCTS_UPLOAD_PATH)) mkdir(PRODUCTS_UPLOAD_PATH, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], PRODUCTS_UPLOAD_PATH . $imageName);
        }
    }

    if (empty($name)) {
        $error = 'Le nom du produit est obligatoire.';
    } else {
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

<!-- Form -->
<div class="card" style="margin-bottom:30px;">
    <h3><i class="fas fa-<?= $editProduct ? 'edit' : 'plus' ?>"></i> <?= $editProduct ? 'Modifier le Produit' : 'Ajouter un Produit' ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $editProduct['id'] ?? 0 ?>">
        <div class="form-row">
            <div class="form-group"><label>Nom *</label><input type="text" name="name" value="<?= sanitize($editProduct['name'] ?? '') ?>" required></div>
            <div class="form-group"><label>Catégorie</label>
                <select name="category_id">
                    <option value="0">-- Aucune --</option>
                    <?php foreach($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($editProduct['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group"><label>Description courte</label><input type="text" name="short_description" value="<?= sanitize($editProduct['short_description'] ?? '') ?>"></div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="4"><?= sanitize($editProduct['description'] ?? '') ?></textarea></div>
        <div class="form-row">
            <div class="form-group"><label>Référence</label><input type="text" name="reference" value="<?= sanitize($editProduct['reference'] ?? '') ?>"></div>
            <div class="form-group"><label>Ordre</label><input type="number" name="sort_order" value="<?= $editProduct['sort_order'] ?? 0 ?>"></div>
        </div>
        <div class="form-group"><label>Image</label><input type="file" name="image" accept="image/*">
            <?php if(!empty($editProduct['image'])): ?>
            <img src="<?= SITE_URL ?>/uploads/products/<?= $editProduct['image'] ?>" style="max-height:80px; margin-top:10px; border-radius:8px;">
            <?php endif; ?>
        </div>
        <div class="form-row">
            <label class="checkbox-label"><input type="checkbox" name="is_featured" <?= ($editProduct['is_featured'] ?? 0) ? 'checked' : '' ?>> Produit phare</label>
            <label class="checkbox-label"><input type="checkbox" name="is_active" <?= ($editProduct['is_active'] ?? 1) ? 'checked' : '' ?> checked> Actif</label>
        </div>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editProduct ? 'Modifier' : 'Ajouter' ?></button>
        <?php if($editProduct): ?><a href="products.php" class="btn-admin btn-admin-secondary">Annuler</a><?php endif; ?>
    </form>
</div>

<!-- Products Table -->
<div class="card">
    <h3><i class="fas fa-list"></i> Liste des Produits (<?= count($products) ?>)</h3>
    <table class="admin-table">
        <thead><tr><th>Image</th><th>Nom</th><th>Catégorie</th><th>Réf</th><th>Phare</th><th>Actif</th><th>Vues</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($products as $p): ?>
        <tr>
            <td><?php if(!empty($p['image'])): ?><img src="<?= SITE_URL ?>/uploads/products/<?= $p['image'] ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px;"><?php else: ?><i class="fas fa-image" style="color:#ccc;font-size:1.5rem;"></i><?php endif; ?></td>
            <td><strong><?= sanitize($p['name']) ?></strong></td>
            <td><span class="badge badge-primary"><?= sanitize($p['category_name'] ?? 'N/A') ?></span></td>
            <td><?= sanitize($p['reference'] ?? '-') ?></td>
            <td><?= $p['is_featured'] ? '<i class="fas fa-star" style="color:#FFE66D;"></i>' : '-' ?></td>
            <td><?= $p['is_active'] ? '<span class="badge badge-success">Oui</span>' : '<span class="badge badge-danger">Non</span>' ?></td>
            <td><?= $p['views'] ?></td>
            <td class="actions">
                <a href="?edit=<?= $p['id'] ?>" class="btn-icon btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
                <a href="?delete=<?= $p['id'] ?>" class="btn-icon btn-delete" title="Supprimer" onclick="return confirm('Supprimer ce produit ?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>