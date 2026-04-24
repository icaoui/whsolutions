<?php
$adminTitle = 'Gestion des Packages';
require_once 'includes/header.php';

$success = '';
$error = '';

// Delete package
if (isset($_GET['delete']) && isSuperAdmin()) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM package_features WHERE package_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM packages WHERE id = ?")->execute([$id]);
    logAdminActivity($pdo, $_SESSION['admin_id'], 'delete_package', "Package #$id supprimé");
    header('Location: packages.php?msg=deleted');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] !== '' ? (float)$_POST['price'] : null;
    $price_label = trim($_POST['price_label'] ?? '');
    $duration = (int)($_POST['duration_months'] ?? 12);
    $icon = trim($_POST['icon'] ?? 'fas fa-box');
    $color = trim($_POST['color'] ?? '#4ECDC4');
    $badge = trim($_POST['badge'] ?? '');
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $slug = slugify($name);

    if (empty($name)) {
        $error = 'Le nom du package est requis.';
    } else {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO packages (name, slug, subtitle, description, price, price_label, duration_months, icon, color, badge, is_popular, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$name, $slug, $subtitle, $description, $price, $price_label, $duration, $icon, $color, $badge, $is_popular, $is_active, $sort_order]);
            $packageId = $pdo->lastInsertId();

            // Save features
            if (!empty($_POST['features'])) {
                $featStmt = $pdo->prepare("INSERT INTO package_features (package_id, feature_text, is_included, sort_order) VALUES (?,?,?,?)");
                foreach ($_POST['features'] as $i => $feat) {
                    $feat = trim($feat);
                    if ($feat !== '') {
                        $included = isset($_POST['feature_included'][$i]) ? 1 : 0;
                        $featStmt->execute([$packageId, $feat, $included, $i]);
                    }
                }
            }

            logAdminActivity($pdo, $_SESSION['admin_id'], 'add_package', "Package '$name' ajouté");
            $success = 'Package ajouté avec succès !';
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE packages SET name=?, slug=?, subtitle=?, description=?, price=?, price_label=?, duration_months=?, icon=?, color=?, badge=?, is_popular=?, is_active=?, sort_order=? WHERE id=?");
            $stmt->execute([$name, $slug, $subtitle, $description, $price, $price_label, $duration, $icon, $color, $badge, $is_popular, $is_active, $sort_order, $id]);

            // Delete old features and re-insert
            $pdo->prepare("DELETE FROM package_features WHERE package_id = ?")->execute([$id]);
            if (!empty($_POST['features'])) {
                $featStmt = $pdo->prepare("INSERT INTO package_features (package_id, feature_text, is_included, sort_order) VALUES (?,?,?,?)");
                foreach ($_POST['features'] as $i => $feat) {
                    $feat = trim($feat);
                    if ($feat !== '') {
                        $included = isset($_POST['feature_included'][$i]) ? 1 : 0;
                        $featStmt->execute([$id, $feat, $included, $i]);
                    }
                }
            }

            logAdminActivity($pdo, $_SESSION['admin_id'], 'edit_package', "Package '$name' modifié");
            $success = 'Package modifié avec succès !';
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') $success = 'Package supprimé.';

// Fetch all packages
$packages = $pdo->query("SELECT p.*, (SELECT COUNT(*) FROM customer_packages cp WHERE cp.package_id = p.id) as client_count FROM packages p ORDER BY p.sort_order, p.id")->fetchAll();

// Fetch edit package
$editPackage = null;
$editFeatures = [];
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$editId]);
    $editPackage = $stmt->fetch();
    if ($editPackage) {
        $stmt2 = $pdo->prepare("SELECT * FROM package_features WHERE package_id = ? ORDER BY sort_order");
        $stmt2->execute([$editId]);
        $editFeatures = $stmt2->fetchAll();
    }
}
?>

