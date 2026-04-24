<?php
$adminTitle = 'Gestion des Clients Packages';
require_once 'includes/header.php';

$success = '';
$error = '';

// Get all active packages for dropdown
$allPackages = $pdo->query("SELECT id, name, color FROM packages ORDER BY sort_order")->fetchAll();

// Status update
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $newStatus = $_GET['status'];
    $allowed = ['pending','active','expired','cancelled'];
    if (in_array($newStatus, $allowed)) {
        $updates = ["status = ?"];
        $params = [$newStatus];
        if ($newStatus === 'active') {
            $updates[] = "activated_at = NOW()";
            // Get package duration
            $dur = $pdo->prepare("SELECT p.duration_months FROM customer_packages cp JOIN packages p ON cp.package_id = p.id WHERE cp.id = ?");
            $dur->execute([$id]);
            $d = $dur->fetch();
            if ($d) {
                $updates[] = "expires_at = DATE_ADD(NOW(), INTERVAL ? MONTH)";
                $params[] = $d['duration_months'];
            }
        }
        $params[] = $id;
        $pdo->prepare("UPDATE customer_packages SET " . implode(', ', $updates) . " WHERE id = ?")->execute($params);
        logAdminActivity($pdo, $_SESSION['admin_id'], 'update_customer_package', "Client package #$id → $newStatus");
        header('Location: customer_packages.php?msg=updated');
        exit;
    }
}

// Delete
if (isset($_GET['delete']) && isSuperAdmin()) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM customer_packages WHERE id = ?")->execute([$id]);
    logAdminActivity($pdo, $_SESSION['admin_id'], 'delete_customer_package', "Client package #$id supprimé");
    header('Location: customer_packages.php?msg=deleted');
    exit;
}

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_company = trim($_POST['customer_company'] ?? '');
    $customer_city = trim($_POST['customer_city'] ?? '');
    $package_id = (int)($_POST['package_id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($customer_name) || !$package_id) {
        $error = 'Le nom du client et le package sont requis.';
    } else {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO customer_packages (customer_name, customer_email, customer_phone, customer_company, customer_city, package_id, status, notes) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$customer_name, $customer_email, $customer_phone, $customer_company, $customer_city, $package_id, $status, $notes]);
            $newId = $pdo->lastInsertId();
            if ($status === 'active') {
                $dur = $pdo->prepare("SELECT duration_months FROM packages WHERE id = ?");
                $dur->execute([$package_id]);
                $d = $dur->fetch();
                $pdo->prepare("UPDATE customer_packages SET activated_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? MONTH) WHERE id = ?")->execute([$d['duration_months'] ?? 12, $newId]);
            }
            logAdminActivity($pdo, $_SESSION['admin_id'], 'add_customer_package', "Client '$customer_name' assigné au package #$package_id");
            $success = 'Client ajouté avec succès !';
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE customer_packages SET customer_name=?, customer_email=?, customer_phone=?, customer_company=?, customer_city=?, package_id=?, status=?, notes=? WHERE id=?");
            $stmt->execute([$customer_name, $customer_email, $customer_phone, $customer_company, $customer_city, $package_id, $status, $notes, $id]);
            logAdminActivity($pdo, $_SESSION['admin_id'], 'edit_customer_package', "Client package #$id modifié");
            $success = 'Client modifié avec succès !';
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'updated') $success = 'Statut mis à jour.';
    if ($_GET['msg'] === 'deleted') $success = 'Supprimé avec succès.';
}

// Filters
$filterStatus = $_GET['filter_status'] ?? '';
$filterPackage = (int)($_GET['filter_package'] ?? 0);
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];
if ($filterStatus) { $where[] = "cp.status = ?"; $params[] = $filterStatus; }
if ($filterPackage) { $where[] = "cp.package_id = ?"; $params[] = $filterPackage; }
if ($search) { $where[] = "(cp.customer_name LIKE ? OR cp.customer_company LIKE ? OR cp.customer_phone LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$customers = $pdo->prepare("SELECT cp.*, p.name as package_name, p.color as package_color, p.icon as package_icon FROM customer_packages cp LEFT JOIN packages p ON cp.package_id = p.id $whereSQL ORDER BY cp.created_at DESC");
$customers->execute($params);
$customers = $customers->fetchAll();

// Stats
$totalClients = $pdo->query("SELECT COUNT(*) FROM customer_packages")->fetchColumn();
$activeClients = $pdo->query("SELECT COUNT(*) FROM customer_packages WHERE status='active'")->fetchColumn();
$pendingClients = $pdo->query("SELECT COUNT(*) FROM customer_packages WHERE status='pending'")->fetchColumn();
$expiredClients = $pdo->query("SELECT COUNT(*) FROM customer_packages WHERE status='expired'")->fetchColumn();

// Edit mode
$editCustomer = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM customer_packages WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCustomer = $stmt->fetch();
}
?>

