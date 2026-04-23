<?php
$adminTitle = 'Paramètres';
require_once 'includes/header.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'change_password') {
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

    if ($action === 'save_whatsapp') {
        $waNumber = preg_replace('/[^0-9]/', '', $_POST['whatsapp_number'] ?? '');
        setSetting($pdo, 'whatsapp_number', $waNumber);
        setSetting($pdo, 'whatsapp_welcome_message', $_POST['whatsapp_welcome_message'] ?? '');
        setSetting($pdo, 'whatsapp_order_message', $_POST['whatsapp_order_message'] ?? '');
        setSetting($pdo, 'whatsapp_catalogue_message', $_POST['whatsapp_catalogue_message'] ?? '');
        $success = 'Paramètres WhatsApp mis à jour avec succès.';
    }

    if ($action === 'save_site') {
        setSetting($pdo, 'site_name', sanitize($_POST['site_name'] ?? ''));
        setSetting($pdo, 'site_tagline', sanitize($_POST['site_tagline'] ?? ''));
        setSetting($pdo, 'site_phone', sanitize($_POST['site_phone'] ?? ''));
        setSetting($pdo, 'site_email', sanitize($_POST['site_email'] ?? ''));
        setSetting($pdo, 'site_address', sanitize($_POST['site_address'] ?? ''));
        $success = 'Informations du site mises à jour.';
    }

    if ($action === 'clear_visitors') {
        $days = intval($_POST['older_than'] ?? 90);
        $stmt = $pdo->prepare("DELETE FROM visitors WHERE visited_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        $deleted = $stmt->rowCount();
        $success = "$deleted entrées de visiteurs supprimées (plus de $days jours).";
    }
}

$settings = getAllSettings($pdo);
$s = function($key, $fallback = '') use ($settings) { return $settings[$key] ?? $fallback; };

$dbStats = [];
$tables = ['products', 'categories', 'messages', 'inquiries', 'visitors'];
foreach ($tables as $table) {
    $dbStats[$table] = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
}
?>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

