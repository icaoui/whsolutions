<?php
$adminTitle = 'Visiteurs';
require_once 'includes/header.php';

$filter = $_GET['period'] ?? '7';
$days = intval($filter);
if ($days <= 0) $days = 7;

$visitors = $pdo->prepare("SELECT * FROM visitors WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY) ORDER BY visited_at DESC LIMIT 200");
$visitors->execute([$days]);
$visitors = $visitors->fetchAll();

$totalCount = $pdo->prepare("SELECT COUNT(*) FROM visitors WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
$totalCount->execute([$days]);
$totalCount = $totalCount->fetchColumn();

$uniqueIps = $pdo->prepare("SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
$uniqueIps->execute([$days]);
$uniqueIps = $uniqueIps->fetchColumn();

$topPages = $pdo->prepare("SELECT page_visited, COUNT(*) as count FROM visitors WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY page_visited ORDER BY count DESC LIMIT 10");
$topPages->execute([$days]);
$topPages = $topPages->fetchAll();
?>

<div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
    <a href="?period=1" class="btn-admin <?= $days == 1 ? 'btn-admin-primary' : 'btn-admin-secondary' ?>">Aujourd'hui</a>
    <a href="?period=7" class="btn-admin <?= $days == 7 ? 'btn-admin-primary' : 'btn-admin-secondary' ?>">7 jours</a>
    <a href="?period=30" class="btn-admin <?= $days == 30 ? 'btn-admin-primary' : 'btn-admin-secondary' ?>">30 jours</a>
    <a href="?period=90" class="btn-admin <?= $days == 90 ? 'btn-admin-primary' : 'btn-admin-secondary' ?>">90 jours</a>
</div>

<div class="stats-grid" style="margin-bottom:30px;">
    <div class="stat-card stat-primary">
        <div class="stat-icon"><i class="fas fa-eye"></i></div>
        <div class="stat-info"><span class="stat-number"><?= $totalCount ?></span><span class="stat-label">Pages vues</span></div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-icon"><i class="fas fa-user"></i></div>
        <div class="stat-info"><span class="stat-number"><?= $uniqueIps ?></span><span class="stat-label">Visiteurs uniques</span></div>
    </div>
</div>

<div class="tables-grid">
    <div class="card">
        <h3><i class="fas fa-fire"></i> Pages les plus visitées</h3>
        <table class="admin-table">
            <thead><tr><th>Page</th><th>Visites</th></tr></thead>
            <tbody>
            <?php foreach($topPages as $tp): ?>
            <tr><td><?= sanitize($tp['page_visited']) ?></td><td><span class="badge badge-primary"><?= $tp['count'] ?></span></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h3><i class="fas fa-clock"></i> Dernières visites</h3>
        <table class="admin-table">
            <thead><tr><th>IP</th><th>Page</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach(array_slice($visitors, 0, 20) as $v): ?>
            <tr>
                <td><?= sanitize($v['ip_address']) ?></td>
                <td><?= sanitize($v['page_visited']) ?></td>
                <td><?= date('d/m H:i', strtotime($v['visited_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>