<!-- Stats -->
<div class="stats-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px; margin-bottom:25px;">
    <div class="stat-card" style="background:linear-gradient(135deg,#1B3A5C,#2d5a8e); color:#fff; padding:20px; border-radius:12px;">
        <div style="font-size:2rem; font-weight:700;"><?= $totalClients ?></div>
        <div style="opacity:0.8;">Total Clients</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#2ecc71); color:#fff; padding:20px; border-radius:12px;">
        <div style="font-size:2rem; font-weight:700;"><?= $activeClients ?></div>
        <div style="opacity:0.8;">Actifs</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#f1c40f); color:#fff; padding:20px; border-radius:12px;">
        <div style="font-size:2rem; font-weight:700;"><?= $pendingClients ?></div>
        <div style="opacity:0.8;">En attente</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff; padding:20px; border-radius:12px;">
        <div style="font-size:2rem; font-weight:700;"><?= $expiredClients ?></div>
        <div style="opacity:0.8;">Expirés</div>
    </div>
</div>

<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<!-- Form -->
<div class="card" style="margin-bottom:25px;">
    <div class="card-header">
        <h3><?= $editCustomer ? 'Modifier le client' : 'Ajouter un client' ?></h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editCustomer ? 'edit' : 'add' ?>">
            <?php if($editCustomer): ?><input type="hidden" name="id" value="<?= $editCustomer['id'] ?>"><?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Nom du client *</label>
                    <input type="text" name="customer_name" class="form-control" value="<?= sanitize($editCustomer['customer_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="customer_email" class="form-control" value="<?= sanitize($editCustomer['customer_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="customer_phone" class="form-control" value="<?= sanitize($editCustomer['customer_phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Entreprise</label>
                    <input type="text" name="customer_company" class="form-control" value="<?= sanitize($editCustomer['customer_company'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="customer_city" class="form-control" value="<?= sanitize($editCustomer['customer_city'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Package *</label>
                    <select name="package_id" class="form-control" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach($allPackages as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($editCustomer['package_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= sanitize($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="status" class="form-control">
                        <option value="pending" <?= ($editCustomer['status'] ?? '') === 'pending' ? 'selected' : '' ?>>En attente</option>
                        <option value="active" <?= ($editCustomer['status'] ?? '') === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="expired" <?= ($editCustomer['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expiré</option>
                        <option value="cancelled" <?= ($editCustomer['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Annulé</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Notes</label>
                    <input type="text" name="notes" class="form-control" value="<?= sanitize($editCustomer['notes'] ?? '') ?>">
                </div>
            </div>
            <div style="margin-top:15px; display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary"><?= $editCustomer ? 'Modifier' : 'Ajouter' ?></button>
                <?php if($editCustomer): ?><a href="customer_packages.php" class="btn btn-secondary">Annuler</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:25px;">
    <div class="card-body">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
            <div class="form-group" style="flex:1; min-width:200px;">
                <label>Rechercher</label>
                <input type="text" name="search" class="form-control" value="<?= sanitize($search) ?>" placeholder="Nom, entreprise, téléphone...">
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="filter_status" class="form-control">
                    <option value="">Tous</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>En attente</option>
                    <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Actif</option>
                    <option value="expired" <?= $filterStatus === 'expired' ? 'selected' : '' ?>>Expiré</option>
                    <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Annulé</option>
                </select>
            </div>
            <div class="form-group">
                <label>Package</label>
                <select name="filter_package" class="form-control">
                    <option value="">Tous</option>
                    <?php foreach($allPackages as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filterPackage == $p['id'] ? 'selected' : '' ?>><?= sanitize($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height:42px;">Filtrer</button>
            <a href="customer_packages.php" class="btn btn-secondary" style="height:42px;">Reset</a>
        </form>
    </div>
</div>

<!-- List -->
<div class="card">
    <div class="card-header"><h3>Clients (<?= count($customers) ?>)</h3></div>
    <div class="card-body">
        <?php if(empty($customers)): ?>
            <p style="text-align:center; color:#999; padding:30px;">Aucun client trouvé.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Package</th>
                        <th>Statut</th>
                        <th>Activation</th>
                        <th>Expiration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($customers as $c): ?>
                    <tr>
                        <td>
                            <strong><?= sanitize($c['customer_name']) ?></strong>
                            <?php if($c['customer_company']): ?><br><small style="color:#999;"><?= sanitize($c['customer_company']) ?></small><?php endif; ?>
                            <?php if($c['customer_city']): ?><br><small style="color:#aaa;"><i class="fas fa-map-marker-alt"></i> <?= sanitize($c['customer_city']) ?></small><?php endif; ?>
                        </td>
                        <td>
                            <?php if($c['customer_phone']): ?><div><i class="fas fa-phone" style="color:#999; width:16px;"></i> <?= sanitize($c['customer_phone']) ?></div><?php endif; ?>
                            <?php if($c['customer_email']): ?><div><i class="fas fa-envelope" style="color:#999; width:16px;"></i> <?= sanitize($c['customer_email']) ?></div><?php endif; ?>
                        </td>
                        <td>
                            <?php if($c['package_name']): ?>
                            <span style="background:<?= sanitize($c['package_color'] ?? '#4ECDC4') ?>20; color:<?= sanitize($c['package_color'] ?? '#4ECDC4') ?>; padding:4px 12px; border-radius:20px; font-weight:600; font-size:0.85rem;">
                                <i class="<?= sanitize($c['package_icon'] ?? 'fas fa-box') ?>"></i> <?= sanitize($c['package_name']) ?>
                            </span>
                            <?php else: ?>
                            <em style="color:#999;">Supprimé</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusLabels = ['pending' => 'En attente', 'active' => 'Actif', 'expired' => 'Expiré', 'cancelled' => 'Annulé'];
                            $statusColors = ['pending' => '#f39c12', 'active' => '#27ae60', 'expired' => '#e74c3c', 'cancelled' => '#95a5a6'];
                            $st = $c['status'];
                            ?>
                            <span style="background:<?= $statusColors[$st] ?>20; color:<?= $statusColors[$st] ?>; padding:4px 12px; border-radius:20px; font-weight:600; font-size:0.85rem;">
                                <?= $statusLabels[$st] ?>
                            </span>
                        </td>
                        <td><?= $c['activated_at'] ? date('d/m/Y', strtotime($c['activated_at'])) : '-' ?></td>
                        <td>
                            <?php if($c['expires_at']): ?>
                                <?php $exp = strtotime($c['expires_at']); $isExpired = $exp < time(); ?>
                                <span style="color:<?= $isExpired ? '#e74c3c' : '#27ae60' ?>; font-weight:600;">
                                    <?= date('d/m/Y', $exp) ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:3px; flex-wrap:wrap;">
                                <?php if($c['status'] === 'pending'): ?>
                                <a href="customer_packages.php?id=<?= $c['id'] ?>&status=active" class="btn-icon" style="background:#27ae60; color:#fff;" title="Activer" onclick="return confirm('Activer ce client ?')"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                                <?php if($c['status'] === 'active'): ?>
                                <a href="customer_packages.php?id=<?= $c['id'] ?>&status=expired" class="btn-icon" style="background:#e74c3c; color:#fff;" title="Expirer" onclick="return confirm('Marquer comme expiré ?')"><i class="fas fa-clock"></i></a>
                                <?php endif; ?>
                                <?php if($c['status'] === 'expired' || $c['status'] === 'cancelled'): ?>
                                <a href="customer_packages.php?id=<?= $c['id'] ?>&status=active" class="btn-icon" style="background:#27ae60; color:#fff;" title="Réactiver"><i class="fas fa-redo"></i></a>
                                <?php endif; ?>
                                <a href="customer_packages.php?edit=<?= $c['id'] ?>" class="btn-icon" title="Modifier"><i class="fas fa-edit"></i></a>
                                <?php if(isSuperAdmin()): ?>
                                <a href="customer_packages.php?delete=<?= $c['id'] ?>" class="btn-icon btn-danger" title="Supprimer" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
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

<?php require_once 'includes/footer.php'; ?>