<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<!-- Form -->
<div class="card" style="margin-bottom:25px;">
    <div class="card-header">
        <h3><?= $editPackage ? 'Modifier le Package' : 'Ajouter un Package' ?></h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editPackage ? 'edit' : 'add' ?>">
            <?php if($editPackage): ?><input type="hidden" name="id" value="<?= $editPackage['id'] ?>"><?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Nom du package *</label>
                    <input type="text" name="name" class="form-control" value="<?= sanitize($editPackage['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Sous-titre</label>
                    <input type="text" name="subtitle" class="form-control" value="<?= sanitize($editPackage['subtitle'] ?? '') ?>" placeholder="Ex: Idéal pour les restaurants">
                </div>
                <div class="form-group">
                    <label>Prix (MAD)</label>
                    <input type="number" name="price" class="form-control" step="0.01" value="<?= $editPackage['price'] ?? '' ?>" placeholder="Laisser vide = Sur devis">
                </div>
                <div class="form-group">
                    <label>Label prix</label>
                    <input type="text" name="price_label" class="form-control" value="<?= sanitize($editPackage['price_label'] ?? '') ?>" placeholder="Ex: /mois, HT, Sur devis">
                </div>
                <div class="form-group">
                    <label>Durée (mois)</label>
                    <input type="number" name="duration_months" class="form-control" value="<?= $editPackage['duration_months'] ?? 12 ?>">
                </div>
                <div class="form-group">
                    <label>Icône (FontAwesome)</label>
                    <input type="text" name="icon" class="form-control" value="<?= sanitize($editPackage['icon'] ?? 'fas fa-box') ?>" placeholder="fas fa-box">
                </div>
                <div class="form-group">
                    <label>Couleur</label>
                    <input type="color" name="color" class="form-control" value="<?= $editPackage['color'] ?? '#4ECDC4' ?>" style="height:42px;">
                </div>
                <div class="form-group">
                    <label>Badge (optionnel)</label>
                    <input type="text" name="badge" class="form-control" value="<?= sanitize($editPackage['badge'] ?? '') ?>" placeholder="Ex: Populaire, Meilleur rapport">
                </div>
                <div class="form-group">
                    <label>Ordre</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $editPackage['sort_order'] ?? 0 ?>">
                </div>
                <div class="form-group" style="display:flex; gap:20px; align-items:center; padding-top:25px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_popular" <?= !empty($editPackage['is_popular']) ? 'checked' : '' ?>> Populaire (mis en avant)
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" <?= ($editPackage === null || !empty($editPackage['is_active'])) ? 'checked' : '' ?>> Actif
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"><?= sanitize($editPackage['description'] ?? '') ?></textarea>
            </div>

            <!-- Features -->
            <div style="margin-top:20px; border-top:1px solid #eee; padding-top:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h4 style="margin:0;">Caractéristiques du package</h4>
                    <button type="button" onclick="addFeature()" class="btn-sm" style="background:var(--secondary,#4ECDC4); color:#fff; border:none; padding:8px 16px; border-radius:8px; cursor:pointer;">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                </div>
                <div id="features-list">
                    <?php if($editFeatures): ?>
                        <?php foreach($editFeatures as $i => $f): ?>
                        <div class="feature-row" style="display:flex; gap:10px; align-items:center; margin-bottom:8px;">
                            <label style="display:flex; align-items:center; gap:5px; white-space:nowrap;">
                                <input type="checkbox" name="feature_included[<?= $i ?>]" <?= $f['is_included'] ? 'checked' : '' ?>> Inclus
                            </label>
                            <input type="text" name="features[<?= $i ?>]" class="form-control" value="<?= sanitize($f['feature_text']) ?>" style="flex:1;">
                            <button type="button" onclick="this.parentElement.remove()" style="background:#e74c3c; color:#fff; border:none; width:36px; height:36px; border-radius:8px; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="feature-row" style="display:flex; gap:10px; align-items:center; margin-bottom:8px;">
                            <label style="display:flex; align-items:center; gap:5px; white-space:nowrap;">
                                <input type="checkbox" name="feature_included[0]" checked> Inclus
                            </label>
                            <input type="text" name="features[0]" class="form-control" placeholder="Ex: Livraison gratuite" style="flex:1;">
                            <button type="button" onclick="this.parentElement.remove()" style="background:#e74c3c; color:#fff; border:none; width:36px; height:36px; border-radius:8px; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary"><?= $editPackage ? 'Modifier' : 'Ajouter' ?></button>
                <?php if($editPackage): ?>
                <a href="packages.php" class="btn btn-secondary">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Packages List -->
