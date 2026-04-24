<?php
$adminTitle = 'Rapports';
require_once 'includes/header.php';

$success = '';
$error = '';

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM reports WHERE id = ?")->execute([$id]);
    logAdminActivity($pdo, $_SESSION['admin_id'], 'delete_report', "Rapport #$id supprimé");
    header('Location: reports.php?msg=deleted');
    exit;
}

// Export as HTML file
if (isset($_GET['export'])) {
    $id = (int)$_GET['export'];
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([$id]);
    $report = $stmt->fetch();
    if ($report) {
        $filename = 'rapport_' . preg_replace('/[^a-z0-9]/i', '_', $report['title']) . '_' . date('Ymd') . '.html';
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>' . htmlspecialchars($report['title']) . '</title>';
        echo '<style>body{font-family:Segoe UI,Arial,sans-serif;max-width:800px;margin:40px auto;padding:0 20px;color:#333;line-height:1.7;}';
        echo 'h1{color:#1B3A5C;border-bottom:3px solid #4ECDC4;padding-bottom:10px;}';
        echo '.meta{color:#888;font-size:0.9rem;margin-bottom:30px;}';
        echo '.content{font-size:1rem;} .content p{margin:10px 0;}';
        echo '.footer{margin-top:40px;padding-top:15px;border-top:1px solid #ddd;color:#aaa;font-size:0.8rem;text-align:center;}';
        echo '</style></head><body>';
        echo '<h1>' . htmlspecialchars($report['title']) . '</h1>';
        echo '<div class="meta">Auteur : ' . htmlspecialchars($report['author']) . ' | Date : ' . date('d/m/Y H:i', strtotime($report['created_at'])) . '</div>';
        echo '<div class="content">' . nl2br(htmlspecialchars($report['content'])) . '</div>';
        echo '<div class="footer">Rapport généré par WH Solutions Admin — ' . date('d/m/Y') . '</div>';
        echo '</body></html>';
        exit;
    }
}

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    $author = $_SESSION['admin_name'] ?? 'Admin';

    if (empty($title) || empty($content)) {
        $error = 'Le titre et le contenu sont requis.';
    } else {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO reports (title, content, category, author, admin_id) VALUES (?,?,?,?,?)");
            $stmt->execute([$title, $content, $category, $author, $_SESSION['admin_id']]);
            logAdminActivity($pdo, $_SESSION['admin_id'], 'add_report', "Rapport '$title' créé");
            $success = 'Rapport créé avec succès !';
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE reports SET title=?, content=?, category=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$title, $content, $category, $id]);
            logAdminActivity($pdo, $_SESSION['admin_id'], 'edit_report', "Rapport #$id modifié");
            $success = 'Rapport modifié avec succès !';
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $success = 'Rapport supprimé.';
}

// Filters
$filterCat = $_GET['cat'] ?? '';
$search = trim($_GET['search'] ?? '');
$where = [];
$params = [];
if ($filterCat) { $where[] = "r.category = ?"; $params[] = $filterCat; }
if ($search) { $where[] = "(r.title LIKE ? OR r.content LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$reports = $pdo->prepare("SELECT r.* FROM reports r $whereSQL ORDER BY r.created_at DESC");
$reports->execute($params);
$reports = $reports->fetchAll();

// Edit mode
$editReport = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editReport = $stmt->fetch();
}

// View mode
$viewReport = null;
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([(int)$_GET['view']]);
    $viewReport = $stmt->fetch();
}

$categories = ['general' => 'Général', 'commercial' => 'Commercial', 'technique' => 'Technique', 'stock' => 'Stock & Inventaire', 'client' => 'Client', 'financier' => 'Financier'];
$catColors = ['general' => '#1B3A5C', 'commercial' => '#27ae60', 'technique' => '#e67e22', 'stock' => '#8e44ad', 'client' => '#2980b9', 'financier' => '#c0392b'];
?>

<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<?php if($viewReport): ?>
<!-- View Report -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;"><?= sanitize($viewReport['title']) ?></h3>
        <div style="display:flex; gap:8px;">
            <a href="reports.php?export=<?= $viewReport['id'] ?>" class="btn-admin btn-admin-primary" style="font-size:0.85rem; padding:8px 16px;"><i class="fas fa-download"></i> Exporter HTML</a>
            <a href="reports.php?edit=<?= $viewReport['id'] ?>" class="btn-admin btn-admin-secondary" style="font-size:0.85rem; padding:8px 16px;"><i class="fas fa-edit"></i> Modifier</a>
            <a href="reports.php" class="btn-admin btn-admin-secondary" style="font-size:0.85rem; padding:8px 16px;"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
    </div>
    <div class="card-body">
        <div style="display:flex; gap:15px; margin-bottom:20px; color:#888; font-size:0.88rem;">
            <span><i class="fas fa-user"></i> <?= sanitize($viewReport['author']) ?></span>
            <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($viewReport['created_at'])) ?></span>
            <span style="background:<?= $catColors[$viewReport['category']] ?? '#1B3A5C' ?>20; color:<?= $catColors[$viewReport['category']] ?? '#1B3A5C' ?>; padding:2px 12px; border-radius:20px; font-weight:600;">
                <?= $categories[$viewReport['category']] ?? $viewReport['category'] ?>
            </span>
            <?php if($viewReport['updated_at'] && $viewReport['updated_at'] !== $viewReport['created_at']): ?>
            <span><i class="fas fa-edit"></i> Modifié le <?= date('d/m/Y H:i', strtotime($viewReport['updated_at'])) ?></span>
            <?php endif; ?>
        </div>
        <div style="background:#f8f9fa; border-radius:12px; padding:30px; line-height:1.8; white-space:pre-wrap; font-size:0.95rem; color:#333;">
