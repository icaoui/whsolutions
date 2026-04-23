<?php
$adminTitle = 'Gestion des Utilisateurs';
require_once 'includes/header.php';

// Super admin only
if (!isSuperAdmin()) {
    echo '<div class="alert alert-error"><i class="fas fa-lock"></i> Accès réservé au Super Administrateur.</div>';
    require_once 'includes/footer.php';
    exit;
}

$success = $error = '';

// Delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id === $_SESSION['admin_id']) {
        $error = 'Vous ne pouvez pas supprimer votre propre compte.';
    } else {
        $pdo->prepare("DELETE FROM admins WHERE id = ? AND role != 'super_admin'")->execute([$id]);
        logAdminActivity($pdo, $_SESSION['admin_id'], 'delete_user', "Suppression utilisateur #$id");
        $success = 'Utilisateur supprimé.';
    }
}

// Toggle active
if (isset($_GET['toggle']) && isset($_GET['val'])) {
    $id = intval($_GET['toggle']);
    if ($id === $_SESSION['admin_id']) {
        $error = 'Vous ne pouvez pas désactiver votre propre compte.';
    } else {
        $pdo->prepare("UPDATE admins SET is_active = ? WHERE id = ?")->execute([intval($_GET['val']), $id]);
        logAdminActivity($pdo, $_SESSION['admin_id'], 'toggle_user', "Activation/désactivation utilisateur #$id");
        $success = 'Statut modifié.';
    }
}

// Add / Edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $username = sanitize($_POST['username'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['super_admin', 'admin']) ? $_POST['role'] : 'admin';
    $password = $_POST['password'] ?? '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($username) || empty($name)) {
        $error = 'Le nom d\'utilisateur et le nom complet sont obligatoires.';
    } else {
        // Check unique username
        $check = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
        $check->execute([$username, $id]);
        if ($check->fetch()) {
            $error = 'Ce nom d\'utilisateur est déjà utilisé.';
        } else {
            if ($id > 0) {
                // Update
                $sql = "UPDATE admins SET username=?, name=?, email=?, role=?, is_active=?";
                $params = [$username, $name, $email, $role, $isActive];
                if (!empty($password)) {
                    if (strlen($password) < 4) {
                        $error = 'Le mot de passe doit contenir au moins 4 caractères.';
                    } else {
                        $sql .= ", password=?";
                        $params[] = password_hash($password, PASSWORD_DEFAULT);
                    }
                }
                if (!$error) {
                    $sql .= " WHERE id=?";
                    $params[] = $id;
                    $pdo->prepare($sql)->execute($params);
                    logAdminActivity($pdo, $_SESSION['admin_id'], 'edit_user', "Modification utilisateur: $username");
                    $success = 'Utilisateur modifié avec succès.';
                }
            } else {
                // Create
                if (empty($password) || strlen($password) < 4) {
                    $error = 'Le mot de passe est obligatoire (min. 4 caractères).';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->prepare("INSERT INTO admins (username, password, name, email, role, is_active) VALUES (?,?,?,?,?,?)")
                        ->execute([$username, $hash, $name, $email, $role, $isActive]);
                    logAdminActivity($pdo, $_SESSION['admin_id'], 'create_user', "Création utilisateur: $username ($role)");
                    $success = 'Utilisateur créé avec succès.';
                }
            }
        }
    }
}

$users = $pdo->query("SELECT * FROM admins ORDER BY role DESC, created_at ASC")->fetchAll();
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editUser = $stmt->fetch();
}
?>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

<!-- Stats -->
<div class="stats-row" style="display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-bottom:25px;">
    <div class="card" style="text-align:center; padding:20px;">
        <div style="font-size:2rem; font-weight:800; color:var(--primary);"><?= count($users) ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Utilisateurs</div>
    </div>
    <div class="card" style="text-align:center; padding:20px;">
        <div style="font-size:2rem; font-weight:800; color:#FFE66D;"><?= count(array_filter($users, fn($u) => $u['role'] === 'super_admin')) ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Super Admins</div>
    </div>
    <div class="card" style="text-align:center; padding:20px;">
        <div style="font-size:2rem; font-weight:800; color:var(--secondary);"><?= count(array_filter($users, fn($u) => ($u['is_active'] ?? 1))) ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Actifs</div>
    </div>
</div>

