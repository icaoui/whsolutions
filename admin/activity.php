<?php
$adminTitle = 'Journal d\'Activité';
require_once 'includes/header.php';

if (!isSuperAdmin()) {
    echo '<div class="alert alert-error"><i class="fas fa-lock"></i> Accès réservé au Super Administrateur.</div>';
    require_once 'includes/footer.php';
    exit;
}

// Filters
$filterAdmin = intval($_GET['admin'] ?? 0);
$filterAction = sanitize($_GET['action'] ?? '');
$filterDate = sanitize($_GET['date'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 30;

// Clear log
if (isset($_GET['clear']) && $_GET['clear'] === 'all') {
    $pdo->exec("DELETE FROM admin_activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    logAdminActivity($pdo, $_SESSION['admin_id'], 'clear_log', 'Nettoyage du journal (+30 jours)');
    header('Location: activity.php?cleared=1');
    exit;
}

// Build query
$where = [];
$params = [];
if ($filterAdmin > 0) { $where[] = "l.admin_id = ?"; $params[] = $filterAdmin; }
if ($filterAction) { $where[] = "l.action LIKE ?"; $params[] = "%$filterAction%"; }
if ($filterDate) { $where[] = "DATE(l.created_at) = ?"; $params[] = $filterDate; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM admin_activity_log l $whereSQL");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

$sql = "SELECT l.*, a.name as admin_name, a.username, a.role FROM admin_activity_log l LEFT JOIN admins a ON l.admin_id = a.id $whereSQL ORDER BY l.created_at DESC LIMIT " . intval($perPage) . " OFFSET " . intval($offset);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get admins for filter dropdown
$admins = $pdo->query("SELECT id, name, username FROM admins ORDER BY name")->fetchAll();

// Get unique actions for filter
$actions = $pdo->query("SELECT DISTINCT action FROM admin_activity_log ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

// Action icon map
function getActionIcon($action) {
    $map = [
        'login' => 'sign-in-alt', 'logout' => 'sign-out-alt',
        'create_user' => 'user-plus', 'edit_user' => 'user-edit', 'delete_user' => 'user-minus', 'toggle_user' => 'user-check',
        'add_product' => 'plus-circle', 'edit_product' => 'edit', 'delete_product' => 'trash', 'toggle_product' => 'toggle-on', 'duplicate_product' => 'copy',
        'add_category' => 'folder-plus', 'edit_category' => 'folder-open', 'delete_category' => 'folder-minus', 'reorder_category' => 'sort',
        'update_settings' => 'cog', 'change_password' => 'key',
        'read_message' => 'envelope-open', 'delete_message' => 'trash-alt',
        'update_inquiry' => 'clipboard-check', 'delete_inquiry' => 'trash',
        'clear_log' => 'broom',
    ];
    return $map[$action] ?? 'info-circle';
}
function getActionColor($action) {
    if (str_contains($action, 'delete') || str_contains($action, 'clear')) return '#e74c3c';
    if (str_contains($action, 'create') || str_contains($action, 'add')) return '#27ae60';
    if (str_contains($action, 'edit') || str_contains($action, 'update') || str_contains($action, 'change')) return '#f39c12';
    if ($action === 'login') return '#3498db';
    if ($action === 'logout') return '#95a5a6';
    return 'var(--primary)';
}
?>

<?php if(isset($_GET['cleared'])): ?><div class="alert alert-success"><i class="fas fa-check"></i> Journal nettoyé (entrées de +30 jours supprimées).</div><?php endif; ?>

<!-- Stats -->
<div class="stats-row" style="display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin-bottom:25px;">
    <div class="card" style="text-align:center; padding:20px;">
        <div style="font-size:2rem; font-weight:800; color:var(--primary);"><?= $total ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Total Entrées</div>
    </div>
    <div class="card" style="text-align:center; padding:20px;">
        <?php $today = $pdo->query("SELECT COUNT(*) FROM admin_activity_log WHERE DATE(created_at) = CURDATE()")->fetchColumn(); ?>
        <div style="font-size:2rem; font-weight:800; color:var(--secondary);"><?= $today ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Aujourd'hui</div>
    </div>
    <div class="card" style="text-align:center; padding:20px;">
        <?php $logins = $pdo->query("SELECT COUNT(*) FROM admin_activity_log WHERE action='login' AND DATE(created_at) = CURDATE()")->fetchColumn(); ?>
        <div style="font-size:2rem; font-weight:800; color:#3498db;"><?= $logins ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Connexions aujourd'hui</div>
    </div>
    <div class="card" style="text-align:center; padding:20px;">
        <?php $activeAdmins = $pdo->query("SELECT COUNT(DISTINCT admin_id) FROM admin_activity_log WHERE DATE(created_at) = CURDATE()")->fetchColumn(); ?>
        <div style="font-size:2rem; font-weight:800; color:#FFE66D;"><?= $activeAdmins ?></div>
        <div style="font-size:0.82rem; color:var(--gray-500);">Admins actifs aujourd'hui</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px;">
    <form method="GET" class="filters-row" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
        <div class="form-group" style="margin:0; min-width:160px;">
            <label style="font-size:0.78rem; font-weight:600;">Administrateur</label>
            <select name="admin">
                <option value="">Tous</option>
                <?php foreach($admins as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $filterAdmin == $a['id'] ? 'selected' : '' ?>><?= sanitize($a['name']) ?> (@<?= sanitize($a['username']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0; min-width:140px;">
            <label style="font-size:0.78rem; font-weight:600;">Action</label>
            <select name="action">
                <option value="">Toutes</option>
                <?php foreach($actions as $a): ?>
                <option value="<?= sanitize($a) ?>" <?= $filterAction === $a ? 'selected' : '' ?>><?= sanitize($a) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0; min-width:140px;">
            <label style="font-size:0.78rem; font-weight:600;">Date</label>
            <input type="date" name="date" value="<?= $filterDate ?>">
        </div>
        <button type="submit" class="btn-admin btn-admin-primary" style="height:38px;"><i class="fas fa-filter"></i> Filtrer</button>
        <a href="activity.php" class="btn-admin btn-admin-secondary" style="height:38px;">Réinitialiser</a>
        <a href="?clear=all" class="btn-admin btn-admin-danger" style="height:38px; margin-left:auto;" onclick="return confirm('Supprimer les entrées de plus de 30 jours ?')"><i class="fas fa-broom"></i> Nettoyer</a>
    </form>
</div>

<!-- Log Table -->
<div class="card">
    <h3><i class="fas fa-history"></i> Journal d'Activité</h3>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width:50px;"></th>
                <th>Administrateur</th>
                <th>Action</th>
                <th>Détails</th>
                <th>IP</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if(empty($logs)): ?>
        <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--gray-400);">Aucune activité trouvée.</td></tr>
        <?php else: foreach($logs as $log): ?>
        <tr>
            <td style="text-align:center;">
                <i class="fas fa-<?= getActionIcon($log['action']) ?>" style="color:<?= getActionColor($log['action']) ?>; font-size:1.1rem;"></i>
            </td>
            <td>
                <strong><?= sanitize($log['admin_name'] ?? 'Inconnu') ?></strong>
                <?php if(($log['role'] ?? '') === 'super_admin'): ?>
                <span class="role-badge role-super" style="font-size:0.6rem; padding:1px 5px;">S</span>
                <?php endif; ?>
            </td>
            <td><code style="background:rgba(0,0,0,0.05);padding:2px 8px;border-radius:4px;font-size:0.8rem;"><?= sanitize($log['action']) ?></code></td>
            <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= sanitize($log['details'] ?? '') ?>"><?= sanitize($log['details'] ?? '-') ?></td>
            <td><small style="color:var(--gray-500);"><?= sanitize($log['ip_address'] ?? '-') ?></small></td>
            <td><small><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></small></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>

    <?php if($totalPages > 1): ?>
    <div class="pagination" style="display:flex; justify-content:center; gap:5px; margin-top:20px;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&admin=<?= $filterAdmin ?>&action=<?= urlencode($filterAction) ?>&date=<?= $filterDate ?>" class="btn-admin <?= $i === $page ? 'btn-admin-primary' : 'btn-admin-secondary' ?>" style="min-width:36px; text-align:center;"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