<!-- WhatsApp Configuration -->
<div class="card" style="margin-bottom:20px; border-left:4px solid #25D366;">
    <h3><i class="fab fa-whatsapp" style="color:#25D366;"></i> Configuration WhatsApp</h3>
    <p style="color:var(--gray-500); font-size:0.85rem; margin-bottom:20px;">Configurez le numéro WhatsApp et les messages automatiques pour les commandes.</p>
    <form method="POST">
        <input type="hidden" name="action" value="save_whatsapp">
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-phone"></i> Numéro WhatsApp (format international sans +)</label>
                <input type="text" name="whatsapp_number" value="<?= sanitize($s('whatsapp_number', WHATSAPP_NUMBER)) ?>" placeholder="212652020702" required>
                <small style="color:var(--gray-500); font-size:0.78rem;">Exemple : 212652020702 (sans espaces ni +)</small>
            </div>
            <div class="form-group">
                <label>Aperçu du lien</label>
                <div style="padding:10px 14px; background:var(--gray-100); border-radius:8px; font-size:0.85rem; color:var(--gray-600); word-break:break-all;">
                    https://wa.me/<?= sanitize($s('whatsapp_number', WHATSAPP_NUMBER)) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label><i class="fas fa-comment-dots"></i> Message d'accueil (bouton WhatsApp général)</label>
            <textarea name="whatsapp_welcome_message" rows="3" placeholder="Bonjour, je souhaite avoir des informations..."><?= sanitize($s('whatsapp_welcome_message', 'Bonjour, je souhaite avoir des informations sur vos produits d\'hygiène professionnelle.')) ?></textarea>
            <small style="color:var(--gray-500); font-size:0.78rem;">Message envoyé quand un visiteur clique sur le bouton WhatsApp flottant ou les liens généraux.</small>
        </div>
        <div class="form-group">
            <label><i class="fas fa-shopping-cart"></i> Message de commande produit</label>
            <textarea name="whatsapp_order_message" rows="3" placeholder="Bonjour, je souhaite commander..."><?= sanitize($s('whatsapp_order_message', 'Bonjour, je souhaite commander le produit : *{product}* (Quantité: {quantity})\nMerci de me contacter pour finaliser la commande.')) ?></textarea>
            <small style="color:var(--gray-500); font-size:0.78rem;">Variables disponibles : <code>{product}</code> = nom du produit, <code>{quantity}</code> = quantité. Utilisez * pour le gras WhatsApp.</small>
        </div>
        <div class="form-group">
            <label><i class="fas fa-book"></i> Message après consultation du catalogue</label>
            <textarea name="whatsapp_catalogue_message" rows="3" placeholder="Bonjour, j'ai consulté votre catalogue..."><?= sanitize($s('whatsapp_catalogue_message', 'Bonjour, j\'ai consulté votre catalogue et je souhaite avoir plus d\'informations sur vos produits.')) ?></textarea>
            <small style="color:var(--gray-500); font-size:0.78rem;">Message pré-rempli quand un visiteur clique sur WhatsApp depuis la page catalogue.</small>
        </div>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Enregistrer les Paramètres WhatsApp</button>
    </form>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
    <!-- Site Info Editable -->
    <div class="card">
        <h3><i class="fas fa-globe"></i> Informations du Site</h3>
        <form method="POST">
            <input type="hidden" name="action" value="save_site">
            <div class="form-group">
                <label>Nom du site</label>
                <input type="text" name="site_name" value="<?= sanitize($s('site_name', SITE_NAME)) ?>">
            </div>
            <div class="form-group">
                <label>Slogan</label>
                <input type="text" name="site_tagline" value="<?= sanitize($s('site_tagline', SITE_TAGLINE)) ?>">
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" name="site_phone" value="<?= sanitize($s('site_phone', SITE_PHONE)) ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="site_email" value="<?= sanitize($s('site_email', SITE_EMAIL)) ?>">
            </div>
            <div class="form-group">
                <label>Adresse</label>
                <textarea name="site_address" rows="2"><?= sanitize($s('site_address', SITE_ADDRESS)) ?></textarea>
            </div>
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Enregistrer</button>
        </form>
    </div>

    <!-- Database & System -->
    <div>
        <div class="card" style="margin-bottom:20px;">
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
            <div style="margin-top:15px; padding-top:15px; border-top:1px solid var(--gray-200); font-size:0.82rem; color:var(--gray-500);">
                <div style="display:flex; justify-content:space-between;"><span>PHP</span><span><?= phpversion() ?></span></div>
                <div style="display:flex; justify-content:space-between; margin-top:4px;"><span>MySQL</span><span><?= $pdo->query("SELECT VERSION()")->fetchColumn() ?></span></div>
            </div>
        </div>

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
                    <label>Confirmer</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Modifier</button>
            </form>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:20px;">
    <!-- Maintenance -->
    <div class="card">
        <h3><i class="fas fa-tools"></i> Maintenance</h3>
        <form method="POST">
            <input type="hidden" name="action" value="clear_visitors">
            <p style="color:var(--gray-600); font-size:0.9rem; margin-bottom:15px;">
                Nettoyer les données visiteurs. Actuellement : <strong><?= number_format($dbStats['visitors']) ?></strong> entrées.
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
            <button type="submit" class="btn-admin btn-admin-primary" onclick="return confirm('Êtes-vous sûr ?')">
                <i class="fas fa-broom"></i> Nettoyer
            </button>
        </form>
    </div>

    <!-- Export -->
    <div class="card">
        <h3><i class="fas fa-download"></i> Exporter les Données</h3>
        <p style="color:var(--gray-600); font-size:0.9rem; margin-bottom:20px;">Téléchargez vos données au format CSV.</p>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <a href="export.php?type=products" class="btn-admin btn-admin-secondary" style="justify-content:flex-start;"><i class="fas fa-box-open"></i> Exporter Produits</a>
            <a href="export.php?type=messages" class="btn-admin btn-admin-secondary" style="justify-content:flex-start;"><i class="fas fa-envelope"></i> Exporter Messages</a>
            <a href="export.php?type=inquiries" class="btn-admin btn-admin-secondary" style="justify-content:flex-start;"><i class="fas fa-question-circle"></i> Exporter Demandes</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
