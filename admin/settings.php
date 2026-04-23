<?php
$adminTitle = 'Paramètres';
require_once 'includes/header.php';

$success = $error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        $admin = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $admin->execute([$_SESSION['admin_id']]);
        $admin = $admin->fetch();
        
        if (!$admin || !password_verify($current, $admin['password'])) {
            $error = 'Mot de passe actuel incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        } elseif ($new !== $confirm) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$hash, $_SESSION['admin_id']]);
            $success = 'Mot de passe modifié avec succès.';
        }
    }
    
    if ($_POST['action'] === 'clear_visitors') {
        $days = intval($_POST['older_than'] ?? 90);
        $stmt = $pdo->prepare("DELETE FROM visitors WHERE visited_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        $deleted = $stmt->rowCount();
        $success = "$deleted entrées de visiteurs supprimées (plus de $days jours).";
    }
}

// Database stats
$dbStats = [];
$tables = ['products', 'categories', 'messages', 'inquiries', 'visitors'];
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    $dbStats[$table] = $count;
}
$totalVisitors = $dbStats['visitors'];
?>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
    <!-- Site Info -->
    <div class="card">
        <h3><i class="fas fa-globe"></i> Informations du Site</h3>
        <table class="admin-table">
            <tbody>
                <tr><td><strong>Nom du site</strong></td><td><?= SITE_NAME ?></td></tr>
                <tr><td><strong>URL</strong></td><td><?= SITE_URL ?></td></tr>
                <tr><td><strong>Email</strong></td><td><?= SITE_EMAIL ?></td></tr>
                <tr><td><strong>Téléphone</strong></td><td><?= SITE_PHONE ?></td></tr>
                <tr><td><strong>WhatsApp</strong></td><td><?= WHATSAPP_NUMBER ?></td></tr>
                <tr><td><strong>Adresse</strong></td><td><?= SITE_ADDRESS ?></td></tr>
                <tr><td><strong>PHP Version</strong></td><td><?= phpversion() ?></td></tr>
                <tr><td><strong>MySQL Version</strong></td><td><?= $pdo->query("SELECT VERSION()")->fetchColumn() ?></td></tr>
            </tbody>
        </table>
    </div>

    <!-- Database Stats -->
    <div class="card">
        <h3><i class="fas fa-database"></i> Base de Données</h3>
        <table class="admin-table">
            <thead><tr><th>Table</th><th>Entrées</th></tr></thead>
            <tbody>
                <?php foreach($dbStats as $table => $count): ?>
                <tr>
                    <td><i class="fas fa-table" style="color:var(--secondary);margin-right:8px;"></i> <?= ucfirst($table) ?></td>
                    <td><span class="badge badge-primary"><?= number_format($count) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <tr style="font-weight:700;">
                    <td>Total</td>
                    <td><span class="badge badge-success"><?= number_format(array_sum($dbStats)) ?></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:20px;">
    <!-- Change Password -->
    <div class="card">
        <h3><i class="fas fa-lock"></i> Changer le Mot de Passe</h3>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label>Mot de passe actuel</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Modifier</button>
        </form>
    </div>

    <!-- Maintenance -->
    <div class="card">
        <h3><i class="fas fa-tools"></i> Maintenance</h3>
        <form method="POST" style="margin-bottom:20px;">
            <input type="hidden" name="action" value="clear_visitors">
            <p style="color:var(--gray-600); font-size:0.9rem; margin-bottom:15px;">
                Nettoyer les anciennes données de visiteurs pour optimiser la base de données.
                <br>Actuellement : <strong><?= number_format($totalVisitors) ?></strong> entrées.
            </p>
            <div class="form-group">
                <label>Supprimer les entrées de plus de :</label>
                <select name="older_than">
                    <option value="30">30 jours</option>
                    <option value="60">60 jours</option>
                    <option value="90" selected>90 jours</option>
                    <option value="180">180 jours</option>
                </select>
            </div>
            <button type="submit" class="btn-admin btn-admin-primary" onclick="return confirm('Êtes-vous sûr de vouloir nettoyer ?')">
                <i class="fas fa-broom"></i> Nettoyer
            </button>
        </form>
        
        <hr style="border:none; border-top:1px solid var(--gray-200); margin:20px 0;">
        
        <h4 style="font-size:0.95rem; color:var(--primary); margin-bottom:12px;"><i class="fas fa-download"></i> Exporter les Données</h4>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="export.php?type=products" class="btn-admin btn-admin-secondary"><i class="fas fa-box-open"></i> Produits (CSV)</a>
            <a href="export.php?type=messages" class="btn-admin btn-admin-secondary"><i class="fas fa-envelope"></i> Messages (CSV)</a>
            <a href="export.php?type=inquiries" class="btn-admin btn-admin-secondary"><i class="fas fa-question-circle"></i> Demandes (CSV)</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
