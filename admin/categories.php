<?php
$adminTitle = 'Gestion des Catégories';
require_once 'includes/header.php';

$success = $error = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $success = 'Catégorie modifiée.';
        } else {
            $pdo->prepare("INSERT INTO categories (name, slug, description, icon, is_active, sort_order) VALUES (?,?,?,?,?,?)")
                ->execute([$name, $slug, $desc, $icon, $isActive, $sortOrder]);
            $success = 'Catégorie ajoutée.';
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
?>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

<div class="card" style="margin-bottom:30px;">
    <h3><i class="fas fa-<?= $editCat ? 'edit' : 'plus' ?>"></i> <?= $editCat ? 'Modifier' : 'Ajouter' ?> une Catégorie</h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editCat['id'] ?? 0 ?>">
        <div class="form-row">
            <div class="form-group"><label>Nom *</label><input type="text" name="name" value="<?= sanitize($editCat['name'] ?? '') ?>" required></div>
            <div class="form-group"><label>Icône (Font Awesome)</label><input type="text" name="icon" value="<?= sanitize($editCat['icon'] ?? 'fas fa-box') ?>" placeholder="fas fa-box"></div>
        </div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= sanitize($editCat['description'] ?? '') ?></textarea></div>
        <div class="form-row">
            <div class="form-group"><label>Ordre</label><input type="number" name="sort_order" value="<?= $editCat['sort_order'] ?? 0 ?>"></div>
            <label class="checkbox-label"><input type="checkbox" name="is_active" <?= ($editCat['is_active'] ?? 1) ? 'checked' : '' ?> checked> Active</label>
        </div>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editCat ? 'Modifier' : 'Ajouter' ?></button>
        <?php if($editCat): ?><a href="categories.php" class="btn-admin btn-admin-secondary">Annuler</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3><i class="fas fa-list"></i> Catégories (<?= count($categories) ?>)</h3>
    <table class="admin-table">
        <thead><tr><th>Icône</th><th>Nom</th><th>Produits</th><th>Active</th><th>Ordre</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($categories as $c): ?>
        <tr>
            <td><i class="<?= sanitize($c['icon']) ?>" style="font-size:1.3rem; color:#4ECDC4;"></i></td>
            <td><strong><?= sanitize($c['name']) ?></strong></td>
            <td><span class="badge badge-primary"><?= $c['product_count'] ?></span></td>
            <td><?= $c['is_active'] ? '<span class="badge badge-success">Oui</span>' : '<span class="badge badge-danger">Non</span>' ?></td>
            <td><?= $c['sort_order'] ?></td>
            <td class="actions">
                <a href="?edit=<?= $c['id'] ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
                <a href="?delete=<?= $c['id'] ?>" class="btn-icon btn-delete" onclick="return confirm('Supprimer cette catégorie ?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>