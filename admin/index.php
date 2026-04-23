<?php
$adminTitle = 'Dashboard';
require_once 'includes/header.php';

// Stats
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalMessages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$totalInquiries = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$totalVisitors = $pdo->query("SELECT COUNT(*) FROM visitors")->fetchColumn();
$todayVisitors = $pdo->query("SELECT COUNT(*) FROM visitors WHERE DATE(visited_at) = CURDATE()")->fetchColumn();
$unreadMessages = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
$pendingInquiries = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();

// Visitors last 7 days
$visitorsData = $pdo->query("SELECT DATE(visited_at) as day, COUNT(*) as count FROM visitors WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(visited_at) ORDER BY day")->fetchAll();
$vLabels = array_map(fn($v) => date('d/m', strtotime($v['day'])), $visitorsData);
$vCounts = array_column($visitorsData, 'count');

// Products per category
$catData = $pdo->query("SELECT c.name, COUNT(p.id) as count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY count DESC")->fetchAll();
$cLabels = array_column($catData, 'name');
$cCounts = array_column($catData, 'count');

// Top products by views
$topProducts = $pdo->query("SELECT name, views FROM products ORDER BY views DESC LIMIT 5")->fetchAll();

// Recent messages
$recentMessages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent inquiries
$recentInquiries = $pdo->query("SELECT i.*, p.name as product_name FROM inquiries i LEFT JOIN products p ON i.product_id = p.id ORDER BY i.created_at DESC LIMIT 5")->fetchAll();
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon"><i class="fas fa-box-open"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $totalProducts ?></span>
            <span class="stat-label">Produits</span>
        </div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-icon"><i class="fas fa-th-large"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $totalCategories ?></span>
            <span class="stat-label">Catégories</span>
        </div>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $totalMessages ?></span>
            <span class="stat-label">Messages</span>
        </div>
        <?php if($unreadMessages > 0): ?>
        <span class="stat-badge"><?= $unreadMessages ?> non lu(s)</span>
        <?php endif; ?>
    </div>
    <div class="stat-card stat-danger">
        <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $totalInquiries ?></span>
            <span class="stat-label">Demandes</span>
        </div>
        <?php if($pendingInquiries > 0): ?>
        <span class="stat-badge"><?= $pendingInquiries ?> en attente</span>
        <?php endif; ?>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $totalVisitors ?></span>
            <span class="stat-label">Visiteurs Total</span>
        </div>
    </div>
    <div class="stat-card stat-secondary">
        <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $todayVisitors ?></span>
            <span class="stat-label">Visiteurs Aujourd'hui</span>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="charts-grid">
    <div class="chart-card">
        <h3><i class="fas fa-chart-area"></i> Visiteurs (7 derniers jours)</h3>
        <canvas id="visitorsChart"></canvas>
    </div>
    <div class="chart-card">
        <h3><i class="fas fa-chart-doughnut"></i> Produits par Catégorie</h3>
        <canvas id="categoriesChart"></canvas>
    </div>
</div>

<!-- Tables -->
<div class="tables-grid">
    <div class="table-card">
        <h3><i class="fas fa-fire"></i> Top Produits (vues)</h3>
        <table class="admin-table">
            <thead><tr><th>Produit</th><th>Vues</th></tr></thead>
            <tbody>
            <?php foreach($topProducts as $tp): ?>
            <tr><td><?= sanitize($tp['name']) ?></td><td><span class="badge badge-primary"><?= $tp['views'] ?></span></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-card">
        <h3><i class="fas fa-envelope"></i> Messages Récents</h3>
        <table class="admin-table">
            <thead><tr><th>Nom</th><th>Sujet</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach($recentMessages as $m): ?>
            <tr>
                <td><?= sanitize($m['name']) ?></td>
                <td><?= sanitize($m['subject']) ?></td>
                <td><?= date('d/m H:i', strtotime($m['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($recentMessages)): ?><tr><td colspan="3" style="text-align:center;">Aucun message</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="table-card" style="margin-top:20px;">
    <h3><i class="fas fa-question-circle"></i> Demandes Récentes</h3>
    <table class="admin-table">
        <thead><tr><th>Client</th><th>Produit</th><th>Qté</th><th>Statut</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach($recentInquiries as $inq): ?>
        <tr>
            <td><?= sanitize($inq['customer_name']) ?></td>
            <td><?= sanitize($inq['product_name'] ?? 'N/A') ?></td>
            <td><?= $inq['quantity'] ?></td>
            <td><span class="badge badge-<?= $inq['status'] === 'new' ? 'warning' : ($inq['status'] === 'completed' ? 'success' : 'primary') ?>"><?= $inq['status'] === 'new' ? 'Nouveau' : ($inq['status'] === 'contacted' ? 'Contacté' : ($inq['status'] === 'completed' ? 'Terminé' : 'Annulé')) ?></span></td>
            <td><?= date('d/m H:i', strtotime($inq['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($recentInquiries)): ?><tr><td colspan="5" style="text-align:center;">Aucune demande</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Visitors Chart
new Chart(document.getElementById('visitorsChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($vLabels) ?>,
        datasets: [{
            label: 'Visiteurs',
            data: <?= json_encode($vCounts) ?>,
            borderColor: '#4ECDC4',
            backgroundColor: 'rgba(78,205,196,0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#4ECDC4'
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

// Categories Chart
new Chart(document.getElementById('categoriesChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($cLabels) ?>,
        datasets: [{
            data: <?= json_encode($cCounts) ?>,
            backgroundColor: ['#1B3A5C','#4ECDC4','#25D366','#FF6B6B','#FFE66D','#6C5CE7','#FDA085','#A8E6CF']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } } } }
});
</script>

<?php require_once 'includes/footer.php'; ?>