<?= sanitize($viewReport['content']) ?>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Form -->
<div class="card" style="margin-bottom:25px;">
    <div class="card-header">
        <h3><?= $editReport ? 'Modifier le rapport' : 'Rédiger un rapport' ?></h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editReport ? 'edit' : 'add' ?>">
            <?php if($editReport): ?><input type="hidden" name="id" value="<?= $editReport['id'] ?>"><?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 200px; gap:15px; margin-bottom:15px;">
                <div class="form-group">
                    <label>Titre du rapport *</label>
                    <input type="text" name="title" class="form-control" value="<?= sanitize($editReport['title'] ?? '') ?>" required placeholder="Ex: Rapport mensuel - Avril 2026">
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="category" class="form-control">
                        <?php foreach($categories as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($editReport['category'] ?? 'general') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Contenu du rapport *</label>
                <textarea name="content" class="form-control" rows="14" required placeholder="Rédigez votre rapport ici..." style="line-height:1.8; font-size:0.95rem; resize:vertical;"><?= sanitize($editReport['content'] ?? '') ?></textarea>
            </div>

            <div style="margin-top:15px; display:flex; gap:10px;">
                <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editReport ? 'Modifier' : 'Enregistrer' ?></button>
                <?php if($editReport): ?>
                <a href="reports.php" class="btn-admin btn-admin-secondary"><i class="fas fa-times"></i> Annuler</a>
                <?php endif; ?>
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
                <input type="text" name="search" class="form-control" value="<?= sanitize($search) ?>" placeholder="Titre ou contenu...">
            </div>
            <div class="form-group">
                <label>Catégorie</label>
                <select name="cat" class="form-control">
                    <option value="">Toutes</option>
                    <?php foreach($categories as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $filterCat === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-admin btn-admin-primary" style="height:42px;"><i class="fas fa-filter"></i> Filtrer</button>
            <a href="reports.php" class="btn-admin btn-admin-secondary" style="height:42px;"><i class="fas fa-redo"></i> Reset</a>
        </form>
    </div>
</div>

<!-- Reports List -->
<div class="card">
    <div class="card-header"><h3>Rapports (<?= count($reports) ?>)</h3></div>
    <div class="card-body">
        <?php if(empty($reports)): ?>
            <p style="text-align:center; color:#999; padding:30px;">Aucun rapport rédigé. Utilisez le formulaire ci-dessus pour en créer un.</p>
        <?php else: ?>
        <div style="display:grid; gap:15px;">
            <?php foreach($reports as $r): ?>
            <div style="background:#f8f9fa; border-radius:12px; padding:20px; border-left:4px solid <?= $catColors[$r['category']] ?? '#1B3A5C' ?>; display:flex; justify-content:space-between; align-items:flex-start; gap:15px;">
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                        <a href="reports.php?view=<?= $r['id'] ?>" style="font-weight:700; color:var(--primary,#1B3A5C); font-size:1.05rem; text-decoration:none;"><?= sanitize($r['title']) ?></a>
                        <span style="background:<?= $catColors[$r['category']] ?? '#1B3A5C' ?>20; color:<?= $catColors[$r['category']] ?? '#1B3A5C' ?>; padding:2px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; white-space:nowrap;">
                            <?= $categories[$r['category']] ?? $r['category'] ?>
                        </span>
                    </div>
                    <p style="color:#666; font-size:0.88rem; margin:0 0 8px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?= sanitize(mb_strimwidth($r['content'], 0, 150, '...')) ?>
                    </p>
                    <div style="display:flex; gap:12px; color:#aaa; font-size:0.8rem;">
                        <span><i class="fas fa-user"></i> <?= sanitize($r['author']) ?></span>
                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></span>
                    </div>
                </div>
                <div style="display:flex; gap:5px; flex-shrink:0;">
                    <a href="reports.php?view=<?= $r['id'] ?>" class="btn-icon btn-edit" title="Voir"><i class="fas fa-eye"></i></a>
                    <a href="reports.php?export=<?= $r['id'] ?>" class="btn-icon" style="background:rgba(39,174,96,0.1); color:#27ae60;" title="Exporter"><i class="fas fa-download"></i></a>
                    <a href="reports.php?edit=<?= $r['id'] ?>" class="btn-icon btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
                    <a href="reports.php?delete=<?= $r['id'] ?>" class="btn-icon btn-delete" title="Supprimer" onclick="return confirm('Supprimer ce rapport ?')"><i class="fas fa-trash"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