<div class="card">
    <div class="card-header"><h3>Packages existants (<?= count($packages) ?>)</h3></div>
    <div class="card-body">
        <?php if(empty($packages)): ?>
            <p style="text-align:center; color:#999; padding:30px;">Aucun package créé. Utilisez le formulaire ci-dessus pour en ajouter.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ordre</th>
                        <th>Package</th>
                        <th>Prix</th>
                        <th>Durée</th>
                        <th>Clients</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($packages as $pkg): ?>
                    <tr>
                        <td><?= $pkg['sort_order'] ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:40px; height:40px; border-radius:10px; background:<?= sanitize($pkg['color']) ?>20; display:flex; align-items:center; justify-content:center;">
                                    <i class="<?= sanitize($pkg['icon']) ?>" style="color:<?= sanitize($pkg['color']) ?>;"></i>
                                </div>
                                <div>
                                    <strong><?= sanitize($pkg['name']) ?></strong>
                                    <?php if($pkg['badge']): ?><span style="background:<?= sanitize($pkg['color']) ?>; color:#fff; font-size:0.7rem; padding:2px 8px; border-radius:10px; margin-left:5px;"><?= sanitize($pkg['badge']) ?></span><?php endif; ?>
                                    <?php if($pkg['subtitle']): ?><br><small style="color:#999;"><?= sanitize($pkg['subtitle']) ?></small><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if($pkg['price']): ?>
                                <strong><?= number_format($pkg['price'], 2) ?> MAD</strong>
                                <?php if($pkg['price_label']): ?><br><small><?= sanitize($pkg['price_label']) ?></small><?php endif; ?>
                            <?php else: ?>
                                <em>Sur devis</em>
                            <?php endif; ?>
                        </td>
                        <td><?= $pkg['duration_months'] ?> mois</td>
                        <td><span style="background:#e8f5e9; color:#2e7d32; padding:4px 12px; border-radius:20px; font-weight:600;"><?= $pkg['client_count'] ?></span></td>
                        <td>
                            <?php if($pkg['is_active']): ?>
                                <span class="status-badge status-active">Actif</span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">Inactif</span>
                            <?php endif; ?>
                            <?php if($pkg['is_popular']): ?>
                                <span style="color:gold; margin-left:5px;" title="Populaire"><i class="fas fa-star"></i></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:5px;">
                                <a href="packages.php?edit=<?= $pkg['id'] ?>" class="btn-icon" title="Modifier"><i class="fas fa-edit"></i></a>
                                <?php if(isSuperAdmin()): ?>
                                <a href="packages.php?delete=<?= $pkg['id'] ?>" class="btn-icon btn-danger" title="Supprimer" onclick="return confirm('Supprimer ce package ?')"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let featureIndex = <?= max(count($editFeatures), 1) ?>;
function addFeature() {
    const list = document.getElementById('features-list');
    const row = document.createElement('div');
    row.className = 'feature-row';
    row.style.cssText = 'display:flex; gap:10px; align-items:center; margin-bottom:8px;';
    row.innerHTML = `
        <label style="display:flex; align-items:center; gap:5px; white-space:nowrap;">
            <input type="checkbox" name="feature_included[${featureIndex}]" checked> Inclus
        </label>
        <input type="text" name="features[${featureIndex}]" class="form-control" placeholder="Caractéristique..." style="flex:1;">
        <button type="button" onclick="this.parentElement.remove()" style="background:#e74c3c; color:#fff; border:none; width:36px; height:36px; border-radius:8px; cursor:pointer;"><i class="fas fa-times"></i></button>
    `;
    list.appendChild(row);
    featureIndex++;
}
</script>

<?php require_once 'includes/footer.php'; ?>