<!-- Form -->
<div class="card" style="margin-bottom:25px;">
    <h3><i class="fas fa-<?= $editUser ? 'edit' : 'user-plus' ?>"></i> <?= $editUser ? 'Modifier l\'Utilisateur' : 'Ajouter un Utilisateur' ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editUser['id'] ?? 0 ?>">
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom d'utilisateur *</label>
                <input type="text" name="username" value="<?= sanitize($editUser['username'] ?? '') ?>" required placeholder="ex: jean.dupont" autocomplete="off">
            </div>
            <div class="form-group">
                <label><i class="fas fa-id-card"></i> Nom complet *</label>
                <input type="text" name="name" value="<?= sanitize($editUser['name'] ?? '') ?>" required placeholder="ex: Jean Dupont">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" value="<?= sanitize($editUser['email'] ?? '') ?>" placeholder="jean@example.com">
            </div>
            <div class="form-group">
                <label><i class="fas fa-key"></i> Mot de passe <?= $editUser ? '(laisser vide pour ne pas changer)' : '*' ?></label>
                <input type="password" name="password" placeholder="<?= $editUser ? '••••••••' : 'Min. 4 caractères' ?>" autocomplete="new-password" <?= $editUser ? '' : 'required' ?>>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-shield-alt"></i> Rôle</label>
                <select name="role">
                    <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                    <option value="super_admin" <?= ($editUser['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Administrateur</option>
                </select>
            </div>
            <label class="checkbox-label" style="padding-top:28px;">
                <input type="checkbox" name="is_active" <?= ($editUser['is_active'] ?? 1) ? 'checked' : '' ?> checked>
                <i class="fas fa-toggle-on" style="color:var(--secondary);"></i> Compte actif
            </label>
        </div>
        <div style="display:flex; gap:10px; margin-top:10px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editUser ? 'Modifier' : 'Créer le Compte' ?></button>
            <?php if($editUser): ?><a href="users.php" class="btn-admin btn-admin-secondary">Annuler</a><?php endif; ?>
        </div>
    </form>
</div>

<!-- Users List -->
<div class="card">
    <h3><i class="fas fa-users-cog"></i> Utilisateurs (<?= count($users) ?>)</h3>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Actif</th>
                <th>Dernière connexion</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($users as $u): ?>
        <tr>
            <td>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:38px;height:38px;border-radius:50%;background:<?= $u['role'] === 'super_admin' ? 'linear-gradient(135deg,#FFE66D,#FFB800)' : 'linear-gradient(135deg,var(--secondary),#00F5D4)' ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.85rem;">
                        <?= strtoupper(mb_substr($u['name'], 0, 2)) ?>
                    </div>
                    <div>
                        <strong><?= sanitize($u['name']) ?></strong><br>
                        <small style="color:var(--gray-500);">@<?= sanitize($u['username']) ?></small>
                    </div>
                </div>
            </td>
            <td><?= sanitize($u['email'] ?? '-') ?></td>
            <td>
                <?php if($u['role'] === 'super_admin'): ?>
                <span class="role-badge role-super"><i class="fas fa-crown"></i> Super Admin</span>
                <?php else: ?>
                <span class="role-badge role-admin"><i class="fas fa-user"></i> Admin</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="?toggle=<?= $u['id'] ?>&val=<?= ($u['is_active'] ?? 1) ? 0 : 1 ?>" class="toggle-switch <?= ($u['is_active'] ?? 1) ? 'active' : '' ?>" <?= $u['id'] === $_SESSION['admin_id'] ? 'onclick="return false" style="opacity:0.4;cursor:not-allowed;"' : '' ?>>
                    <span class="toggle-slider"></span>
                </a>
            </td>
            <td>
                <?php if(!empty($u['last_login'])): ?>
                <span title="<?= $u['last_login'] ?>"><?= date('d/m/Y H:i', strtotime($u['last_login'])) ?></span>
                <?php else: ?>
                <span style="color:var(--gray-400);">Jamais</span>
                <?php endif; ?>
            </td>
            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td class="actions">
                <a href="?edit=<?= $u['id'] ?>" class="btn-icon btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
                <?php if($u['id'] !== $_SESSION['admin_id'] && $u['role'] !== 'super_admin'): ?>
                <a href="?delete=<?= $u['id'] ?>" class="btn-icon btn-delete" title="Supprimer" onclick="return confirm('Supprimer cet utilisateur ?')"><i class="fas fa-trash"></i></a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
